<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    protected $casts = [
        'items_snapshot' => 'array',
        'due_date' => 'date'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function storage()
    {
        return $this->belongsTo(Storage::class);
    }
}
