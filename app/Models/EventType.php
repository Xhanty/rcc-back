<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (EventType $eventType): void {
            if (empty($eventType->slug) && ! empty($eventType->name)) {
                $eventType->slug = Str::slug($eventType->name);
            }
        });
    }
}
