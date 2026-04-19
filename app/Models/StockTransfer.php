<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'product_id',
        'from_storage_id',
        'to_storage_id',
        'qty',
        'user_id',
        'notes'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromStorage()
    {
        return $this->belongsTo(Storage::class, 'from_storage_id');
    }

    public function toStorage()
    {
        return $this->belongsTo(Storage::class, 'to_storage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
