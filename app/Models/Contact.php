<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone', // nullable
        'email', // nullable
        'subject',
        'message',
        'status', // enum: pending, completed
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
