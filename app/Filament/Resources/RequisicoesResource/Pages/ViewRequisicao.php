<?php

namespace App\Filament\Resources\RequisicoesResource\Pages;

use App\Filament\Resources\RequisicoesResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Infolists;

class ViewRequisicao extends ViewRecord
{
    protected static string $resource = RequisicoesResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('secondary')
                ->url(RequisicoesResource::getUrl()),

            Action::make('editar')
                ->label(fn() => match (Filament::auth()->user()?->cargo) {
                    'atendente' => 'Atender',
                    'gerente'   => 'Aprovar',
                    default     => 'Editar',
                })
                ->icon('heroicon-o-pencil-square')
                ->color(fn() => match (Filament::auth()->user()?->cargo) {
                    'atendente' => 'info',
                    'gerente'   => 'success',  
                    default     => 'info',
                })
                ->url(fn() => RequisicoesResource::getUrl('edit', [
                    'record' => $this->record,
                ])),


            Action::make('excluir')
                ->label('Excluir')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Excluir requisição?')
                ->modalDescription(fn() => "Tem certeza que deseja excluir {$this->record->name}?")
                ->modalSubmitActionLabel('Sim, excluir')
                ->color('danger')
                ->action(function () {
                    $record = $this->record;
                    $record->delete();

                    Notification::make()
                        ->title('Requisição excluída')
                        ->body("A requisição {$record->name} foi removida.")
                        ->icon('heroicon-o-trash')
                        ->danger()
                        ->send();

                    return redirect(RequisicoesResource::getUrl());
                }),
        ];
    }

   public function getTitle(): string
{
    return 'Visualizando Chamado ' . $this->record->relato;
}

}
