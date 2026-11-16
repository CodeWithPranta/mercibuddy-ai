<?php

namespace App\Filament\Artisan\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.artisan.pages.dashboard';

    public function getColumns(): int|array
    {
        return 2;
    }
}
