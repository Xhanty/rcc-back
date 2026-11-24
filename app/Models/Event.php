<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'event_type_id',
        'guest_name',
        'guest_instagram',
        'prayer_id',
        'title',
        'slug',
        'short_description',
        'content',
        'banner_image_path',
        'modality', // enum: in_person, virtual
        'venue_name',
        'address',
        'live_url',
        'start_datetime',
        'end_datetime',
        'status', // enum: draft, published, not_published, cancelled, completed
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    // Relación con event_types
    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    // Relación con asistencias
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Relación con asistentes (a través de asistencias)
    public function assistants()
    {
        return $this->belongsToMany(Assistant::class, 'attendances');
    }

    // Oración principal asociada al evento
    public function prayer()
    {
        return $this->belongsTo(Prayer::class, 'prayer_id');
    }

    // Oraciones asociadas al evento (relación muchos a muchos)
    public function prayers()
    {
        return $this->belongsToMany(Prayer::class, 'event_prayer')->withTimestamps();
    }

    // Canciones asociadas al evento
    public function songs()
    {
        return $this->belongsToMany(Song::class, 'event_song')->withTimestamps();
    }

    /**
     * URL completa del perfil de Instagram del invitado (soporta tanto @usuario como URL completa).
     */
    public function getGuestInstagramUrlAttribute(): ?string
    {
        if (blank($this->guest_instagram)) {
            return null;
        }

        $value = trim((string) $this->guest_instagram);

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $handle = ltrim($value, '@/');

        return $handle !== '' ? "https://www.instagram.com/{$handle}/" : null;
    }

    /**
     * Usuario (@handle) de Instagram del invitado extraído desde URL o texto.
     */
    public function getGuestInstagramHandleAttribute(): ?string
    {
        if (blank($this->guest_instagram)) {
            return null;
        }

        $value = trim((string) $this->guest_instagram);

        if (preg_match('/instagram\.com\/([A-Za-z0-9._]+)/i', $value, $matches)) {
            return '@' . $matches[1];
        }

        $handle = ltrim($value, '@/');

        return $handle !== '' ? '@' . $handle : null;
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event): void {
            if (empty($event->slug) && ! empty($event->title)) {
                $dateStr = $event->start_datetime
                    ? $event->start_datetime->format('Y-m-d')
                    : now()->format('Y-m-d');

                $event->slug = Str::slug($event->title) . '-' . $dateStr;
            }
        });
    }
}
