<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Song extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'lyrics',
        'youtube_url',
        'spotify_url',
        'instagram_url',
        'event_id',
    ];

    /**
     * Evento principal (si aplica).
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Eventos asociados (relación muchos a muchos).
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_song')->withTimestamps();
    }
}
