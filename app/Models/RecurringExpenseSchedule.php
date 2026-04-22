<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringExpenseSchedule extends Model
{
    protected $fillable = [
        'expense_id',
        'frequency',
        'next_due_date',
        'is_active',
    ];

    protected $casts = [
        'next_due_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
