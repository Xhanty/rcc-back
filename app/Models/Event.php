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

    protected static function booted(): void
    {
        static::creating(function (Event $event): void {
            if (empty($event->slug) && ! empty($event->title)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }
}
