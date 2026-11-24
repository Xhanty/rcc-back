<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Petition extends Model
{
    use SoftDeletes;

    protected $table = 'petitions';

    protected $fillable = [
        'name',
        'phone', // nullable
        'email', // nullable
        'petition',
        'status',
        'accepted_terms',
        'terms_accepted_at',
        'terms_version',
        'consent_ip',
        'consent_user_agent',
    ];

    protected $casts = [
        'status' => 'string',
        'accepted_terms' => 'boolean',
        'terms_accepted_at' => 'datetime',
    ];
}
