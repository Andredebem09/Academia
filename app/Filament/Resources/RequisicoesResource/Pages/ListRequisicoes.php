<?php

namespace App\Filament\Resources\RequisicoesResource\Pages;

use App\Filament\Resources\RequisicoesResource;
use Filament\Tables\Actions\ViewAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequisicoes extends ListRecords
{
    protected static string $resource = RequisicoesResource::class;

        protected static string $view = 'filament.components.lista_requisicoes';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    

     
}
