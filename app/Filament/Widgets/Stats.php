<?php

namespace App\Filament\Widgets;

use App\Models\Academia;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class Stats extends BaseWidget
{
    protected function getStats(): array
    {
        // Total de unidades cadastradas hoje
        $hoje = Academia::whereDate('created_at', today())->count();

        // Total de unidades cadastradas ontem
        $ontem = Academia::whereDate('created_at', today()->subDay())->count();

        // Total geral de unidades
        $total = Academia::count();

        return [
            Stat::make('Unidades hoje', $hoje)
                ->description('Cadastradas hoje')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('success'),

            Stat::make('Unidades ontem', $ontem)
                ->description('Cadastradas ontem')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make('Total de unidades', $total)
                ->description('Total geral')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('primary'),
        ];
    }
}
