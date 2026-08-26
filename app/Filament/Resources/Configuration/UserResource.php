<?php

namespace App\Filament\Resources\Configuration;

use App\Filament\Resources\Configuration\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre Completo')
                    ->prefixIcon(Heroicon::OutlinedUser)
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej. Juan Pérez'),

                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->prefixIcon(Heroicon::OutlinedEnvelope)
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('ejemplo@correo.com'),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->prefixIcon(Heroicon::OutlinedLockClosed)
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->maxLength(255)
                    ->visible(fn(string $operation): bool => $operation === 'create')
                    ->placeholder('Mínimo 8 caracteres'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->searchPlaceholder('Buscar por nombre o correo...')
            ->searchDebounce('500ms')
            ->emptyStateHeading('No hay usuarios registrados')
            ->emptyStateDescription('Crea un nuevo usuario para comenzar a gestionar el acceso a la plataforma.')
            ->emptyStateIcon(Heroicon::OutlinedUsers)
            ->columns([
                TextColumn::make('name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Correo copiado al portapapeles')
                    ->copyMessageDuration(1500),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Última Modificación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()->native(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Modificar')
                        ->icon(Heroicon::OutlinedPencil)
                        ->color('info')
                        ->modalHeading('Modificar Usuario')
                        ->modalSubmitActionLabel('Modificar')
                        ->modalCancelActionLabel('Cancelar')
                        ->modalWidth('md')
                        ->slideOver(),

                    Action::make('changePassword')
                        ->label('Cambiar contraseña')
                        ->icon(Heroicon::OutlinedKey)
                        ->color('warning')
                        ->modalHeading('Actualizar Contraseña')
                        ->modalDescription('Ingresa y confirma la nueva contraseña para este usuario.')
                        ->modalSubmitActionLabel('Actualizar')
                        ->modalCancelActionLabel('Cancelar')
                        ->modalWidth('md')
                        ->form([
                            TextInput::make('password')
                                ->label('Nueva Contraseña')
                                ->prefixIcon(Heroicon::OutlinedLockClosed)
                                ->password()
                                ->revealable()
                                ->required()
                                ->minLength(8)
                                ->maxLength(255)
                                ->placeholder('Ingresa la nueva contraseña'),
                            TextInput::make('password_confirmation')
                                ->label('Confirmar Contraseña')
                                ->prefixIcon(Heroicon::OutlinedLockClosed)
                                ->password()
                                ->revealable()
                                ->same('password')
                                ->required()
                                ->placeholder('Repite la nueva contraseña'),
                        ])
                        ->action(function (User $record, array $data): void {
                            $record->update([
                                'password' => $data['password'],
                            ]);

                            Notification::make()
                                ->title('Contraseña actualizada con éxito')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->icon(Heroicon::OutlinedTrash),

                    RestoreAction::make()
                        ->label('Restaurar')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('success'),

                    ForceDeleteAction::make()
                        ->label('Eliminar permanentemente')
                        ->icon(Heroicon::OutlinedTrash)
                        ->color('danger'),
                ])
                    ->tooltip('Opciones')
                    ->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
