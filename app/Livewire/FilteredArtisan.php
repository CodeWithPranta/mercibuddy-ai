<?php

namespace App\Livewire;

use App\Models\Artisan;
use App\Models\Category;
use App\Models\Country;
use Livewire\Attributes\Url;
use Livewire\Component;

class FilteredArtisan extends Component
{
    public int $countryId;

    public int $categoryId;

    #[Url('search')]
    public $search = '';

    #[Url('sort')]
    public $sort = 'recommended';

    public function mount(int $countryId, int $categoryId): void
    {
        $this->countryId = $countryId;
        $this->categoryId = $categoryId;

        if (! in_array($this->sort, array_keys($this->sortOptions()), true)) {
            $this->sort = 'recommended';
        }
    }

    /**
     * @return array<string, string>
     */
    public function sortOptions(): array
    {
        return [
            'recommended' => 'Recommended',
            'liked' => 'Most liked',
            'experienced' => 'Most experienced',
            'newest' => 'Newest members',
        ];
    }

    public function render()
    {
        $artisans = Artisan::query()
            ->with(['category', 'country', 'state', 'city'])
            ->withCount(['likers', 'dislikers'])
            ->where('country_id', $this->countryId)
            ->where('category_id', $this->categoryId)
            ->when($this->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhere('profession', 'like', '%'.$search.'%');
                });
            })
            ->tap(fn ($query) => $this->applySort($query))
            ->get();

        return view('livewire.filtered-artisan', [
            'artisans' => $artisans,
            'country' => Country::find($this->countryId),
            'category' => Category::find($this->categoryId),
        ]);
    }

    private function applySort($query): void
    {
        match ($this->sort) {
            'liked' => $query
                ->orderByDesc('likers_count')
                ->orderByRaw('CAST(experience_in_year AS UNSIGNED) DESC'),
            'experienced' => $query
                ->orderByRaw('CAST(experience_in_year AS UNSIGNED) DESC')
                ->orderByDesc('likers_count'),
            'newest' => $query->orderByDesc('artisans.created_at'),
            // Recommended: most liked first, then most experienced,
            // then the oldest members of the directory.
            default => $query
                ->orderByDesc('likers_count')
                ->orderByRaw('CAST(experience_in_year AS UNSIGNED) DESC')
                ->orderBy('artisans.created_at'),
        };
    }
}
