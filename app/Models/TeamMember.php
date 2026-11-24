<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TeamMember extends Model
{
    use HasFactory, SoftDeletes;

    public const ROLE_SPIRITUAL_DIRECTOR = 'spiritual_director';
    public const ROLE_GENERAL_COORDINATOR = 'general_coordinator';
    public const ROLE_BASE_GROUP = 'base_group';
    public const ROLE_LEADER_1 = 'leader_1';
    public const ROLE_LEADER_2 = 'leader_2';

    protected $fillable = [
        'user_id',
        'custom_name',
        'custom_photo_path',
        'role_group',
        'display_title',
        'custom_occupation',
        'custom_joined_since',
        'custom_testimony',
        'custom_favorite_verse',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Niveles del Organigrama en orden jerárquico estricto (1 a 5).
     *
     * @return array<string, string>
     */
    public static function roleGroupOptions(): array
    {
        return [
            self::ROLE_SPIRITUAL_DIRECTOR => '1. Director Espiritual',
            self::ROLE_GENERAL_COORDINATOR => '2. Coordinador(a) de la Comunidad',
            self::ROLE_BASE_GROUP => '3. Grupo Base (Fundadores)',
            self::ROLE_LEADER_1 => '4. Líderes 1',
            self::ROLE_LEADER_2 => '5. Líderes 2',
        ];
    }

    /**
     * Etiqueta limpia del nivel sin el número prefijo.
     *
     * @return array<string, string>
     */
    public static function roleGroupCleanLabels(): array
    {
        return [
            self::ROLE_SPIRITUAL_DIRECTOR => 'Director Espiritual',
            self::ROLE_GENERAL_COORDINATOR => 'Coordinador(a) de la Comunidad',
            self::ROLE_BASE_GROUP => 'Grupo Base',
            self::ROLE_LEADER_1 => 'Líderes 1',
            self::ROLE_LEADER_2 => 'Líderes 2',
        ];
    }

    /**
     * Nivel numérico del organigrama (1 = cabeza, 5 = último nivel).
     */
    public static function hierarchyLevel(?string $roleGroup): int
    {
        return match ($roleGroup) {
            self::ROLE_SPIRITUAL_DIRECTOR => 1,
            self::ROLE_GENERAL_COORDINATOR => 2,
            self::ROLE_BASE_GROUP => 3,
            self::ROLE_LEADER_1 => 4,
            self::ROLE_LEADER_2 => 5,
            default => 99,
        };
    }

    protected static function booted(): void
    {
        static::saving(function (TeamMember $member): void {
            $member->sort_order = static::hierarchyLevel($member->role_group);

            if ($member->user_id && ($member->isDirty('custom_occupation') || $member->isDirty('custom_joined_since') || $member->isDirty('custom_testimony') || $member->isDirty('custom_favorite_verse'))) {
                $user = User::find($member->user_id);
                if ($user) {
                    $user->update([
                        'occupation' => $member->custom_occupation ?? $user->occupation,
                        'joined_since' => $member->custom_joined_since ?? $user->joined_since,
                        'testimony' => $member->custom_testimony ?? $user->testimony,
                        'favorite_verse' => $member->custom_favorite_verse ?? $user->favorite_verse,
                    ]);
                }
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Nombre a mostrar (prioriza el personalizado si existe, o el del usuario vinculado).
     */
    public function getNameAttribute(): string
    {
        return $this->custom_name ?: ($this->user?->name ?? 'Servidor');
    }

    /**
     * Profesión u ocupación normalizada (sin prefijos "Soy..." repetidos y con mayúscula inicial).
     */
    public function getOccupationAttribute(): ?string
    {
        $raw = $this->user?->occupation ?: $this->custom_occupation;

        return static::normalizeOccupation($raw);
    }

    /**
     * Año limpio (4 dígitos) desde que camina en la comunidad.
     */
    public function getJoinedYearAttribute(): ?string
    {
        $raw = $this->user?->joined_since ?: $this->custom_joined_since;

        return static::extractYear($raw);
    }

    /**
     * Etiqueta uniforme para todos: "En Resucitados desde 2021".
     */
    public function getJoinedSinceAttribute(): ?string
    {
        $year = $this->joined_year;

        return $year ? "En Resucitados desde {$year}" : null;
    }

    /**
     * Historia corta de vida normalizada (mayúscula inicial y punto final).
     */
    public function getTestimonyAttribute(): ?string
    {
        $raw = $this->user?->testimony ?: $this->custom_testimony;

        return static::normalizeTestimony($raw);
    }

    /**
     * Versículo o frase limpia (sin comillas duplicadas para que el front las pinte siempre igual).
     */
    public function getFavoriteVerseAttribute(): ?string
    {
        $raw = $this->user?->favorite_verse ?: $this->custom_favorite_verse;

        return static::normalizeVerse($raw);
    }

    /**
     * Opciones de años para el selector guiado "¿Desde qué año caminas en Resucitados?".
     *
     * @return array<string, string>
     */
    public static function joinedYearOptions(): array
    {
        $currentYear = (int) now()->format('Y');
        $options = [];

        for ($year = $currentYear; $year >= 2005; $year--) {
            $options[(string) $year] = "Desde {$year}";
        }

        return $options;
    }

    public static function extractYear(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (preg_match('/\b(19\d{2}|20\d{2})\b/', (string) $value, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function normalizeOccupation(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $clean = trim((string) $value);
        $clean = preg_replace('/^(hola[,\s]+)?(yo\s+)?soy\s+/iu', '', $clean) ?: $clean;
        $clean = rtrim($clean, '. ');

        return $clean !== '' ? mb_strtoupper(mb_substr($clean, 0, 1)) . mb_substr($clean, 1) : null;
    }

    public static function normalizeTestimony(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $clean = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
        if ($clean === '') {
            return null;
        }

        $clean = mb_strtoupper(mb_substr($clean, 0, 1)) . mb_substr($clean, 1);

        if (! preg_match('/[.!?…]$/u', $clean)) {
            $clean .= '.';
        }

        return $clean;
    }

    public static function normalizeVerse(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $clean = trim((string) $value);
        $clean = trim($clean, " \"'«»“”");

        return $clean !== '' ? "«{$clean}»" : null;
    }

    /**
     * URL de la fotografía (prioriza foto personalizada si se subió, o el avatar del usuario vinculado).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (filled($this->custom_photo_path)) {
            if (str_starts_with($this->custom_photo_path, 'http://') || str_starts_with($this->custom_photo_path, 'https://')) {
                return $this->custom_photo_path;
            }

            return url(Storage::disk('public')->url($this->custom_photo_path));
        }

        $userAvatar = $this->user?->getFilamentAvatarUrl();

        return $userAvatar ? url($userAvatar) : null;
    }

    /**
     * Sugiere automáticamente el título público (subtítulo de la tarjeta) según el nivel del organigrama y los ministerios del usuario.
     */
    public static function suggestDisplayTitle(?int $userId, ?string $roleGroup): string
    {
        $roleLabel = match ($roleGroup) {
            self::ROLE_SPIRITUAL_DIRECTOR => 'Director Espiritual',
            self::ROLE_GENERAL_COORDINATOR => 'Coordinador General',
            self::ROLE_BASE_GROUP => 'Grupo Base',
            self::ROLE_LEADER_1 => 'Líder 1',
            self::ROLE_LEADER_2 => 'Líder 2',
            default => '',
        };

        if (! $userId) {
            return $roleLabel;
        }

        $user = User::with(['ledMinistries'])->find($userId);
        if (! $user) {
            return $roleLabel;
        }

        $ledTitles = $user->ledMinistries->pluck('title')->filter()->values();

        if ($ledTitles->isNotEmpty() && in_array($roleGroup, [self::ROLE_BASE_GROUP, self::ROLE_LEADER_1, self::ROLE_LEADER_2], true)) {
            return $roleLabel . ' · Líder de ' . $ledTitles->join(', ');
        }

        return $roleLabel;
    }
}
