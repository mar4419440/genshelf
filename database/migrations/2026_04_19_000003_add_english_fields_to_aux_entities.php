<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->text('description_en')->nullable()->after('description');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->text('reason_en')->nullable()->after('reason');
        });

        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->text('description_en')->nullable()->after('description');
            $table->text('resolution_en')->nullable()->after('resolution');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['description_en']);
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropColumn(['reason_en']);
        });

        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->dropColumn(['description_en', 'resolution_en']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['name_en']);
        });
    }
};
