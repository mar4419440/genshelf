<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashFlowEntry extends Model
{
    protected $fillable = [
        'type',
        'direction',
        'source',
        'source_reference',
        'amount',
        'description',
        'entry_date',
        'user_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
