<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;


class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Cadastrar novo Usuário';
    }

     protected function getFormActions(): array
{
    return [
        $this->getCreateFormAction(),
        $this->getCancelFormAction(), 
    ];
}

 protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Novo usuario cadastrado com sucesso')
            ->body('Um novo usuario foi cadastrado no sistema.')
            ->icon('heroicon-o-user')
            ->success(); 
    }
}
