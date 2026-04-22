<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tax Entries Tracking
        Schema::create('tax_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->enum('status', ['collected', 'remitted'])->default('collected');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamps();
        });

        // Cash Flow Entries Tracking
        Schema::create('cash_flow_entries', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['operating', 'investing', 'financing']);
            $table->enum('direction', ['inflow', 'outflow']);
            $table->string('source'); // 'sale', 'expense', 'purchase_order', etc.
            $table->string('source_reference')->nullable(); 
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->date('entry_date');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Financial Snapshots for Reporting Performance
        Schema::create('financial_snapshots', function (Blueprint $table) {
            $table->id();
            $table->enum('period_type', ['daily', 'monthly', 'quarterly', 'yearly']);
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('total_cogs', 12, 2)->default(0);
            $table->decimal('gross_profit', 12, 2)->default(0);
            $table->decimal('total_expenses', 12, 2)->default(0);
            $table->decimal('net_profit', 12, 2)->default(0);
            $table->decimal('total_tax_collected', 12, 2)->default(0);
            $table->integer('tx_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_snapshots');
        Schema::dropIfExists('cash_flow_entries');
        Schema::dropIfExists('tax_entries');
    }
};
