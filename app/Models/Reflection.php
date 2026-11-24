<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Reflection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'content',
        'image_path',
        'reading_time_minutes',
        'user_id',
        'author_name',
        'published_at',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'reading_time_minutes' => 'integer',
            'user_id' => 'integer',
            'published_at' => 'date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Reflection $reflection): void {
            if (($reflection->isDirty('title') || blank($reflection->slug)) && filled($reflection->title)) {
                $baseSlug = Str::slug($reflection->title);
                $slug = $baseSlug;
                $counter = 2;

                while (static::withTrashed()
                    ->where('slug', $slug)
                    ->when($reflection->id, fn (Builder $q) => $q->where('id', '!=', $reflection->id))
                    ->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }

                $reflection->slug = $slug;
            }

            if (blank($reflection->published_at)) {
                $reflection->published_at = now()->toDateString();
            }

            if (! $reflection->reading_time_minutes || $reflection->reading_time_minutes < 1) {
                $reflection->reading_time_minutes = static::estimateReadingTime((string) $reflection->content);
            }
        });
    }

    /**
     * Calcula los minutos estimados de lectura a partir del texto (~180 palabras por minuto).
     */
    public static function estimateReadingTime(string $htmlContent): int
    {
        $plainText = trim(strip_tags($htmlContent));
        if ($plainText === '') {
            return 3;
        }

        $wordCount = str_word_count($plainText);

        return max(1, (int) ceil($wordCount / 180));
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
     * Nombre final del autor a mostrar en la tarjeta (prioriza author_name manual o el nombre del usuario vinculado).
     */
    public function getDisplayAuthorNameAttribute(): string
    {
        return $this->author_name ?: ($this->user?->name ?? 'Comunidad Resucitados RCC');
    }

    /**
     * Iniciales de 2 letras del autor (ej. "Elena Martínez" -> "EM", "Pbro. David Solano" -> "DS").
     */
    public function getAuthorInitialsAttribute(): string
    {
        $name = trim($this->display_author_name);
        // Ignorar prefijos comunes como Pbro., Padre, Hno., Hna. para obtener las iniciales reales
        $cleaned = preg_replace('/^(pbro\.?|padre|p\.|hno\.?|hna\.?|mons\.?)\s+/iu', '', $name) ?: $name;

        $words = preg_split('/\s+/u', trim($cleaned)) ?: [];
        $words = array_values(array_filter($words, fn (string $w): bool => mb_strlen($w) > 0 && ! in_array(mb_strtolower($w), ['de', 'del', 'la', 'las', 'los', 'y'], true)));

        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }

        if (count($words) === 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 2));
        }

        return 'RC';
    }

    /**
     * Avatar del autor si está vinculado a un usuario del sistema con foto.
     */
    public function getAuthorAvatarUrlAttribute(): ?string
    {
        $avatar = $this->user?->getFilamentAvatarUrl();

        return $avatar ? url($avatar) : null;
    }

    /**
     * URL pública de la imagen de portada de la lectura/reflexión.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image_path)) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return url(Storage::disk('public')->url($this->image_path));
    }
}
