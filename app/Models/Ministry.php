<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ministry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'detail',
        'image_path',
        'leader_id',
        'meeting_day',
        'meeting_time',
        'meeting_location',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Días disponibles para las reuniones del ministerio.
     *
     * @return array<string, string>
     */
    public static function dayOptions(): array
    {
        return [
            'Lunes' => 'Lunes',
            'Martes' => 'Martes',
            'Miércoles' => 'Miércoles',
            'Jueves' => 'Jueves',
            'Viernes' => 'Viernes',
            'Sábado' => 'Sábado',
            'Domingo' => 'Domingo',
            'Varios días' => 'Varios días (ver horario)',
        ];
    }

    /**
     * Líderes del ministerio (usuarios del panel administrativo).
     */
    public function leaders(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ministry_leader')->withTimestamps();
    }

    /**
     * Líder principal (compatibilidad).
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Servidores que acompañan y apoyan el ministerio (usuarios del panel administrativo).
     */
    public function servants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ministry_user')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function booted(): void
    {
        static::creating(function (Ministry $ministry): void {
            if (empty($ministry->slug) && ! empty($ministry->title)) {
                $baseSlug = Str::slug($ministry->title);
                $slug = $baseSlug;
                $counter = 1;

                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                $ministry->slug = $slug;
            }
        });
    }
}
