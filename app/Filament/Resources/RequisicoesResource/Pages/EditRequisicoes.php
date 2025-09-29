<?php

namespace App\Filament\Resources\RequisicoesResource\Pages;

use App\Filament\Resources\RequisicoesResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class EditRequisicoes extends EditRecord
{
    protected static string $resource = RequisicoesResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // ====== BOTÃO DE ATENDER / APROVAR (dependendo do cargo) ======
            Action::make('workflow')
                ->label(fn () => match (Filament::auth()->user()?->cargo) {
                    'atendente' => match ($this->record->status) {
                        'pendente'   => 'Iniciar Atendimento',
                        'atendendo'  => 'Encaminhar para Aprovação',
                        default      => 'Atualizar',
                    },
                    'gerente'   => 'Aprovar',
                    default     => 'Atualizar',
                })
                ->icon(fn () => match (Filament::auth()->user()?->cargo) {
                    'atendente' => 'heroicon-o-clipboard-document-check',
                    'gerente'   => 'heroicon-o-check-circle',
                    default     => 'heroicon-o-pencil-square',
                })
                ->color(fn () => match (Filament::auth()->user()?->cargo) {
                    'atendente' => 'info',
                    'gerente'   => 'success',
                    default     => 'primary',
                })
                ->action(function () {
                    $user   = Filament::auth()->user();
                    $record = $this->record;

                    if ($user?->cargo === 'atendente') {
                        if ($record->status === 'pendente') {
                            $record->update(['status' => 'atendendo']);
                        } elseif ($record->status === 'atendendo') {
                            $record->update(['status' => 'aprovacao']);
                        }
                    } elseif ($user?->cargo === 'gerente') {
                        $record->update(['status' => 'concluido']);
                    }

                    Notification::make()
                        ->title('Requisição atualizada')
                        ->body(match ($user?->cargo) {
                            'atendente' => match ($this->record->status) {
                                'atendendo' => 'Atendimento iniciado.',
                                'aprovacao' => 'Atendimento concluído. Encaminhado para aprovação.',
                                default     => 'Registro atualizado.',
                            },
                            'gerente' => 'Requisição aprovada com sucesso.',
                            default   => 'Registro atualizado.',
                        })
                        ->icon('heroicon-o-check')
                        ->success()
                        ->send();

                    return redirect(RequisicoesResource::getUrl());
                }),

            // ====== BOTÃO DE REPROVAR (visível apenas para gerente em aprovação) ======
            Action::make('reprovar')
                ->label('Reprovar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () =>
                    Filament::auth()->user()?->cargo === 'gerente'
                    && $this->record->status === 'aprovacao'
                )
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    $record->update(['status' => 'reprovado']);

                    Notification::make()
                        ->title('Requisição reprovada')
                        ->body("A requisição {$record->name} foi reprovada.")
                        ->icon('heroicon-o-x-circle')
                        ->danger()
                        ->send();

                    return redirect(RequisicoesResource::getUrl());
                }),

            // ====== BOTÃO DE VOLTAR ======
            Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('secondary')
                ->url(RequisicoesResource::getUrl()),
        ];
    }


//  protected function getFormActions(): array
//     {
//         return [];
//     }

public function getTitle(): string
{
    return 'Editando Chamado ' . $this->record->relato;
}

    


}
