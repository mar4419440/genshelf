<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseBudget extends Model
{
    protected $fillable = [
        'category',
        'sub_category',
        'year',
        'month',
        'budgeted_amount',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
