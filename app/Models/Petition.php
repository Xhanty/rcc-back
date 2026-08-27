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
    ];
}
