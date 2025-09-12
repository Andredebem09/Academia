<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnidadeResource\Pages;
use App\Filament\Resources\UnidadeResource\RelationManagers;
use App\Models\Academia;
use App\Models\Unidade;
use Carbon\Carbon;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnidadeResource extends Resource
{
    protected static ?string $model = Academia::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\Section::make('Informações da Unidade')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label('Nome')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('address')
                                ->label('Endereço')
                                ->required(),
                        ]),

                    // Campo de mapa (descomente se quiser usar)
                    // Map::make('localizacao')
                    //     ->label('Localização')
                    //     ->center(['lat' => -23.55052, 'lng' => -46.633308]) // Exemplo de São Paulo
                    //     ->zoom(12)
                    //     ->required(),
                ])
                ->columns(1) // a seção ocupa toda a largura do formulário
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label('Nome'),
                
                Tables\Columns\TextColumn::make('address')
                ->label('Endereço'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->tooltip(fn($record) => $record->created_at->diffForHumans())
                    ->formatStateUsing(
                        fn($state) => Carbon::parse($state)
                            ->locale('pt_BR')
                            ->isoFormat('dddd, DD/MM/YYYY HH:mm')
                    )
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
               Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->extraModalFooterActions([
                         Tables\Actions\EditAction::make()
                            ->label('Editar')
                            ->slideOver()
                            ->color('info')
                            ->successNotification(null)
                            ->icon('heroicon-o-pencil-square')
                            ->after(function ($record, $livewire) {
                                $livewire->dispatch('refresh');

                                Notification::make()
                                    ->title('Conta atualizada')
                                    ->body("Informações da unidade {$record->name} foram editadas com sucesso.")
                                    ->icon('heroicon-o-building-office')
                                    ->success()
                                    ->sendToDatabase(Filament::auth()->user())
                                    ->send();

                                return redirect(static::getUrl('index'));
                            }),

                        Tables\Actions\DeleteAction::make()
                            ->label('Excluir')
                            ->icon('heroicon-o-trash')
                            ->requiresConfirmation()
                            ->modalHeading('Excluir Usuário')
                            ->modalDescription(fn($record) => "Tem certeza que deseja excluir {$record->name}?")
                            ->modalSubmitActionLabel('Sim, excluir')
                            ->successNotificationTitle(null)
                            ->after(function ($record, $livewire) {

                                Notification::make()
                                    ->title('Unidade exluida')
                                    ->body("A unidade {$record->name} foi deletada dos nossos registros.")
                                    ->icon('heroicon-o-building-office')
                                    ->danger()
                                    ->sendToDatabase(Filament::auth()->user())
                                    ->send();

                                return redirect(static::getUrl('index'));
                            }),
                    ]),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnidades::route('/'),
            'create' => Pages\CreateUnidade::route('/create'),
            // 'edit' => Pages\EditUnidade::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Unidade';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Unidades';
    }
}
