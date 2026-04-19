<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function returnRecord()
    {
        return $this->belongsTo(Returns::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
