<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialOffer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
        'applicable_products' => 'array',
        'applicable_categories' => 'array'
    ];

    public function getProductsAttribute()
    {
        if (empty($this->applicable_products)) return collect();
        return \App\Models\Product::whereIn('id', $this->applicable_products)->get();
    }
}
