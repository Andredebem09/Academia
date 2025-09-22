<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnidadeResource\Pages;
use App\Models\Academia;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MultiSelect;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class UnidadeResource extends Resource
{
    protected static ?string $model = Academia::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informações da Unidade')
                ->columns(1)
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label('Nome')
                                ->required()
                                ->columnSpanFull()
                                ->maxLength(255),
                        ]),
                ]),

            Section::make('Endereço')
                ->columns(2)
                ->schema([
                    TextInput::make('cep')
                        ->label('CEP')
                        ->mask('99999-999')
                        ->placeholder('00000-000')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $cep = preg_replace('/[^0-9]/', '', $state);

                            if (empty($cep)) {
                                $set('rua', '');
                                $set('bairro', '');
                                $set('cidade', '');
                                $set('estado', '');
                                return;
                            }

                            if (strlen($cep) === 8) {
                                try {
                                    $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");
                                    if ($response->successful() && !isset($response['erro'])) {
                                        $set('rua', $response['logradouro'] ?? '');
                                        $set('bairro', $response['bairro'] ?? '');
                                        $set('cidade', $response['localidade'] ?? '');
                                        $set('estado', $response['uf'] ?? '');
                                    }
                                } catch (\Exception $e) {
                                }
                            }
                        }),

                    TextInput::make('estado')
                        ->label('UF')
                        ->maxLength(2),

                    TextInput::make('cidade')
                        ->label('Cidade')
                        ->maxLength(100),

                    TextInput::make('bairro')
                        ->label('Bairro')
                        ->maxLength(100),

                    TextInput::make('rua')
                        ->label('Rua')
                        ->maxLength(150),
                ]),

            Section::make('Gerente da Unidade')
                ->columns(1)
                ->schema([
                    MultiSelect::make('users')   
                        ->label('Gestores')
                        ->relationship(         
                            name: 'users',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn($query) =>
                            $query->where('cargo', 'gerente') 
                        )
                        ->preload()
                        ->placeholder('Selecione os gestores')
                        ->helperText('Selecione os usuários que serão gestores desta unidade'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('endereco_completo')
                    ->label('Endereço')
                    ->getStateUsing(function ($record) {
                        return collect([
                            $record->rua,
                            $record->bairro,
                            $record->cidade,
                            $record->estado,
                            $record->cep,
                        ])->filter()->implode(', ');
                    }),

                Tables\Columns\TextColumn::make('gestores_list')
                    ->label('Gestores')
                    ->getStateUsing(function ($record) {
                        return $record->users->pluck('name')->implode(', ');
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->tooltip(fn($record) => $record->created_at->diffForHumans())
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->locale('pt_BR')->isoFormat('dddd, DD/MM/YYYY HH:mm'))
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
                                    ->title('Unidade atualizada')
                                    ->body("Informações da unidade {$record->name} foram editadas com sucesso.")
                                    ->icon('heroicon-o-building-office')
                                    ->success()
                                    ->send();

                                return redirect(static::getUrl('index'));
                            }),

                        Tables\Actions\DeleteAction::make()
                            ->label('Excluir')
                            ->icon('heroicon-o-trash')
                            ->requiresConfirmation()
                            ->modalHeading('Excluir Unidade')
                            ->modalDescription(fn($record) => "Tem certeza que deseja excluir {$record->name}?")
                            ->modalSubmitActionLabel('Sim, excluir')
                            ->successNotificationTitle(null)
                            ->after(function ($record, $livewire) {

                                Notification::make()
                                    ->title('Usuario exluido')
                                    ->body("A unidade {$record->name} foi deletada dos nossos registros.")
                                    ->icon('heroicon-o-building-office')
                                    ->danger()
                                    ->send();

                                return redirect(static::getUrl('index'));
                            }),
                    ]),
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
            'index'  => Pages\ListUnidades::route('/'),
            'create' => Pages\CreateUnidade::route('/create'),
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
