<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    public const SECTION_HOME = 'home';
    public const SECTION_ABOUT = 'about';
    public const SECTION_ADMIN_LOGIN = 'admin_login';

    protected $fillable = [
        'title',
        'section',
        'image_path',
        'description',
        'link_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'image_path' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Secciones disponibles para los banners.
     *
     * @return array<string, string>
     */
    public static function sectionOptions(): array
    {
        return [
            self::SECTION_HOME => 'Página Principal (Inicio)',
            self::SECTION_ABOUT => 'Página Principal (Nosotros)',
            self::SECTION_ADMIN_LOGIN => 'Panel Administrativo (Inicio de Sesión)',
        ];
    }

    /**
     * Devuelve siempre un arreglo limpio con las rutas de imágenes del banner.
     *
     * @return list<string>
     */
    public function getImagePaths(): array
    {
        $raw = $this->image_path;

        if (is_array($raw)) {
            return array_values(array_filter($raw, fn ($path): bool => is_string($path) && filled($path)));
        }

        if (is_string($raw) && filled($raw)) {
            return [$raw];
        }

        return [];
    }

    /**
     * Secciones permitidas para ser consultadas desde el API público del frontend.
     *
     * @return list<string>
     */
    public static function frontendSections(): array
    {
        return [
            self::SECTION_HOME,
            self::SECTION_ABOUT,
        ];
    }

    /**
     * Determina si una ruta corresponde a un archivo de video.
     */
    public static function isVideoPath(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_EXTENSION));

        return in_array($ext, ['mp4', 'webm', 'mov', 'ogg'], true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForSection(Builder $query, string $section): Builder
    {
        return $query->where('section', $section);
    }
}
