<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== ROLES =====
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('permissions')->nullable(); // array of module keys
            $table->timestamps();
        });

        // ===== MODIFY USERS (add role, display_name) =====
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete()->after('display_name');
        });

        // ===== SETTINGS =====
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // ===== SUPPLIERS =====
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // ===== PRODUCTS =====
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('General');
            $table->decimal('default_price', 12, 2)->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->integer('warranty_duration')->default(0); // months
            $table->boolean('is_service')->default(false);
            $table->timestamps();
        });

        // ===== PRODUCT BATCHES =====
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('qty')->default(0);
            $table->decimal('cost', 12, 2)->default(0);
            $table->timestamps();
        });

        // ===== CUSTOMERS =====
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('credit_balance', 12, 2)->default(0);
            $table->integer('loyalty_points')->default(0);
            $table->timestamps();
        });

        // ===== PURCHASE ORDERS =====
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->enum('status', ['pending', 'received'])->default('pending');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ===== TRANSACTIONS (SALES) =====
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // employee
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'credit'])->default('cash');
            $table->timestamps();
        });

        // ===== TRANSACTION ITEMS =====
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // snapshot of product name or service name
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->boolean('is_service')->default(false);
        });

        // ===== RETURNS =====
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['invoice', 'defective', 'general']);
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete(); // original invoice
            $table->string('reason')->nullable();
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->enum('refund_method', ['cash', 'credit'])->default('cash');
            $table->boolean('restocked')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // processed by
            $table->timestamps();
        });

        // ===== RETURN ITEMS =====
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
        });

        // ===== DEFECTIVE PRODUCTS =====
        Schema::create('defective_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'claimed', 'resolved'])->default('open');
            $table->timestamps();
        });

        // ===== EXPENSES =====
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // rent, utilities, salaries, maintenance, other
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('is_recurring')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // ===== CASH DRAWER EVENTS =====
        Schema::create('cash_drawer_events', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['open', 'close', 'in', 'out']);
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // ===== SPECIAL OFFERS =====
        Schema::create('special_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['pct', 'fixed', 'bogo', 'bundle'])->default('pct');
            $table->decimal('value', 12, 2)->default(0);
            $table->json('applicable_categories')->nullable();
            $table->json('applicable_products')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // ===== WARRANTIES =====
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->date('purchase_date');
            $table->date('end_date');
            $table->timestamps();
        });

        // ===== WARRANTY CLAIMS =====
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();
        });

        // ===== STOCK TRANSFERS =====
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('from_location');
            $table->string('to_location');
            $table->integer('qty');
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // ===== CUSTOMER PAYMENTS =====
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // ===== AUDIT LOG =====
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('customer_payments');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('special_offers');
        Schema::dropIfExists('cash_drawer_events');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('defective_products');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('product_batches');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('settings');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['display_name', 'role_id']);
        });
        Schema::dropIfExists('roles');
    }
};
