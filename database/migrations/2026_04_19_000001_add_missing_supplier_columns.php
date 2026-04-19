<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('category_en')->nullable()->after('category');
            $table->string('contact_person')->nullable()->after('phone');
            $table->text('address')->nullable()->after('contact_person');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'address']);
        });
    }
};
