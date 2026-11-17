<?php

namespace App\Livewire;

use App\Models\Artisan;
use App\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ArtisanReviews extends Component
{
    use WithPagination;

    public Artisan $artisan;

    public ?int $rating = null;

    public string $comment = '';

    public bool $showForm = false;

    public bool $isOwnProfile = false;

    public function mount(Artisan $artisan): void
    {
        $this->artisan = $artisan;
        $this->isOwnProfile = (int) Auth::id() === (int) $artisan->user_id;

        if ($existing = $this->existingReview()) {
            $this->rating = (int) $existing->rating;
            $this->comment = (string) $existing->comment;
        }
    }

    public function submit(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));

            return;
        }

        if ($this->isOwnProfile) {
            return;
        }

        $validated = $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        Comment::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'artisan_id' => $this->artisan->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ],
        );

        $this->showForm = false;
        $this->resetPage();
        session()->flash('review_status', 'Thanks! Your review has been saved.');
    }

    public function render(): View
    {
        $breakdown = $this->artisan->ratingBreakdown();
        $total = array_sum($breakdown);
        $average = $this->artisan->averageRating();

        $percentages = [];

        foreach ($breakdown as $star => $count) {
            $percentages[$star] = $total > 0 ? (int) round($count / $total * 100) : 0;
        }

        return view('livewire.artisan-reviews', [
            'reviews' => Comment::query()
                ->with('user')
                ->where('artisan_id', $this->artisan->id)
                ->whereNotNull('rating')
                ->latest()
                ->paginate(5),
            'breakdown' => $breakdown,
            'percentages' => $percentages,
            'total' => $total,
            'average' => $average,
            'label' => $this->artisan->ratingLabel(),
            'hasReviewed' => $this->existingReview() !== null,
        ]);
    }

    private function existingReview(): ?Comment
    {
        if (! Auth::check()) {
            return null;
        }

        return Comment::query()
            ->where('user_id', Auth::id())
            ->where('artisan_id', $this->artisan->id)
            ->first();
    }
}
