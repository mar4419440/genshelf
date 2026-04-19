<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin role with all permissions
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Admin',
            'permissions' => json_encode([
                'dashboard',
                'pos',
                'inventory',
                'suppliers',
                'customers',
                'offers',
                'returns',
                'finance',
                'reports',
                'warranty',
                'transfers',
                'settings',
                'users',
                'categories',
                'storages'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Admin user
        DB::table('users')->insert([
            'name' => 'admin',
            'display_name' => 'Admin',
            'email' => 'admin@genshelf.com',
            'password' => Hash::make('admin'),
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default settings
        $defaults = [
            'lang' => 'en',
            'currency' => 'EGP',
            'tax_rate' => '14',
            'store_name' => 'GenShelf Store',
            'store_address' => '',
            'store_phone' => '',
            'low_stock_default' => '5',
            'toggle_loyalty' => '1',
            'toggle_credit' => '1',
            'toggle_offers' => '1',
            'toggle_warranty' => '1',
            'toggle_tax' => '1',
            'toggle_notifs' => '1',
            'toggle_transfers' => '1',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
