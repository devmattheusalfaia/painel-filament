<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Usuários', User::count())
            ->description('32k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([1, 1, 1])
            ->color('success'),
            
            Stat::make('Vagas Disponíveis', 150)
            ->description('10% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([1, 2, 3])
            ->color('info'),

            Stat::make('Instituições', 75)
            ->description('5% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([1, 2, 1])
            ->color('warning'),

            Stat::make('Candidaturas', 300)
            ->description('20% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([1, 3, 2])
            ->color('danger'),
        ];
    }
}
