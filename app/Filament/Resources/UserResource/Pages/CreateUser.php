<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        // redireciona sempre para a listagem após salvar
        return static::getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Cadastrar novo Usuário';
    }
}
