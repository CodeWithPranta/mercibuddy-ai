<?php

namespace App\Livewire;

use App\Livewire\Actions\Logout;
use App\Models\Setting;
use Livewire\Component;

class Navigation extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.navigation', [
            'setting' => Setting::first(),
        ]);
    }
}
