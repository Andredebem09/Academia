<?php

namespace App\Filament\Resources\RequisicoesResource\Pages;

use App\Filament\Resources\RequisicoesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequisicoes extends ListRecords
{
    protected static string $resource = RequisicoesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
