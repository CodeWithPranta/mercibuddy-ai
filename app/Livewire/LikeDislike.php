<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LikeDislike extends Component
{
    public $artisan;
    // public $userLikesArtisan;
    // public $userDislikesArtisan;

    public function mount($artisan)
    {
        $this->artisan = $artisan;
        // $this->userLikesArtisan = auth()->user()->likedArtisans->contains($artisan);
        // $this->userDislikesArtisan = auth()->user()->dislikedArtisans->contains($artisan);
    }

    public function like()
    {
        $user = Auth::user();

        if (! $user) {
            // User is not logged in, show a flash message or redirect to login page
            session()->flash('message', 'Please log in to react.');

            return;
        }

        $liked = $user->likedArtisans()->where('artisan_user.artisan_id', $this->artisan->id)->exists();

        if ($liked) {
            // If already liked, detach the like
            $user->likedArtisans()->detach($this->artisan->id);
        } else {
            // If not liked yet, attach the like and detach any existing dislike
            $user->likedArtisans()->attach($this->artisan->id, ['reaction' => 'like']);
            $user->dislikedArtisans()->detach($this->artisan->id);
        }

        $this->artisan = $this->artisan->fresh();
    }

    public function dislike()
    {
        $user = Auth::user();

        if (! $user) {
            // User is not logged in, show a flash message or redirect to login page
            session()->flash('message', 'Please log in to react.');

            return;
        }

        $disliked = $user->dislikedArtisans()->where('artisan_user.artisan_id', $this->artisan->id)->exists();

        if ($disliked) {
            // If already disliked, detach the dislike
            $user->dislikedArtisans()->detach($this->artisan->id);
        } else {
            // If not disliked yet, attach the dislike and detach any existing like
            $user->dislikedArtisans()->attach($this->artisan->id, ['reaction' => 'dislike']);
            $user->likedArtisans()->detach($this->artisan->id);
        }

        $this->artisan = $this->artisan->fresh();
    }

    public function render()
    {
        // dd($this->userDislikesArtisan);
        return view('livewire.like-dislike');
    }
}
