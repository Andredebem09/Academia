<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequisicoesResource\Pages;
use App\Filament\Resources\RequisicoesResource\RelationManagers;
use App\Models\Academia;
use App\Models\Requisicoes;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RequisicoesResource extends Resource
{
    protected static ?string $model = Requisicoes::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                   Forms\Components\Section::make('Informações da Requisição')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Select::make('unidade_id')
                                ->label('Unidade')
                                ->options(Academia::all()->pluck('name','id'))
                                ->required(),

                            Toggle::make('emergencial')
                                ->label('Emergencial')
                                ->default(false),
                        ]),

                    Textarea::make('relato')
                        ->label('Relato do problema')
                        ->required()
                        ->rows(5),

                    FileUpload::make('foto')
                        ->label('Foto')
                        ->image()
                        ->nullable(),
                ])
                ->columns(1) 
                ->columnSpanFull(), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 TextColumn::make('user.name')
                 ->label('Usuário')
                 ->sortable(),

                TextColumn::make('unidade.name')
                ->label('Unidade')
                ->sortable(),

                TextColumn::make('relato')
                ->limit(50)
                ->label('Relato'),

                ImageColumn::make('foto')
                ->label('Foto'),

                TextColumn::make('status')
                ->label('Status')
                ->sortable()
                ->badge()
                    ->colors([
                        'gray' => 'pendente',
                        'warning' => 'em andamento',
                        'info' => 'aprovacao',
                        'success' => 'concluido',
                    ]),


                IconColumn::make('emergencial')
                    ->label('Emergencial')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                ->label('Criado em')
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
            'index' => Pages\ListRequisicoes::route('/'),
            'create' => Pages\CreateRequisicoes::route('/create'),
            // 'edit' => Pages\EditRequisicoes::route('/{record}/edit'),

        ];
    }
}
