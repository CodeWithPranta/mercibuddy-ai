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
            'price_desc' => 'Price High to Low',
            'price_asc' => 'Price Low to High',
            'satisfaction_desc' => 'Satisfaction Rate High to Low',
            'satisfaction_asc' => 'Satisfaction Rate Low to High',
            'recommended' => 'Yaara Recommended',
        ];
    }

    public function render()
    {
        $artisans = Artisan::query()
            ->with(['category', 'country', 'state', 'city', 'contactDetail'])
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
            'price_desc' => $query->orderByDesc('hourly_rate')->orderBy('artisans.created_at'),
            'price_asc' => $query->orderByRaw('hourly_rate IS NULL')->orderBy('hourly_rate')->orderBy('artisans.created_at'),
            'satisfaction_desc' => $query
                ->orderByRaw('CASE WHEN (likers_count + dislikers_count) = 0 THEN 0 ELSE likers_count / (likers_count + dislikers_count) END DESC')
                ->orderByDesc('likers_count'),
            'satisfaction_asc' => $query
                ->orderByRaw('CASE WHEN (likers_count + dislikers_count) = 0 THEN 0 ELSE likers_count / (likers_count + dislikers_count) END ASC')
                ->orderBy('likers_count'),
            // Recommended: most liked first, then most experienced,
            // then the oldest members of the directory.
            default => $query
                ->orderByDesc('likers_count')
                ->orderByRaw('CAST(experience_in_year AS UNSIGNED) DESC')
                ->orderBy('artisans.created_at'),
        };
    }
}
