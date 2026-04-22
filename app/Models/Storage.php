<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'name_en',
        'conditions',
        'is_active'
    ];

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }
}
