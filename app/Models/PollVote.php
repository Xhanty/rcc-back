<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_id',
        'poll_option_id',
        'assistant_id',
        'document',
        'name',
        'email',
        'phone',
        'birth_date',
        'voter_token',
        'fingerprint_hash',
        'ip_hash',
        'accepted_terms',
        'terms_accepted_at',
        'terms_version',
        'consent_ip',
        'consent_user_agent',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'accepted_terms' => 'boolean',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(Assistant::class);
    }
}
