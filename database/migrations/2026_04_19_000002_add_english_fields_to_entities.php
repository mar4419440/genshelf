<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('category_en')->nullable()->after('category');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });

        Schema::table('special_offers', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'category_en']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['name_en']);
        });

        Schema::table('special_offers', function (Blueprint $table) {
            $table->dropColumn(['name_en']);
        });
    }
};
