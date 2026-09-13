<?php

namespace App\Filament\Artisan\Widgets;

use App\Models\Artisan;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = Filament::auth()->user()->id;

        $artisan = Artisan::where('user_id', $userId)->first();
        if ($artisan) {
            return [
                Stat::make('Likers', $artisan->likers()->count()),
                Stat::make('Dislikers', $artisan->dislikers()->count()),
            ];
        } else {
            return [
                Stat::make('Instruction', 'Edit artisan profile'),
            ];
        }
    }
}
