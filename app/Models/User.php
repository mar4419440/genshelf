<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends \Illuminate\Foundation\Auth\User
{
    protected $guarded = [];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
