<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin role with ALL permissions
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Administrator',
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

        // 2. Main Admin User
        DB::table('users')->insert([
            'name' => 'admin',
            'display_name' => 'System Admin',
            'email' => 'admin@genshelf.com',
            'password' => Hash::make('admin'),
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Optional: Manager Account (No deletes/settings)
        $managerRoleId = DB::table('roles')->insertGetId([
            'name' => 'Manager',
            'permissions' => json_encode([
                'dashboard',
                'pos',
                'inventory',
                'suppliers',
                'customers',
                'offers',
                'returns',
                'reports',
                'warranty',
                'transfers'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'manager',
            'display_name' => 'Store Manager',
            'email' => 'manager@genshelf.com',
            'password' => Hash::make('manager123'),
            'role_id' => $managerRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
