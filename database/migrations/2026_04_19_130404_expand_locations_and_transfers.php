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
        Schema::table('storages', function (Blueprint $table) {
            $table->enum('type', ['storage', 'pos'])->default('storage')->after('name');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('storage_id')->nullable()->constrained('storages')->nullOnDelete()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('storages', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('storage_id');
        });
    }
};
