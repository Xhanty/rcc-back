<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Concerns\HasModulePermissions;
use App\Filament\Resources\Administration\TeamMemberResource\Pages;
use App\Models\TeamMember;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class TeamMemberResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = TeamMember::class;

    protected static ?string $modelLabel = 'Miembro del Organigrama';

    protected static ?string $pluralModelLabel = 'Equipo de Servidores (Organigrama)';

    protected static function getPermissionModule(): string
    {
        return 'team_members';
    }

    protected static ?string $navigationLabel = 'Equipo de Servidores';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $recordTitleAttribute = 'display_title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Organigrama del Equipo de Servidores')
                    ->description('El orden visual en la web se organiza automáticamente en tipo Organigrama según el nivel jerárquico: 1. Director Espiritual → 2. Coordinador(a) → 3. Grupo Base → 4. Líderes 1 → 5. Líderes 2.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('role_group')
                                    ->label('Nivel en el Organigrama')
                                    ->options(TeamMember::roleGroupOptions())
                                    ->required()
                                    ->default(TeamMember::ROLE_LEADER_1)
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                        $userId = $get('user_id');
                                        $suggested = TeamMember::suggestDisplayTitle($userId ? (int) $userId : null, $state);
                                        if (filled($suggested)) {
                                            $set('display_title', $suggested);
                                        }
                                    }),

                                Select::make('user_id')
                                    ->label('Usuario del Sistema')
                                    ->options(function (?TeamMember $record): array {
                                        $assignedUserIds = TeamMember::query()
                                            ->whereNotNull('user_id')
                                            ->when($record?->id, fn (Builder $q) => $q->where('id', '!=', $record->id))
                                            ->pluck('user_id')
                                            ->all();

                                        return User::query()
                                            ->whereRaw('LOWER(email) != ?', ['admin@resucitadosrcc.com'])
                                            ->when(! empty($assignedUserIds), fn (Builder $q) => $q->whereNotIn('id', $assignedUserIds))
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live()
                                    ->unique(
                                        table: TeamMember::class,
                                        column: 'user_id',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')
                                    )
                                    ->validationMessages([
                                        'unique' => 'Este usuario ya forma parte del Organigrama.',
                                    ])
                                    ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                        $roleGroup = (string) ($get('role_group') ?: TeamMember::ROLE_LEADER_1);
                                        $suggested = TeamMember::suggestDisplayTitle($state ? (int) $state : null, $roleGroup);
                                        if (filled($suggested)) {
                                            $set('display_title', $suggested);
                                        }

                                        if ($state) {
                                            $user = User::find($state);
                                            if ($user) {
                                                $set('custom_occupation', $user->occupation);
                                                $set('custom_joined_since', $user->joined_since);
                                                $set('custom_testimony', $user->testimony);
                                                $set('custom_favorite_verse', $user->favorite_verse);
                                            }
                                        }
                                    })
                                    ->helperText('Toma automáticamente su foto de perfil, nombre, presentación y ministerios. Opcional si es externo (ej. Director Espiritual).'),
                            ]),

                        Placeholder::make('user_ministries_preview')
                            ->label('Datos detectados del Usuario')
                            ->visible(fn (Get $get): bool => filled($get('user_id')))
                            ->content(function (Get $get): HtmlString {
                                $user = User::with(['ledMinistries', 'servedMinistries'])->find($get('user_id'));
                                if (! $user) {
                                    return new HtmlString('<span class="text-sm text-gray-400">Usuario no encontrado.</span>');
                                }

                                $avatar = $user->getFilamentAvatarUrl() ?: asset('icon.png');
                                $led = $user->ledMinistries->pluck('title')->all();
                                $served = $user->servedMinistries->pluck('title')->all();

                                $ministryBadges = '';
                                foreach ($led as $title) {
                                    $e = e($title);
                                    $ministryBadges .= "<span style=\"display:inline-flex;align-items:center;padding:2px 8px;border-radius:9999px;font-size:12px;font-weight:600;background:rgba(245,158,11,0.15);color:#fbbf24;margin-right:6px;\">★ Líder: {$e}</span>";
                                }
                                foreach ($served as $title) {
                                    if (in_array($title, $led, true)) {
                                        continue;
                                    }
                                    $e = e($title);
                                    $ministryBadges .= "<span style=\"display:inline-flex;align-items:center;padding:2px 8px;border-radius:9999px;font-size:12px;font-weight:600;background:rgba(59,130,246,0.15);color:#60a5fa;margin-right:6px;\">Servidor: {$e}</span>";
                                }

                                if ($ministryBadges === '') {
                                    $ministryBadges = '<span style="font-size:12px;opacity:0.75;">Sin ministerio específico asignado</span>';
                                }

                                $name = e($user->name);
                                $avatarUrl = e($avatar);

                                return new HtmlString("
                                    <div style=\"display:flex;align-items:center;gap:14px;padding:10px 14px;border-radius:12px;border:1px solid rgba(156,163,175,0.2);background:rgba(156,163,175,0.05);\">
                                        <img src=\"{$avatarUrl}\" alt=\"{$name}\" style=\"width:48px;height:48px;border-radius:9999px;object-fit:cover;border:2px solid rgba(245,158,11,0.4);\" />
                                        <div style=\"display:flex;flex-direction:column;gap:4px;\">
                                            <span style=\"font-weight:700;font-size:14px;\">{$name}</span>
                                            <div>{$ministryBadges}</div>
                                        </div>
                                    </div>
                                ");
                            }),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('display_title')
                                    ->label('Título / Cargo visible en el Organigrama')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej. DIRECTOR ESPIRITUAL, COORDINADOR GENERAL, GRUPO BASE · LÍDER DE KOINONÍA...')
                                    ->helperText('Se sugiere automáticamente según el nivel del organigrama y sus ministerios, pero puedes editarlo.'),

                                TextInput::make('custom_name')
                                    ->label(fn (Get $get): string => filled($get('user_id'))
                                        ? 'Sobreescribir Nombre (Opcional · por defecto usa el del usuario)'
                                        : 'Nombre Completo (Obligatorio si no seleccionas usuario)')
                                    ->required(fn (Get $get): bool => blank($get('user_id')))
                                    ->maxLength(255)
                                    ->placeholder('Ej. Pbro. David Solano'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                FileUpload::make('custom_photo_path')
                                    ->label('Foto Personalizada (Opcional · por defecto usa el avatar del usuario)')
                                    ->image()
                                    ->disk('public')
                                    ->directory('team-members')
                                    ->visibility('public')
                                    ->maxSize(5120),

                                Toggle::make('is_active')
                                    ->label('Visible en el Organigrama Web')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),

                Section::make('Historia Corta y Presentación Guiada (Modal del Organigrama)')
                    ->description('Campos guiados con formato automático para que todas las tarjetas del Organigrama se vean uniformes. Si el usuario ya los completó en «Mi Perfil», se cargan automáticamente.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('custom_occupation')
                                    ->label('1. Profesión, Oficio o Vocación')
                                    ->prefix('Soy')
                                    ->maxLength(120)
                                    ->placeholder('Ej. Médica Pediatra, Sacerdote Palotino, Docente...')
                                    ->helperText('Solo la profesión u oficio (sin escribir «Yo soy»).'),

                                Select::make('custom_joined_since')
                                    ->label('2. ¿Desde qué año camina en Resucitados?')
                                    ->prefixIcon(Heroicon::OutlinedCalendarDays)
                                    ->options(TeamMember::joinedYearOptions())
                                    ->searchable()
                                    ->placeholder('Selecciona el año')
                                    ->helperText('Se mostrará siempre como «En Resucitados desde [Año]».'),
                            ]),

                        Textarea::make('custom_testimony')
                            ->label('3. Historia Corta en la Comunidad (Párrafo breve)')
                            ->rows(3)
                            ->maxLength(450)
                            ->placeholder('Ej. Llegué a la comunidad invitada a una asamblea cuando atravesaba un momento de mucha incertidumbre. Aquí encontré una familia en la fe que me enseñó a orar y a descubrir que Dios tenía un propósito hermoso para mi vida y mi vocación.')
                            ->helperText('Guía sugerida (máx. 450 caracteres): ¿Cómo llegó a la comunidad y cómo ha transformado Dios su vida al servir aquí?'),

                        TextInput::make('custom_favorite_verse')
                            ->label('4. Cita Bíblica o Frase que lo inspira (Sin comillas)')
                            ->prefixIcon(Heroicon::OutlinedSparkles)
                            ->maxLength(220)
                            ->placeholder('Ej. Todo lo puedo en Cristo que me fortalece — Filipenses 4, 13')
                            ->helperText('Las comillas « » se agregan automáticamente con el mismo estilo para todos.'),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->defaultGroup(
                Group::make('sort_order')
                    ->label('Nivel del Organigrama')
                    ->getTitleFromRecordUsing(fn (TeamMember $record): string => TeamMember::roleGroupOptions()[$record->role_group] ?? $record->role_group)
                    ->orderQueryUsing(fn (Builder $query, string $direction) => $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc'))
                    ->collapsible()
            )
            ->striped()
            ->searchPlaceholder('Buscar por nombre o título...')
            ->emptyStateHeading('No hay miembros registrados en el Organigrama')
            ->emptyStateDescription('Agrega al Director Espiritual, Coordinador, Grupo Base, Líderes 1 y Líderes 2.')
            ->emptyStateIcon(Heroicon::OutlinedIdentification)
            ->columns([
                ImageColumn::make('photo_url')
                    ->label('Foto')
                    ->circular()
                    ->height(44)
                    ->defaultImageUrl(asset('icon.png')),

                TextColumn::make('name')
                    ->label('Servidor / Líder')
                    ->searchable(['custom_name', 'display_title'])
                    ->weight('bold'),

                TextColumn::make('role_group')
                    ->label('Nivel del Organigrama')
                    ->formatStateUsing(fn (string $state): string => TeamMember::roleGroupOptions()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        TeamMember::ROLE_SPIRITUAL_DIRECTOR => 'primary',
                        TeamMember::ROLE_GENERAL_COORDINATOR => 'danger',
                        TeamMember::ROLE_BASE_GROUP => 'warning',
                        TeamMember::ROLE_LEADER_1 => 'success',
                        TeamMember::ROLE_LEADER_2 => 'info',
                        default => 'gray',
                    })
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('sort_order', $direction)),

                TextColumn::make('display_title')
                    ->label('Título en la Web')
                    ->searchable()
                    ->limit(45),

                ToggleColumn::make('is_active')
                    ->label('Visible')
                    ->disabled(fn (): bool => ! (Auth::user()?->hasPermission('team_members', 'edit') ?? false)),
            ])
            ->filters([
                SelectFilter::make('role_group')
                    ->label('Nivel del Organigrama')
                    ->options(TeamMember::roleGroupOptions()),

                TernaryFilter::make('is_active')
                    ->label('Visible en la Web'),

                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user.ledMinistries', 'user.servedMinistries'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit'   => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}
