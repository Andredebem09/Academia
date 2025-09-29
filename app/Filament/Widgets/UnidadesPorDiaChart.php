<?php

namespace App\Filament\Widgets;

use App\Models\Requisicoes;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PostsPerWeek extends ChartWidget
{
    protected static ?string $heading = 'Chamados nos últimos 7 dias';
    protected static ?string $description = 'Veja quantos Chamados foram realizadas nos últimos 7 dias.';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $startDate = Carbon::today()->subDays(6);
        $counts = Requisicoes::select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('COUNT(*) as total')
            )
            ->whereDate('created_at', '>=', $startDate)
            ->groupBy('data')
            ->orderBy('data')
            ->pluck('total', 'data');

        $labels = [];
        $data   = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $startDate->copy()->addDays($i);
            $labels[] = $day->format('d/m');
            $data[]   = $counts[$day->toDateString()] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Chamados por dia',
                    'data' => $data,
                    'fill' => true,
                    'backgroundColor' => 'rgba(56,189,248,0.2)',
                    'borderColor' => 'rgba(56,189,248,1)',
                    'pointBackgroundColor' => 'rgba(56,189,248,1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full'; 
    }

    protected function getContentHeight(): ?string
    {
        return '250px'; 
    }

    protected function getType(): string
    {
        return 'line';
    }

    // Adicionado: apenas números inteiros no eixo Y
    protected function getOptions(): ?array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
