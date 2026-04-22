<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxEntry extends Model
{
    protected $fillable = [
        'transaction_id',
        'taxable_amount',
        'tax_rate',
        'tax_amount',
        'status',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
