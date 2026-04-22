<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Change enum to string first to avoid issues, or use DB statement to update enum
        // We will move to a more flexible string type for 'type' column
        Schema::table('special_offers', function (Blueprint $table) {
            $table->string('type')->default('pct')->change();
        });

        // Add name_en if missing (standardizing with other entities)
        if (!Schema::hasColumn('special_offers', 'name_en')) {
            Schema::table('special_offers', function (Blueprint $table) {
                $table->string('name_en')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('special_offers', function (Blueprint $table) {
            $table->enum('type', ['pct', 'fixed', 'bogo', 'bundle'])->default('pct')->change();
            $table->dropColumn('name_en');
        });
    }
};
