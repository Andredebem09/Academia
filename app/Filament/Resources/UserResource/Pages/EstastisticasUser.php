<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;


class EstatisticasUser extends Page
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.components.estatisticas-User';

     public ?User $user = null;
    public array $chartLabels = [];
    public array $chartData   = [];

    public function mount($record): void
{
    $this->user = $record instanceof User
        ? $record->load('requisicoes')      // 👈 carrega as requisições
        : User::with('requisicoes')->find($record);

    if (! $this->user) return;

    // Agrupa por dia (últimos 7 dias)
    $dias = $this->user->requisicoes()
        ->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
        ->where('created_at', '>=', now()->subDays(7))
        ->groupBy('dia')
        ->orderBy('dia')
        ->get();

    $this->chartLabels = $dias->pluck('dia')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toArray();
    $this->chartData   = $dias->pluck('total')->toArray();
}

    public function getTitle(): string
    {
        return $this->user
            ? 'Estatísticas de ' . $this->user->name
            : 'Estatísticas';
    }
}

