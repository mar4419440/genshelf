<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Fix: description_en and sub_category
            if (!Schema::hasColumn('expenses', 'description_en')) {
                $table->string('description_en')->nullable()->after('description');
            }
            $table->string('sub_category')->nullable()->after('category');

            // Payment and Reference
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'cheque'])
                  ->default('cash')->after('amount');
            $table->string('reference_number')->nullable()->after('payment_method');

            // Attachments and Dates
            $table->string('attachment_path')->nullable()->after('reference_number');
            $table->date('expense_date')->nullable()->after('attachment_path');

            // Approval System
            $table->enum('status', ['draft', 'approved', 'rejected'])->default('approved')->after('expense_date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
        });

        // Category Budgets Table
        Schema::create('expense_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('sub_category')->nullable();
            $table->integer('year');
            $table->integer('month'); 
            $table->decimal('budgeted_amount', 12, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['category', 'sub_category', 'year', 'month'], 'unique_budget_period');
        });

        // Recurring Schedules Table
        Schema::create('recurring_expense_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->enum('frequency', ['weekly', 'monthly', 'quarterly', 'yearly']);
            $table->date('next_due_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_expense_schedules');
        Schema::dropIfExists('expense_budgets');
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn([
                'description_en', 'sub_category', 'payment_method',
                'reference_number', 'attachment_path', 'expense_date',
                'status', 'approved_by', 'approved_at', 'rejection_reason'
            ]);
        });
    }
};
