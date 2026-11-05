<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prayer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'content',
        'image_path',
        'days',
        'event_id',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'array',
        ];
    }

    /**
     * Evento al que está asociada la oración (opcional).
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Scope para filtrar sólo oraciones.
     */
    public function scopeOraciones($query)
    {
        return $query->where('type', 'oracion');
    }

    /**
     * Scope para filtrar sólo novenas.
     */
    public function scopeNovenas($query)
    {
        return $query->where('type', 'novena');
    }
}
