<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Concerns\HasModulePermissions;
use App\Filament\Resources\Administration\ContactResource\Pages;
use App\Models\Contact;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ContactResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = Contact::class;

    protected static ?string $modelLabel = 'Contacto';

    protected static ?string $pluralModelLabel = 'Contactos';

    protected static function getPermissionModule(): string
    {
        return 'contacts';
    }

    protected static ?string $navigationLabel = 'Contactos';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del Mensaje')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->readOnly(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->prefixIcon(Heroicon::OutlinedEnvelope)
                                    ->placeholder('Sin correo')
                                    ->readOnly(),

                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->prefixIcon(Heroicon::OutlinedPhone)
                                    ->placeholder('Sin teléfono')
                                    ->readOnly(),
                            ]),

                        TextInput::make('subject')
                            ->label('Asunto')
                            ->prefixIcon(Heroicon::OutlinedChatBubbleBottomCenterText)
                            ->readOnly(),

                        Textarea::make('message')
                            ->label('Mensaje')
                            ->rows(5)
                            ->readOnly(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('status')
                                    ->label('Estado')
                                    ->prefixIcon(Heroicon::OutlinedCheckCircle)
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'pending' => 'Pendiente',
                                        'completed' => 'Completado',
                                        default => (string) $state,
                                    })
                                    ->readOnly(),

                                TextInput::make('created_at')
                                    ->label('Fecha de Envío')
                                    ->prefixIcon(Heroicon::OutlinedCalendar)
                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/m/Y H:i A') : null)
                                    ->readOnly(),
                            ]),
                    ]),

                Section::make('Prueba de Autorización Habeas Data (Ley 1581 de 2012)')
                    ->description('Soporte probatorio de autorización de tratamiento de datos personales para consultas o solicitudes ARCO ante la SIC.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('accepted_terms')
                                    ->label('Estado de Autorización')
                                    ->prefixIcon(Heroicon::OutlinedShieldCheck)
                                    ->formatStateUsing(fn ($state, ?Contact $record): string => $record?->accepted_terms
                                        ? '✅ Autorizó datos (' . ($record->terms_version ?: '2026-v1') . ')'
                                        : 'Sin datos de contacto / Anónimo')
                                    ->readOnly(),

                                TextInput::make('terms_accepted_at')
                                    ->label('Fecha y Hora de Aceptación')
                                    ->prefixIcon(Heroicon::OutlinedClock)
                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/m/Y H:i:s A') : 'N/A')
                                    ->readOnly(),

                                TextInput::make('terms_version')
                                    ->label('Versión de Política Aceptada')
                                    ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                                    ->readOnly(),

                                TextInput::make('consent_ip')
                                    ->label('Dirección IP de Origen')
                                    ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                                    ->readOnly(),
                            ]),

                        Textarea::make('consent_user_agent')
                            ->label('Navegador / Dispositivo (User-Agent)')
                            ->rows(2)
                            ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                            ->readOnly(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->searchPlaceholder('Buscar por nombre o asunto...')
            ->searchDebounce('500ms')
            ->emptyStateHeading('No hay mensajes de contacto')
            ->emptyStateDescription('Los mensajes enviados desde la web aparecerán aquí.')
            ->emptyStateIcon(Heroicon::OutlinedEnvelope)
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('accepted_terms')
                    ->label('Habeas Data (Ley 1581)')
                    ->badge()
                    ->formatStateUsing(fn (bool $state, Contact $record): string => $state
                        ? '✅ Autorizó datos (' . ($record->terms_version ?: '2026-v1') . ')'
                        : 'Sin datos de contacto / Anónimo')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->description(fn (Contact $record): ?string => $record->accepted_terms && $record->terms_accepted_at
                        ? $record->terms_accepted_at->format('d/m/Y H:i A')
                        : null)
                    ->sortable(),

                SelectColumn::make('status')
                    ->label('Estado')
                    ->disabled(fn (): bool => ! (Auth::user()?->hasPermission('contacts', 'edit') ?? false))
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                    ])
                    ->sortable()
                    ->selectablePlaceholder(false),

                TextColumn::make('created_at')
                    ->label('Fecha de Envío')
                    ->dateTime('d/m/Y H:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendientes',
                        'completed' => 'Completados',
                    ]),

                TernaryFilter::make('accepted_terms')
                    ->label('Autorización Habeas Data')
                    ->trueLabel('Con autorización firmada')
                    ->falseLabel('Sin datos de contacto / Anónimo'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Ver Mensaje')
                        ->icon(Heroicon::OutlinedEye)
                        ->color('info')
                        ->modalHeading('Detalle del Mensaje de Contacto')
                        ->modalSubmitActionLabel(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->modalWidth('xl')
                        ->slideOver(),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->icon(Heroicon::OutlinedTrash),
                ])
                    ->tooltip('Opciones')
                    ->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContacts::route('/'),
        ];
    }
}
