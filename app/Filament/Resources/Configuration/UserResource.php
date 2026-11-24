<?php

namespace App\Filament\Resources\Configuration;

use App\Filament\Concerns\HasModulePermissions;
use App\Filament\Resources\Configuration\UserResource\Pages;
use App\Models\Assistant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class UserResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static function getPermissionModule(): string
    {
        return 'users';
    }

    protected static ?string $navigationLabel = 'Usuarios';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->prefixIcon(Heroicon::OutlinedEnvelope)
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Pega el correo para autocompletar desde Asistentes...')
                    ->live(debounce: 300)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                        $email = strtolower(trim((string) $state));
                        if ($email === '') {
                            return;
                        }

                        $assistant = Assistant::query()
                            ->whereRaw('LOWER(email) = ?', [$email])
                            ->first();

                        if ($assistant && filled($assistant->name)) {
                            $set('name', $assistant->name);
                        }

                        if (blank($get('password'))) {
                            $set('password', '12345678');
                        }
                    })
                    ->helperText(function (?string $state): ?string {
                        $email = strtolower(trim((string) $state));
                        if ($email === '') {
                            return 'Si el correo existe en Asistentes, se autocompletará el nombre.';
                        }

                        $assistant = Assistant::query()
                            ->whereRaw('LOWER(email) = ?', [$email])
                            ->first();

                        return $assistant
                            ? "✅ Asistente encontrado: {$assistant->name}"
                            : null;
                    }),

                TextInput::make('name')
                    ->label('Nombre Completo')
                    ->prefixIcon(Heroicon::OutlinedUser)
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej. Juan Pérez'),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->prefixIcon(Heroicon::OutlinedLockClosed)
                    ->password()
                    ->revealable()
                    ->required()
                    ->default('12345678')
                    ->minLength(8)
                    ->maxLength(255)
                    ->visible(fn(string $operation): bool => $operation === 'create')
                    ->placeholder('Mínimo 8 caracteres')
                    ->helperText('El usuario deberá cambiar obligatoriamente esta contraseña en su primer inicio de sesión.'),
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

                IconColumn::make('is_super_admin')
                    ->label('Admin')
                    ->alignCenter()
                    ->boolean()
                    ->sortable(),

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

                            Toggle::make('must_change_password')
                                ->label('Exigir cambio de contraseña en el próximo inicio')
                                ->default(false),
                        ])
                        ->action(function (User $record, array $data): void {
                            $record->update([
                                'password' => $data['password'],
                                'must_change_password' => $data['must_change_password'] ?? false,
                            ]);

                            Notification::make()
                                ->title('Contraseña actualizada con éxito')
                                ->success()
                                ->send();
                        }),

                    Action::make('assignModules')
                        ->label('Asignar permisos')
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->color('success')
                        ->visible(fn (): bool => Auth::user()?->hasPermission('users', 'edit') ?? false)
                        ->modalHeading('Asignar Módulos y Subpermisos')
                        ->modalDescription('Configura el rol de Administrador Maestro o elige exactamente qué acciones (botones) puede realizar este usuario en cada módulo.')
                        ->modalSubmitActionLabel('Guardar permisos')
                        ->modalCancelActionLabel('Cancelar')
                        ->modalWidth('4xl')
                        ->fillForm(function (User $record): array {
                            $effective = $record->is_super_admin ? [] : $record->getEffectivePermissions();
                            $enabledModules = [];

                            foreach (array_keys(User::modulePermissionSchema()) as $moduleKey) {
                                $enabledModules[$moduleKey] = ! empty($effective[$moduleKey]);
                            }

                            return [
                                'is_super_admin' => $record->is_super_admin,
                                'enabled_modules' => $enabledModules,
                                'permissions' => $effective,
                            ];
                        })
                        ->form(function (): array {
                            $moduleCards = [];

                            foreach (User::modulePermissionSchema() as $moduleKey => $config) {
                                $totalActions = count($config['actions']);

                                $moduleCards[] = Section::make()
                                    ->compact()
                                    ->schema([
                                        Toggle::make("enabled_modules.{$moduleKey}")
                                            ->label($config['label'])
                                            ->helperText(function (Get $get) use ($moduleKey, $totalActions): string {
                                                if (! $get("enabled_modules.{$moduleKey}")) {
                                                    return 'Sin acceso · Actívalo para desplegar los subpermisos';
                                                }

                                                $selectedCount = count((array) ($get("permissions.{$moduleKey}") ?? []));

                                                return "{$selectedCount} de {$totalActions} subpermisos activos";
                                            })
                                            ->live()
                                            ->afterStateUpdated(function (bool $state, Get $get, Set $set) use ($moduleKey): void {
                                                if ($state) {
                                                    $current = (array) ($get("permissions.{$moduleKey}") ?? []);
                                                    if (empty($current)) {
                                                        $set("permissions.{$moduleKey}", ['view']);
                                                    }
                                                } else {
                                                    $set("permissions.{$moduleKey}", []);
                                                }
                                            }),

                                        Section::make('Subpermisos del módulo')
                                            ->compact()
                                            ->collapsible()
                                            ->visible(fn (Get $get): bool => (bool) $get("enabled_modules.{$moduleKey}"))
                                            ->schema([
                                                CheckboxList::make("permissions.{$moduleKey}")
                                                    ->hiddenLabel()
                                                    ->options($config['actions'])
                                                    ->columns(2)
                                                    ->bulkToggleable()
                                                    ->live(),
                                            ]),
                                    ]);
                            }

                            return [
                                Toggle::make('is_super_admin')
                                    ->label('Administrador Maestro (Acceso Total a todos los módulos y acciones)')
                                    ->live(),

                                Grid::make(2)
                                    ->schema($moduleCards)
                                    ->hidden(fn (Get $get): bool => (bool) $get('is_super_admin')),
                            ];
                        })
                        ->action(function (User $record, array $data): void {
                            if ($data['is_super_admin']) {
                                $record->update([
                                    'is_super_admin' => true,
                                    'modules' => null,
                                    'permissions' => null,
                                ]);
                            } else {
                                $enabledModules = $data['enabled_modules'] ?? [];
                                $rawPermissions = $data['permissions'] ?? [];
                                $cleanPermissions = [];

                                foreach (User::modulePermissionSchema() as $moduleKey => $config) {
                                    if (empty($enabledModules[$moduleKey])) {
                                        continue;
                                    }

                                    $selected = array_values(array_intersect(
                                        array_keys($config['actions']),
                                        is_array($rawPermissions[$moduleKey] ?? null) ? $rawPermissions[$moduleKey] : []
                                    ));

                                    if (empty($selected) && array_key_exists('view', $config['actions'])) {
                                        $selected = ['view'];
                                    }

                                    if (! empty($selected)) {
                                        if (array_key_exists('view', $config['actions']) && ! in_array('view', $selected, true)) {
                                            array_unshift($selected, 'view');
                                        }
                                        $cleanPermissions[$moduleKey] = $selected;
                                    }
                                }

                                $record->update([
                                    'is_super_admin' => false,
                                    'modules' => array_keys($cleanPermissions),
                                    'permissions' => $cleanPermissions,
                                ]);
                            }

                            Notification::make()
                                ->title('Módulos y subpermisos actualizados con éxito')
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
