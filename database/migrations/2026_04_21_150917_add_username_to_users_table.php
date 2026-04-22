<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('id');
            });
        }

        // Populate existing users with something unique if they don't have a username
        foreach (\App\Models\User::whereNull('username')->get() as $user) {
            $user->update(['username' => explode('@', $user->email)[0] . $user->id]);
        }

        // Now add unique constraint if not exists
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('username');
            });
        } catch (\Exception $e) {
            // Already unique or other error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
