<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Expense extends Model
{
    protected $fillable = [
        'category',
        'sub_category',
        'description',
        'description_en',
        'amount',
        'payment_method',
        'reference_number',
        'attachment_path',
        'expense_date',
        'is_recurring',
        'status',
        'user_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'expense_date' => 'date',
        'approved_at'  => 'datetime',
        'amount'       => 'decimal:2',
    ];

    // ========== RELATIONSHIPS ==========

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recurringSchedule()
    {
        return $this->hasOne(RecurringExpenseSchedule::class);
    }

    // ========== SCOPES ==========

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeByPeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereYear('expense_date', now()->year)
                     ->whereMonth('expense_date', now()->month);
    }

    public function scopeRecurring(Builder $query): Builder
    {
        return $query->where('is_recurring', true);
    }

    // ========== ACCESSORS ==========

    public function getLocalDescriptionAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'en' && $this->description_en) {
            return $this->description_en;
        }
        return $this->description ?? '';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'draft'    => 'warning',
            default    => 'secondary',
        };
    }

    // ========== STATIC HELPERS ==========

    public static function categories(): array
    {
        return [
            'rent'        => ['office', 'warehouse', 'retail', 'other'],
            'utilities'   => ['electricity', 'water', 'internet', 'phone', 'gas'],
            'salaries'    => ['staff', 'freelance', 'bonus', 'commission'],
            'maintenance' => ['equipment', 'facility', 'vehicle', 'it'],
            'marketing'   => ['ads', 'print', 'events', 'social_media'],
            'logistics'   => ['shipping', 'delivery', 'fuel', 'packaging'],
            'taxes_fees'  => ['vat', 'customs', 'licenses', 'fines'],
            'other'       => ['miscellaneous'],
        ];
    }
}
