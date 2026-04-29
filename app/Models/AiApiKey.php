<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiApiKey extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];
}
