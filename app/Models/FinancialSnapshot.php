<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialSnapshot extends Model
{
    protected $fillable = [
        'period_type',
        'period_start',
        'period_end',
        'total_revenue',
        'total_cogs',
        'gross_profit',
        'total_expenses',
        'net_profit',
        'total_tax_collected',
        'tx_count',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];
}
