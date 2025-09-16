<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequisicoesResource\Pages;
use App\Models\Academia;
use App\Models\Requisicoes;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;


class RequisicoesResource extends Resource
{
    protected static ?string $model = Requisicoes::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([

            // --- Seção de Informações da Requisição ---
            Forms\Components\Section::make('Informações da Requisição')
                ->visible(
                    fn() =>
                    in_array(Filament::auth()->user()?->cargo, [
                        'administrador',
                        'gerente',
                        'funcionario',
                        'atendente'
                    ])
                )
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Select::make('unidade_id')
                                ->label('Unidade')
                                ->options(Academia::all()->pluck('name', 'id'))
                                ->required()
                                ->disabled(fn() => in_array(
                                    Filament::auth()->user()?->cargo,
                                    ['atendente', 'gerente']
                                )),

                            Toggle::make('emergencial')
                                ->label('Emergencial')
                                ->default(false)
                                ->disabled(fn() => in_array(
                                    Filament::auth()->user()?->cargo,
                                    ['atendente', 'gerente']
                                )),
                        ]),

                    Select::make('user_id')
                        ->label('Atendente')
                        ->options(User::where('cargo', 'atendente')->pluck('name', 'id'))
                        ->required()
                        ->disabled(fn() => in_array(
                            Filament::auth()->user()?->cargo,
                            ['atendente', 'gerente']
                        )),

                    Textarea::make('relato')
                        ->label('Relato do problema')
                        ->required()
                        ->rows(5)
                        ->disabled(fn() => in_array(
                            Filament::auth()->user()?->cargo,
                            ['atendente', 'gerente']
                        )),

                    FileUpload::make('foto')
                        ->label('Foto')
                        ->image()
                        ->disk('public')
                        ->directory('requisicoes')
                        ->nullable()
                        ->disabled(fn() => in_array(
                            Filament::auth()->user()?->cargo,
                            ['atendente', 'gerente']
                        )),
                ])
                ->columns(1)
                ->columnSpanFull(),

            // --- Seção para Atendimento (ATENDENTE pode editar) ---
            Forms\Components\Section::make('Atendimento')
                ->visible(fn() => in_array(
                    Filament::auth()->user()?->cargo,
                    ['atendente', 'gerente']
                ))
                ->schema([
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Select::make('gestor_id')
                                ->label('Encaminhar para Gerente')
                                ->options(
                                    User::where('cargo', 'gerente')->pluck('name', 'id')
                                )
                                ->searchable()
                                ->required()
                                ->placeholder('Selecione o gestor responsável')
                                ->disabled(fn() => in_array(
                                    Filament::auth()->user()?->cargo,
                                    ['gerente']
                                )),

                            Textarea::make('nota_atendimento')
                                ->label('Nota do Atendimento')
                                ->autosize()
                                ->disabled(fn() => in_array(
                                    Filament::auth()->user()?->cargo,
                                    ['gerente']
                                ))
                                ->nullable(),
                        ]),
                ]),

            // --- Seção para Aprovação do Gerente ---
            Forms\Components\Section::make('Aprovação do Gerente')
                ->visible(fn() => Filament::auth()->user()?->cargo === 'gerente')
                ->schema([
                    Textarea::make('nota_aprovacao')
                        ->label('Observações do Gerente')
                        ->autosize()
                        ->nullable(),
                ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
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
                    ->label('Foto')
                    ->disk('public')
                    ->height(60)
                    ->width(60)
                    ->rounded()
                    ->extraImgAttributes(['class' => 'object-cover'])
                    ->visibility('public')
                    ->openUrlInNewTab(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($record): string|array|null => match ($record->status) {
                        'pendente'    => Color::Gray,
                        'atendimento' => Color::Yellow,
                        'aprovacao'   => Color::Blue,
                        'concluido'   => Color::Green,
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pendente'    => 'Pendente',
                        'atendimento' => 'Em Atendimento',
                        'aprovacao'   => 'Aprovação',
                        'concluido'   => 'Concluído',
                    })
                    ->sortable()
                    ->searchable(),

                IconColumn::make('emergencial')
                    ->label('Emergencial')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->tooltip(fn($record) => $record->created_at->diffForHumans())
                    ->formatStateUsing(
                        fn($state) => Carbon::parse($state)
                            ->locale('pt_BR')
                            ->isoFormat('dddd, DD/MM/YYYY HH:mm')
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente'    => 'Pendente',
                        'atendimento' => 'Em Atendimento',
                        'aprovacao'   => 'Aprovação',
                        'concluido'   => 'Concluído',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(fn() => Filament::auth()->user()?->cargo === 'atendente'
                        ? 'Atender'
                        : 'Detalhes')
                    ->icon(fn() => Filament::auth()->user()?->cargo === 'atendente'
                        ? 'heroicon-o-clipboard-document-check'
                        : 'heroicon-o-document-text')
                    ->modalHeading(fn() => Filament::auth()->user()?->cargo === 'atendente'
                        ? 'Atender Requisição'
                        : 'Visualizar Requisição')
                    ->slideOver()
                    ->color('primary')
                    ->extraModalFooterActions([
                        Tables\Actions\EditAction::make()
                            ->label(function () {
                                return match (Filament::auth()->user()?->cargo) {
                                    'atendente' => 'Atender',
                                    'gerente'   => 'Aprovação',
                                    default     => 'Editar',
                                };
                            })
                            ->icon(function () {
                                return match (Filament::auth()->user()?->cargo) {
                                    'atendente' => 'heroicon-o-clipboard-document-check',
                                    'gerente'   => 'heroicon-o-check-circle',
                                    default     => 'heroicon-o-pencil-square',
                                };
                            })
                            ->color(function () {
                                return match (Filament::auth()->user()?->cargo) {
                                    'atendente' => 'info',
                                    'gerente'   => 'success',
                                    default     => 'info',
                                };
                            })
                            ->disabled(fn($record) => match (Filament::auth()->user()?->cargo) {
                                'atendente' => $record->status !== 'pendente',
                                'gerente'   => $record->status !== 'atendimento',
                                default     => false,
                            })
                            ->slideOver()
                            ->modalHeading(fn() => match (Filament::auth()->user()?->cargo) {
                                'gerente'   => 'Aprovar Requisição',
                                'atendente' => 'Atender Requisição',
                                default     => 'Editar Requisição',
                            })
                            ->modalSubmitActionLabel(fn() => match (Filament::auth()->user()?->cargo) {
                                'gerente'   => 'Aprovar',
                                'atendente' => 'Concluir Atendimento',
                                default     => 'Salvar',
                            })
                            ->after(function ($record, $livewire) {
                                $user = Filament::auth()->user();

                                if ($user?->cargo === 'atendente') {
                                    $record->update(['status' => 'atendimento']);
                                } elseif ($user?->cargo === 'gerente') {
                                    $record->update(['status' => 'concluido']);
                                }

                                $livewire->dispatch('refresh');

                                Notification::make()
                                    ->title('Requisição atualizada')
                                    ->body(match ($user?->cargo) {
                                        'atendente' => "Atendimento salvo e enviado para o gerente.",
                                        'gerente'   => "Requisição aprovada e marcada como concluída.",
                                        default     => "Registro atualizado.",
                                    })
                                    ->icon('heroicon-o-check')
                                    ->success()
                                    ->sendToDatabase($user)
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
                                    ->title('Unidade excluída')
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRequisicoes::route('/'),
            'create' => Pages\CreateRequisicoes::route('/create'),
            // edição feita via slideOver do EditAction
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Filament::auth()->user();

        return match ($user?->cargo) {
            'atendente'   => $query->where('status', 'pendente'),
            'gerente'     => $query->where('status', 'atendimento'),
            default       => $query,
        };
    }
}
