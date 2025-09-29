<?php

namespace App\Filament\Resources\UnidadeResource\Pages;

use App\Filament\Resources\UnidadeResource;
use App\Models\Academia;
use Filament\Resources\Pages\Page;

class LocalizacaoUnidade extends Page
{
    protected static string $resource = UnidadeResource::class;

    protected static string $view = 'filament.components.localizacao-unidade';

     public $record;

    public function mount($record)
    {
        $this->record = Academia::findOrFail($record);
    }

     public function getTitle(): string
{
    return 'Localização de ' . $this->record->name;
}
}
