<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class UserInfo extends BaseWidget
{
   
    protected int|string|array $columnSpan = 'full';

    protected function getCards(): array
    {
        $user = Filament::auth()->user();

        return [
            Card::make('Usuário', $user->name ?? '—')
                ->description("Cargo: " . ($user->cargo ?? '—'))
                ->descriptionIcon('heroicon-o-briefcase')     
                ->icon('heroicon-o-user-circle')              
                ->color('primary')                            
                ->extraAttributes([
                    'class' => 'shadow-lg rounded-xl',         
                ]),
        ];
    }
    protected function getColumns(): int
    {
        return 1;
    }
}
