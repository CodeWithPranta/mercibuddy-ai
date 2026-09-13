<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class GuestPageLogo extends Component
{
    public function render()
    {
        return view('livewire.guest-page-logo', [
            'setting' => Setting::first(),
        ]);
    }
}
