<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assistant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document',
        'name',
        'email',
        'phone',
        'birth_date',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    // Relación con asistencias
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Relación con eventos (a través de asistencias)
    public function events()
    {
        return $this->belongsToMany(Event::class, 'attendances');
    }
}
