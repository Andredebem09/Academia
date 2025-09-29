<?php

namespace App\Filament\Widgets;

use App\Models\Requisicoes;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class UserStats extends BaseWidget
{
    protected function getCards(): array
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return [];
        }

        $cards = [];

        switch ($user->cargo) {
            case 'atendente':
                $total = Requisicoes::where('status', 'atendimento')
                    ->where('status', 'atendimento')
                    ->count();

                $cards[] = Card::make('Chamados Atendidas', $total)
                    ->description('Total de atendimentos realizados até hoje')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->chart($this->lineData($user, 'atendimento'));
                break;

            case 'gerente':
                $total = Requisicoes::where('status', 'concluido')->count();

                $cards[] = Card::make('Chamados Aprovados', $total)
                    ->description('Total de Chamados aprovadas até hoje')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->chart($this->lineData($user, 'concluido'));
                break;

            case 'administrador':
                $total = Requisicoes::count();

                $cards[] = Card::make('Chamados Totais', $total)
                    ->description('Visão geral de todas os chamados')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('primary')
                    ->chart($this->lineData($user));
                break;
        }

        return $cards;
    }

    
   private function lineData($user, string $status = null): array
{
    $query = Requisicoes::query();

    if ($status) {
        $query->where('status', $status);
    }

    $data = $query->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
        ->where('created_at', '>=', now()->subDays(6))
        ->groupBy('dia')
        ->orderBy('dia')
        ->pluck('total', 'dia');

    $series = [];
    for ($i = 6; $i >= 0; $i--) {
        $dia = now()->subDays($i)->toDateString();
        $series[] = $data[$dia] ?? 0;
    }

    return $series;
}


    protected function getColumns(): int
    {
        return 1;
    }
}
