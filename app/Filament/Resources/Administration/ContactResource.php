<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Resources\Administration\ContactResource\Pages;
use App\Models\Contact;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $modelLabel = 'Contacto';

    protected static ?string $pluralModelLabel = 'Contactos';

    protected static ?string $navigationLabel = 'Contactos';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->prefixIcon(Heroicon::OutlinedUser)
                    ->readOnly(),

                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->prefixIcon(Heroicon::OutlinedEnvelope)
                    ->readOnly(),

                TextInput::make('phone')
                    ->label('Teléfono')
                    ->prefixIcon(Heroicon::OutlinedPhone)
                    ->readOnly(),

                TextInput::make('subject')
                    ->label('Asunto')
                    ->prefixIcon(Heroicon::OutlinedChatBubbleBottomCenterText)
                    ->readOnly(),

                Textarea::make('message')
                    ->label('Mensaje')
                    ->rows(6)
                    ->readOnly(),

                TextInput::make('status')
                    ->label('Estado')
                    ->prefixIcon(Heroicon::OutlinedCheckCircle)
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        default => $state,
                    })
                    ->readOnly(),

                TextInput::make('created_at')
                    ->label('Fecha de Envío')
                    ->prefixIcon(Heroicon::OutlinedCalendar)
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i A') : null)
                    ->readOnly(),
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
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->sortable(),

                SelectColumn::make('status')
                    ->label('Estado')
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
                        ->modalWidth('lg')
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
