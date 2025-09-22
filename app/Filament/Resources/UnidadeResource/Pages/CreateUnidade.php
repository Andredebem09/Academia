<?php

namespace App\Filament\Resources\UnidadeResource\Pages;

use App\Filament\Resources\UnidadeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUnidade extends CreateRecord
{
    protected static string $resource = UnidadeResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Cadastrar nova Unidade';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Unidade cadastrada com sucesso')
            ->body('A nova unidade de academias da rede foi cadastrada corretamente.')
            ->success(); 
    }

     protected function getFormActions(): array
{
    return [
        $this->getCreateFormAction(),
        $this->getCancelFormAction(), 
    ];
}
}
