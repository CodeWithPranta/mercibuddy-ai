<?php

namespace App\Filament\Artisan\Widgets;

use App\Models\Artisan;
use App\Models\ArtisanQuery;
use App\Models\Service;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = Filament::auth()->user()->id;

        $artisan = Artisan::where('user_id', $userId)->first();
        $serviceCount = Service::where('user_id', $userId)->count();
        $unreadQueryCount = ArtisanQuery::where('artisan_id', $artisan?->id)->whereNull('read_at')->count();
        $totalQueryCount = ArtisanQuery::where('artisan_id', $artisan?->id)->count();

        if (! $artisan) {
            return [
                Stat::make('Profile status', 'Not created')
                    ->description('Create your artisan profile')
                    ->color('danger'),
                Stat::make('Services', $serviceCount)
                    ->description('Service details you offer')
                    ->color('info'),
                Stat::make('Customer queries', $totalQueryCount)
                    ->description('Queries received from customers')
                    ->color('info'),
            ];
        }

        $satisfaction = $artisan->satisfactionPercentage();

        return [
            Stat::make('Profile status', $artisan->is_active ? 'Active' : 'Inactive')
                ->description($artisan->is_active ? 'Visible to customers' : 'Hidden from customers')
                ->color($artisan->is_active ? 'success' : 'danger'),

            Stat::make('Likes', $artisan->likeCount())
                ->description('Positive reactions from customers')
                ->color('success'),

            Stat::make('Dislikes', $artisan->dislikeCount())
                ->description('Negative reactions from customers')
                ->color('danger'),

            Stat::make('Satisfaction', $satisfaction.'%')
                ->description('Like-to-dislike ratio')
                ->color($satisfaction >= 60 ? 'success' : ($satisfaction >= 40 ? 'warning' : 'danger')),

            Stat::make('Services', $serviceCount)
                ->description('Service details you offer')
                ->color('info'),

            Stat::make('Customer queries', $totalQueryCount)
                ->description($unreadQueryCount.' unread')
                ->color($unreadQueryCount > 0 ? 'warning' : 'info'),

            Stat::make('Experience', $artisan->experienceYears().' years')
                ->description('Years in your profession')
                ->color('info'),
        ];
    }
}
