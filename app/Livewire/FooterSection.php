<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class FooterSection extends Component
{
    public function render()
    {
        return view('livewire.footer-section', [
            'setting' => Setting::first(),
        ]);
    }
}
