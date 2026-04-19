<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'lang' => 'en',
            'currency' => 'EGP',
            'tax_rate' => '14',
            'store_name' => 'GenShelf Store',
            'store_address' => 'Cairo, Egypt',
            'store_phone' => '+20 123 456 789',
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
            DB::table('settings')->updateOrInsert(['key' => $key], [
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
