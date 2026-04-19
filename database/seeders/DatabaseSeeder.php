<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingSeeder::class,
        ]);

        // Create a default POS location so the system is usable immediately
        DB::table('storages')->insert([
            'name' => 'Main POS Station',
            'type' => 'pos',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a default Storage location
        DB::table('storages')->insert([
            'name' => 'Main Warehouse',
            'type' => 'storage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
