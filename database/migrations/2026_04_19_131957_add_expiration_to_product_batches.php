<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('product_batches', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('qty');
            }
            if (Schema::hasColumn('product_batches', 'cost')) {
                $table->renameColumn('cost', 'unit_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropColumn('expiration_date');
            $table->renameColumn('unit_cost', 'cost');
        });
    }
};
