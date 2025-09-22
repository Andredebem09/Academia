<?php

namespace App\Filament\Resources\RequisicoesResource\Pages;

use App\Filament\Resources\RequisicoesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;


class CreateRequisicoes extends CreateRecord
{
    protected static string $resource = RequisicoesResource::class;

     protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function getFormActions(): array
{
    return [
        $this->getCreateFormAction(),
        $this->getCancelFormAction(), 
    ];
}


}
