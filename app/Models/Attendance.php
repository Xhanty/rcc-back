<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'event_id',
        'assistant_id',
    ];

    // Relación con Event
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    // Relación con Assistant
    public function assistant(): BelongsTo
    {
        return $this->belongsTo(Assistant::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Attendance $attendance) {
            $event = $attendance->event;

            if (! $event) {
                throw new \Exception('El evento asociado no existe.');
            }

            if (! in_array($event->status, ['published', 'not_published'])) {
                throw new \Exception('Solo se puede registrar asistencia a eventos que estén Publicados o No Publicados.');
            }
        });
    }
}
