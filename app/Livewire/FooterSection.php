<?php

namespace App\Livewire;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FooterSection extends Component
{
    public function render()
    {
        $user = Auth::user();
        $unreadQueryCount = $user?->unreadQueryCount() ?? 0;

        return view('livewire.footer-section', [
            'setting' => Setting::first(),
            'showNotifications' => $user !== null
                && ($user->isArtisan() || $user->artisanQueries()->exists()),
            'unreadQueryCount' => $unreadQueryCount,
        ]);
    }
}
