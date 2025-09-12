<?php

namespace App\Filament\Resources\UnidadeResource\Pages;

use App\Filament\Resources\UnidadeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUnidade extends CreateRecord
{
    protected static string $resource = UnidadeResource::class;

    // protected function getFormActions(): array
    // {
    //     return [
    //         $this->getCreateFormAction(),
    //         $this->getCancelAction(),
    //     ];
    // }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

     public function getTitle(): string
{
    return 'Cadastrar nova Unidade';
}
}
