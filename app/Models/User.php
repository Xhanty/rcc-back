<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar_url',
        'occupation',
        'joined_since',
        'testimony',
        'favorite_verse',
        'password',
        'modules',
        'permissions',
        'is_super_admin',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'modules' => 'array',
            'permissions' => 'array',
            'is_super_admin' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->must_change_password === null) {
                $user->must_change_password = true;
            }
        });
    }

    /**
     * Check if user has access to a specific module.
     */
    public function hasModuleAccess(string $module): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (is_array($this->permissions)) {
            return ! empty($this->permissions[$module]);
        }

        return in_array($module, $this->modules ?? [], true);
    }

    /**
     * Check if user has a specific sub-permission (action) inside a module.
     */
    public function hasPermission(string $module, string $action): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (is_array($this->permissions)) {
            $actions = $this->permissions[$module] ?? [];

            if ($action === 'view') {
                return ! empty($actions);
            }

            return in_array($action, $actions, true);
        }

        // Compatibilidad con usuarios antiguos que solo tenían módulos generales asignados
        return in_array($module, $this->modules ?? [], true);
    }

    /**
     * Definición centralizada de módulos y sus subpermisos (botones/acciones disponibles).
     *
     * @return array<string, array{label: string, actions: array<string, string>}>
     */
    public static function modulePermissionSchema(): array
    {
        return [
            'attendance' => [
                'label' => 'Asistencia (Registro Rápido)',
                'actions' => [
                    'view' => 'Ver asistencia',
                    'create' => 'Registrar asistencia',
                ],
            ],
            'assistants' => [
                'label' => 'Asistentes',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar',
                    'delete' => 'Eliminar',
                ],
            ],
            'banners' => [
                'label' => 'Banners',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar / Activar / Ordenar',
                    'delete' => 'Eliminar',
                ],
            ],
            'songs' => [
                'label' => 'Canciones',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar',
                    'delete' => 'Eliminar',
                ],
            ],
            'contacts' => [
                'label' => 'Contactos (Mensajes)',
                'actions' => [
                    'view' => 'Ver',
                    'edit' => 'Cambiar estado',
                    'delete' => 'Eliminar',
                ],
            ],
            'polls' => [
                'label' => 'Encuestas',
                'actions' => [
                    'view' => 'Ver y conteo',
                    'create' => 'Crear',
                    'edit' => 'Modificar / Activar',
                    'delete' => 'Eliminar',
                ],
            ],
            'team_members' => [
                'label' => 'Equipo de Servidores',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar / Ordenar',
                    'delete' => 'Eliminar',
                ],
            ],
            'events' => [
                'label' => 'Eventos',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar / Estado',
                    'delete' => 'Eliminar',
                ],
            ],
            'reflections' => [
                'label' => 'Lecturas y Reflexiones',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar / Destacar',
                    'delete' => 'Eliminar',
                ],
            ],
            'ministries' => [
                'label' => 'Ministerios',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar / Visibilidad',
                    'delete' => 'Eliminar',
                ],
            ],
            'prayers' => [
                'label' => 'Oraciones y Novenas',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar',
                    'delete' => 'Eliminar',
                ],
            ],
            'petitions' => [
                'label' => 'Peticiones de Oración',
                'actions' => [
                    'view' => 'Ver',
                    'edit' => 'Cambiar estado',
                    'delete' => 'Eliminar',
                ],
            ],
            'social_media' => [
                'label' => 'Redes y Medios Digitales',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar / Visibilidad',
                    'delete' => 'Eliminar',
                ],
            ],
            'reports' => [
                'label' => 'Reportes',
                'actions' => [
                    'view' => 'Ver reportes',
                    'export' => 'Descargar Excel (XLSX)',
                ],
            ],
            'event_types' => [
                'label' => 'Tipo de Eventos',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar',
                    'delete' => 'Eliminar',
                ],
            ],
            'users' => [
                'label' => 'Usuarios (Configuración)',
                'actions' => [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Modificar / Permisos / Seguridad',
                    'delete' => 'Eliminar',
                ],
            ],
        ];
    }

    /**
     * Etiquetas legibles de todos los módulos configurables del sistema.
     *
     * @return array<string, string>
     */
    public static function moduleLabels(): array
    {
        return collect(static::modulePermissionSchema())
            ->mapWithKeys(fn (array $config, string $key): array => [$key => $config['label']])
            ->all();
    }

    /**
     * Devuelve los permisos efectivos del usuario por módulo (incluyendo fallback legacy).
     *
     * @return array<string, list<string>>
     */
    public function getEffectivePermissions(): array
    {
        $schema = static::modulePermissionSchema();

        if ($this->is_super_admin) {
            return collect($schema)
                ->mapWithKeys(fn (array $config, string $key): array => [$key => array_keys($config['actions'])])
                ->all();
        }

        if (is_array($this->permissions)) {
            return $this->permissions;
        }

        $legacyModules = $this->modules ?? [];

        return collect($schema)
            ->filter(fn (array $config, string $key): bool => in_array($key, $legacyModules, true))
            ->mapWithKeys(fn (array $config, string $key): array => [$key => array_keys($config['actions'])])
            ->all();
    }

    /**
     * Indica si es la cuenta genérica de administrador del sistema (que no es asistente).
     */
    public function isSystemAdminAccount(): bool
    {
        return strtolower((string) $this->email) === 'admin@resucitadosrcc.com';
    }

    /**
     * Registro de asistente vinculado por correo electrónico.
     */
    public function assistant(): HasOne
    {
        return $this->hasOne(Assistant::class, 'email', 'email');
    }

    /**
     * Ministerios donde este usuario es líder.
     */
    public function ledMinistries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_leader')->withTimestamps();
    }

    /**
     * Ministerios donde este usuario participa como servidor.
     */
    public function servedMinistries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_user')->withTimestamps();
    }

    /**
     * Ficha del usuario en el Equipo de Servidores / Liderazgo de la Comunidad.
     */
    public function teamMember(): HasOne
    {
        return $this->hasOne(TeamMember::class);
    }

    /**
     * URL del avatar para la barra superior y el perfil de Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->avatar_url)) {
            return null;
        }

        if (str_starts_with($this->avatar_url, 'http://') || str_starts_with($this->avatar_url, 'https://')) {
            return $this->avatar_url;
        }

        return Storage::disk('public')->url($this->avatar_url);
    }
}
