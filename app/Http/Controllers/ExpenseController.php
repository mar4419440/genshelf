<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    // ========== INDEX — قائمة مع فلاتر متقدمة ==========

    public function index(Request $request)
    {
        $query = Expense::with(['user', 'approver']);

        // فلترة بالفئة
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // فلترة بالحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة بالتاريخ
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [
                $request->start_date,
                $request->end_date,
            ]);
        } elseif ($request->filled('period')) {
            match ($request->period) {
                'today'      => $query->whereDate('expense_date', today()),
                'this_week'  => $query->whereBetween('expense_date', [
                                    now()->startOfWeek(), now()->endOfWeek()
                                ]),
                'this_month' => $query->whereMonth('expense_date', now()->month)
                                      ->whereYear('expense_date', now()->year),
                'this_year'  => $query->whereYear('expense_date', now()->year),
                default      => null,
            };
        }

        // بحث نصي
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('description_en', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $expenses = $query->latest('expense_date')->paginate(25)->withQueryString();

        // إجمالي المصروفات في الفترة المختارة
        $totalInPeriod = $query->sum('amount');

        // ملخص بالفئات
        $categoryBreakdown = Expense::approved()
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->whereYear('expense_date', now()->year)
            ->whereMonth('expense_date', now()->month)
            ->get()
            ->keyBy('category');

        // Budget vs Actual لهذا الشهر
        $budgets = DB::table('expense_budgets')
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->get()
            ->keyBy('category');

        $categories = Expense::categories();

        return view('pages.expenses.index', compact(
            'expenses', 'categoryBreakdown', 'budgets', 'categories', 'totalInPeriod'
        ));
    }

    // ========== STORE — إضافة مصروف ==========

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'         => 'required|string|max:100',
            'sub_category'     => 'nullable|string|max:100',
            'description'      => 'required|string|max:500',
            'description_en'   => 'nullable|string|max:500',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|in:cash,bank_transfer,card,cheque',
            'reference_number' => 'nullable|string|max:100',
            'expense_date'     => 'required|date',
            'is_recurring'     => 'boolean',
            'attachment'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $validated['user_id']      = auth()->id();
        $validated['is_recurring'] = $request->boolean('is_recurring');
        $validated['status']       = 'approved'; // Auto-approve for now or based on role

        // رفع المرفق
        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')
                ->store('expenses/attachments', 'public');
        }

        $expense = Expense::create($validated);

        // لو متكرر، سجل الجدول الزمني
        if ($expense->is_recurring && $request->filled('frequency')) {
            DB::table('recurring_expense_schedules')->insert([
                'expense_id'    => $expense->id,
                'frequency'     => $request->frequency,
                'next_due_date' => $this->calculateNextDueDate($request->expense_date, $request->frequency),
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        return redirect()->back()->with('success', __('Expense logged successfully.'));
    }

    // ========== UPDATE — تعديل مصروف ==========

    public function update(Request $request, Expense $expense)
    {
        // منع تعديل المصروفات المعتمدة من غير أدمن (Simplified role check)
        if ($expense->status === 'approved' && auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', __('Cannot edit an approved expense.'));
        }

        $validated = $request->validate([
            'category'         => 'required|string|max:100',
            'sub_category'     => 'nullable|string|max:100',
            'description'      => 'required|string|max:500',
            'description_en'   => 'nullable|string|max:500',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|in:cash,bank_transfer,card,cheque',
            'reference_number' => 'nullable|string|max:100',
            'expense_date'     => 'required|date',
        ]);

        if ($request->hasFile('attachment')) {
            if ($expense->attachment_path) {
                Storage::disk('public')->delete($expense->attachment_path);
            }
            $validated['attachment_path'] = $request->file('attachment')
                ->store('expenses/attachments', 'public');
        }

        $expense->update($validated);

        return redirect()->back()->with('success', __('Expense updated.'));
    }

    // ========== DESTROY ==========

    public function destroy(Expense $expense)
    {
        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }
        $expense->delete();

        return redirect()->back()->with('success', __('Expense deleted.'));
    }

    // ========== APPROVE / REJECT ==========

    public function approve(Request $request, Expense $expense)
    {
        $expense->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()->back()->with('success', __('Expense approved.'));
    }

    public function reject(Request $request, Expense $expense)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $expense->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', __('Expense rejected.'));
    }

    // ========== SUMMARY — تقرير تلخيصي ==========

    public function summary(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $monthlyByCategory = Expense::approved()
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        $trend = Expense::approved()
            ->where('expense_date', '>=', now()->subMonths(12)->startOfMonth())
            ->select(
                DB::raw('YEAR(expense_date) as year'),
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::createFromDate($row->year, $row->month, 1)->format('M Y'),
                'total' => (float) $row->total,
            ]);

        $budgets = DB::table('expense_budgets')
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->keyBy('category');

        $budgetComparison = $monthlyByCategory->map(function ($item) use ($budgets) {
            $budget = $budgets->get($item->category);
            $budgeted = $budget ? (float) $budget->budgeted_amount : 0;
            $actual   = (float) $item->total;
            return [
                'category'   => $item->category,
                'actual'     => $actual,
                'budgeted'   => $budgeted,
                'variance'   => $budgeted - $actual,
                'pct_used'   => $budgeted > 0 ? round(($actual / $budgeted) * 100, 1) : null,
                'over_budget'=> $budgeted > 0 && $actual > $budgeted,
            ];
        });

        return view('pages.expenses.summary', compact(
            'monthlyByCategory', 'trend', 'budgetComparison', 'year', 'month'
        ));
    }

    private function calculateNextDueDate(string $fromDate, string $frequency): string
    {
        $date = Carbon::parse($fromDate);
        return match ($frequency) {
            'weekly'    => $date->addWeek()->toDateString(),
            'monthly'   => $date->addMonth()->toDateString(),
            'quarterly' => $date->addMonths(3)->toDateString(),
            'yearly'    => $date->addYear()->toDateString(),
            default     => $date->addMonth()->toDateString(),
        };
    }
}
