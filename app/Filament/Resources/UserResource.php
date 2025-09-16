<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->columnSpanFull()
                    ->required()

                    ->maxLength(255),

                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),

                     TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->visible(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->required(fn($context) => $context === 'create')
                    ->dehydrateStateUsing(fn($state) => bcrypt($state)),

                Select::make('cargo')
                    ->label('Cargo')
                    ->columnSpanFull()
                    ->options([
                        'administrador' => 'Administrador',
                        'gerente' => 'Gerente',
                        'funcionario' => 'Funcionário',
                        'atendente' => 'atendente',
                    ])
                    ->required(),

              
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->badge()
                    ->colors([
                        'info' => 'gerente',
                        'warning' => 'funcionario',
                        'success' => 'administrador',
                        'primary' => 'atendente'
                    ])
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->tooltip(fn($record) => $record->created_at->diffForHumans())
                    ->formatStateUsing(
                        fn($state) => Carbon::parse($state)
                            ->locale('pt_BR')
                            ->isoFormat('dddd, DD/MM/YYYY HH:mm')
                    )
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->extraModalFooterActions([
                        Tables\Actions\EditAction::make()
                            ->label('Editar')
                            ->color('info')
                            ->slideOver()
                            ->successNotification(null)
                            ->icon('heroicon-o-pencil-square')
                            ->after(function ($record, $livewire) {
                                $livewire->dispatch('refresh');

                                Notification::make()
                                    ->title('Conta atualizada')
                                    ->body("Informações do usuario {$record->name} foram editadas com sucesso.")
                                    ->icon('heroicon-o-user')
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
                                    ->title('Usuario exluido')
                                    ->body("A conta {$record->name} foi deletada dos nossos registros.")
                                    ->icon('heroicon-o-user')
                                    ->danger()
                                    ->sendToDatabase(Filament::auth()->user())
                                    ->send();

                                return redirect(static::getUrl('index'));
                            }),
                    ]),


                Action::make('estatisticas')
                    ->label('Ver Estatísticas')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn(User $record) => UserResource::getUrl('estatisticas', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
            'estatisticas' => Pages\EstatisticasUser::route('/{record}/estatisticas'),

        ];
    }
}
