<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * AnalyticsDemoSeeder
 * 
 * Seeds realistic demo data so analytics dashboards are never empty.
 * All records are tagged with '[DEMO]' marker for easy bulk removal.
 * 
 * Run:    php artisan db:seed --class=AnalyticsDemoSeeder
 * Remove: php artisan db:seed --class=AnalyticsDemoSeeder -- --unseed
 *         OR call AnalyticsDemoSeeder::unseed() programmatically
 */
class AnalyticsDemoSeeder extends Seeder
{
    private const MARKER = '[DEMO]';

    public function run(): void
    {
        // If called with --unseed flag, remove demo data instead
        if (in_array('--unseed', $_SERVER['argv'] ?? [])) {
            $this->unseed();
            return;
        }

        $this->command?->info('🌱 Seeding analytics demo data...');

        // Ensure storages exist
        $storageId = DB::table('storages')->insertGetId([
            'name' => 'Demo Warehouse ' . self::MARKER,
            'type' => 'storage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Suppliers
        $suppliers = [];
        $supplierNames = ['شركة النور للتوريدات', 'مؤسسة الأمل التجارية', 'التقنية الحديثة للإلكترونيات'];
        foreach ($supplierNames as $name) {
            $suppliers[] = DB::table('suppliers')->insertGetId([
                'name' => $name . ' ' . self::MARKER,
                'phone' => '01' . rand(00000000, 99999999),
                'email' => 'demo' . rand(1, 99) . '@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Products with batches
        $products = [];
        $productData = [
            ['name' => 'شاشة LED 55 بوصة', 'category' => 'إلكترونيات', 'price' => 12500, 'cost' => 9000],
            ['name' => 'هاتف ذكي Galaxy A54', 'category' => 'إلكترونيات', 'price' => 8500, 'cost' => 6200],
            ['name' => 'لابتوب HP ProBook', 'category' => 'حواسيب', 'price' => 18000, 'cost' => 14000],
            ['name' => 'ماوس لاسلكي Logitech', 'category' => 'إكسسوارات', 'price' => 450, 'cost' => 250],
            ['name' => 'كيبورد ميكانيكي RGB', 'category' => 'إكسسوارات', 'price' => 1200, 'cost' => 700],
            ['name' => 'سماعة Bluetooth JBL', 'category' => 'صوتيات', 'price' => 2500, 'cost' => 1600],
            ['name' => 'شاحن سريع 65W', 'category' => 'إكسسوارات', 'price' => 350, 'cost' => 150],
            ['name' => 'كاميرا مراقبة WiFi', 'category' => 'أمان', 'price' => 1800, 'cost' => 1100],
            ['name' => 'طابعة ليزر Canon', 'category' => 'حواسيب', 'price' => 4500, 'cost' => 3200],
            ['name' => 'حبر طابعة أسود', 'category' => 'مستلزمات', 'price' => 280, 'cost' => 120],
            ['name' => 'كابل HDMI 2M', 'category' => 'إكسسوارات', 'price' => 120, 'cost' => 40],
            ['name' => 'هارد خارجي 1TB', 'category' => 'تخزين', 'price' => 2200, 'cost' => 1500],
        ];

        foreach ($productData as $p) {
            $pid = DB::table('products')->insertGetId([
                'name' => $p['name'] . ' ' . self::MARKER,
                'category' => $p['category'],
                'default_price' => $p['price'],
                'low_stock_threshold' => rand(3, 10),
                'warranty_duration' => rand(0, 1) ? rand(6, 24) : 0,
                'is_service' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $products[] = ['id' => $pid, 'price' => $p['price'], 'cost' => $p['cost']];

            // Batch with stock
            DB::table('product_batches')->insert([
                'product_id' => $pid,
                'supplier_id' => $suppliers[array_rand($suppliers)],
                'storage_id' => $storageId,
                'qty' => rand(5, 80),
                'unit_cost' => $p['cost'],
                'batch_number' => 'DEMO-B' . rand(1000, 9999),
                'expiration_date' => rand(0, 1) ? now()->addDays(rand(10, 120))->toDateString() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // A product with LOW stock for alerts
        $lowPid = DB::table('products')->insertGetId([
            'name' => 'فلاشة USB 64GB ' . self::MARKER,
            'category' => 'تخزين',
            'default_price' => 180,
            'low_stock_threshold' => 10,
            'is_service' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('product_batches')->insert([
            'product_id' => $lowPid,
            'supplier_id' => $suppliers[0],
            'storage_id' => $storageId,
            'qty' => 2,
            'unit_cost' => 80,
            'batch_number' => 'DEMO-LOW1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Customers
        $customers = [];
        $customerNames = [
            'أحمد محمد', 'فاطمة علي', 'محمود حسن', 'سارة إبراهيم',
            'عمر خالد', 'نورا سعيد', 'يوسف عبدالله', 'مريم أحمد',
            'خالد عمر', 'هدى محمود',
        ];
        foreach ($customerNames as $i => $name) {
            $customers[] = DB::table('customers')->insertGetId([
                'name' => $name . ' ' . self::MARKER,
                'phone' => '01' . rand(00000000, 99999999),
                'email' => 'customer' . ($i + 1) . '@demo.test',
                'notes' => self::MARKER,
                'credit_balance' => $i < 3 ? rand(500, 5000) : 0, // 3 debtors
                'loyalty_points' => rand(0, 2000),
                'created_at' => now()->subDays(rand(30, 180)),
                'updated_at' => now(),
            ]);
        }

        // 4. Admin user for seeded transactions
        $userId = DB::table('users')->where('role_id', '!=', null)->value('id')
                  ?? DB::table('users')->first()?->id
                  ?? 1;

        // 5. Transactions spread over the last 6 months
        $this->command?->info('   📊 Creating transactions...');
        $paymentMethods = ['cash', 'cash', 'cash', 'credit', 'partial', 'debt'];

        for ($dayOffset = 180; $dayOffset >= 0; $dayOffset--) {
            $date = now()->subDays($dayOffset);
            $txCount = rand(1, 5); // 1-5 transactions per day

            for ($t = 0; $t < $txCount; $t++) {
                $product = $products[array_rand($products)];
                $qty = rand(1, 4);
                $unitPrice = $product['price'];
                $subtotal = $unitPrice * $qty;
                $discount = rand(0, 1) ? round($subtotal * rand(5, 15) / 100, 2) : 0;
                $tax = round(($subtotal - $discount) * 0.14, 2);
                $total = round($subtotal - $discount + $tax, 2);
                $payMethod = $paymentMethods[array_rand($paymentMethods)];
                $custId = $customers[array_rand($customers)];
                $dueAmount = $payMethod === 'debt' ? $total : ($payMethod === 'partial' ? round($total * 0.4, 2) : 0);
                $paidAmount = $total - $dueAmount;

                $txId = DB::table('transactions')->insertGetId([
                    'customer_id' => $custId,
                    'user_id' => $userId,
                    'storage_id' => $storageId,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'paid_amount' => $paidAmount,
                    'due_amount' => $dueAmount,
                    'due_date' => $dueAmount > 0 ? $date->copy()->addDays(rand(7, 30))->toDateString() : null,
                    'payment_method' => $payMethod,
                    'items_snapshot' => json_encode([['name' => self::MARKER]]),
                    'created_at' => $date->copy()->setTime(rand(8, 21), rand(0, 59)),
                    'updated_at' => $date,
                ]);

                DB::table('transaction_items')->insert([
                    'transaction_id' => $txId,
                    'product_id' => $product['id'],
                    'name' => self::MARKER,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $subtotal,
                    'is_service' => false,
                ]);

                // Tax entry
                DB::table('tax_entries')->insert([
                    'transaction_id' => $txId,
                    'taxable_amount' => $subtotal - $discount,
                    'tax_rate' => 14,
                    'tax_amount' => $tax,
                    'status' => rand(0, 5) > 0 ? 'collected' : 'remitted',
                    'period_start' => $date->copy()->startOfMonth()->toDateString(),
                    'period_end' => $date->copy()->endOfMonth()->toDateString(),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        // 6. Expenses (past 6 months)
        $this->command?->info('   💰 Creating expenses...');
        $expenseCategories = ['rent', 'utilities', 'salaries', 'marketing', 'maintenance', 'logistics'];
        for ($m = 5; $m >= 0; $m--) {
            $monthStart = now()->subMonths($m)->startOfMonth();
            foreach ($expenseCategories as $cat) {
                $amount = match ($cat) {
                    'rent' => rand(8000, 12000),
                    'salaries' => rand(15000, 25000),
                    'utilities' => rand(1500, 3500),
                    'marketing' => rand(2000, 6000),
                    'maintenance' => rand(500, 2000),
                    'logistics' => rand(1000, 4000),
                };
                DB::table('expenses')->insert([
                    'category' => $cat,
                    'description' => self::MARKER . ' ' . $cat,
                    'description_en' => self::MARKER . ' ' . $cat . ' expense',
                    'amount' => $amount,
                    'is_recurring' => in_array($cat, ['rent', 'salaries', 'utilities']),
                    'user_id' => $userId,
                    'payment_method' => 'cash',
                    'expense_date' => $monthStart->copy()->addDays(rand(1, 25))->toDateString(),
                    'status' => 'approved',
                    'created_at' => $monthStart,
                    'updated_at' => $monthStart,
                ]);
            }
        }

        // 7. Expense Budgets
        for ($m = 0; $m < 6; $m++) {
            $monthDate = now()->subMonths($m);
            foreach (['rent', 'salaries', 'utilities', 'marketing'] as $cat) {
                DB::table('expense_budgets')->insertOrIgnore([
                    'category' => $cat,
                    'year' => $monthDate->year,
                    'month' => $monthDate->month,
                    'budgeted_amount' => match ($cat) {
                        'rent' => 12000,
                        'salaries' => 28000,
                        'utilities' => 4000,
                        'marketing' => 8000,
                    },
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 8. Returns (a few)
        $this->command?->info('   🔄 Creating returns...');
        $txIds = DB::table('transactions')
            ->whereRaw("items_snapshot LIKE '%[DEMO]%'")
            ->inRandomOrder()->limit(8)->pluck('id');

        foreach ($txIds as $txId) {
            $tx = DB::table('transactions')->find($txId);
            $refund = round($tx->total * rand(30, 100) / 100, 2);
            $returnId = DB::table('returns')->insertGetId([
                'type' => 'invoice',
                'transaction_id' => $txId,
                'reason' => self::MARKER . ' عيب مصنعي',
                'refund_amount' => $refund,
                'refund_method' => 'cash',
                'restocked' => rand(0, 1),
                'user_id' => $userId,
                'created_at' => Carbon::parse($tx->created_at)->addDays(rand(1, 10)),
                'updated_at' => now(),
            ]);

            $txItem = DB::table('transaction_items')->where('transaction_id', $txId)->first();
            if ($txItem) {
                DB::table('return_items')->insert([
                    'return_id' => $returnId,
                    'product_id' => $txItem->product_id,
                    'name' => self::MARKER,
                    'qty' => 1,
                    'unit_price' => $txItem->unit_price,
                ]);
            }
        }

        // 9. Cash Flow Entries
        $this->command?->info('   💸 Creating cash flow entries...');
        for ($d = 90; $d >= 0; $d--) {
            $date = now()->subDays($d);
            DB::table('cash_flow_entries')->insert([
                'type' => 'operating',
                'direction' => 'inflow',
                'source' => self::MARKER,
                'amount' => rand(2000, 15000),
                'description' => self::MARKER . ' إيرادات مبيعات',
                'entry_date' => $date->toDateString(),
                'user_id' => $userId,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            if (rand(0, 1)) {
                DB::table('cash_flow_entries')->insert([
                    'type' => ['operating', 'investing', 'financing'][rand(0, 2)],
                    'direction' => 'outflow',
                    'source' => self::MARKER,
                    'amount' => rand(500, 5000),
                    'description' => self::MARKER . ' مصروفات تشغيلية',
                    'entry_date' => $date->toDateString(),
                    'user_id' => $userId,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        // 10. Defective products
        foreach (array_slice($products, 0, 3) as $p) {
            DB::table('defective_products')->insert([
                'product_id' => $p['id'],
                'supplier_id' => $suppliers[array_rand($suppliers)],
                'description' => self::MARKER . ' عيب في التصنيع',
                'status' => ['open', 'claimed', 'resolved'][rand(0, 2)],
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
        }

        // 11. Warranties
        foreach (array_slice($products, 0, 5) as $p) {
            $purchaseDate = now()->subMonths(rand(1, 12));
            DB::table('warranties')->insert([
                'product_id' => $p['id'],
                'customer_id' => $customers[array_rand($customers)],
                'purchase_date' => $purchaseDate->toDateString(),
                'end_date' => $purchaseDate->copy()->addMonths(rand(6, 24))->toDateString(),
                'created_at' => $purchaseDate,
                'updated_at' => now(),
            ]);
        }

        // 12. Audit logs
        $actions = ['created_product', 'updated_price', 'processed_return', 'approved_expense', 'delete_batch'];
        for ($i = 0; $i < 20; $i++) {
            DB::table('audit_logs')->insert([
                'action' => self::MARKER . ' ' . $actions[array_rand($actions)],
                'user_id' => $userId,
                'user_name' => self::MARKER,
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 12)),
                'updated_at' => now(),
            ]);
        }

        $this->command?->info('✅ Analytics demo data seeded successfully!');
        $this->command?->info('   To remove: php artisan db:seed --class=AnalyticsDemoSeeder -- --unseed');
    }

    /**
     * Remove all demo data tagged with [DEMO] marker.
     * Can be called via: php artisan db:seed --class=AnalyticsDemoSeeder -- --unseed
     */
    public static function unseed(): void
    {
        $marker = self::MARKER;

        echo "🗑️  Removing analytics demo data...\n";

        // Delete in dependency order (children first)
        DB::table('audit_logs')->where('action', 'LIKE', "%{$marker}%")->delete();
        DB::table('warranty_claims')->whereIn('warranty_id', function ($q) use ($marker) {
            $q->select('id')->from('warranties')->whereIn('product_id', function ($q2) use ($marker) {
                $q2->select('id')->from('products')->where('name', 'LIKE', "%{$marker}%");
            });
        })->delete();
        DB::table('warranties')->whereIn('product_id', function ($q) use ($marker) {
            $q->select('id')->from('products')->where('name', 'LIKE', "%{$marker}%");
        })->delete();
        DB::table('defective_products')->where('description', 'LIKE', "%{$marker}%")->delete();
        DB::table('cash_flow_entries')->where('source', 'LIKE', "%{$marker}%")->delete();

        // Returns and return items
        $returnIds = DB::table('returns')->where('reason', 'LIKE', "%{$marker}%")->pluck('id');
        DB::table('return_items')->whereIn('return_id', $returnIds)->delete();
        DB::table('returns')->whereIn('id', $returnIds)->delete();

        // Tax entries via demo transactions
        $demoTxIds = DB::table('transactions')
            ->whereRaw("items_snapshot LIKE '%{$marker}%'")
            ->pluck('id');
        DB::table('tax_entries')->whereIn('transaction_id', $demoTxIds)->delete();

        // Transaction items & transactions
        DB::table('transaction_items')->whereIn('transaction_id', $demoTxIds)->delete();
        DB::table('transactions')->whereIn('id', $demoTxIds)->delete();

        // Expense budgets (can't tag these easily, skip if shared)
        DB::table('expenses')->where('description', 'LIKE', "%{$marker}%")->delete();

        // Product batches, products
        $demoProdIds = DB::table('products')->where('name', 'LIKE', "%{$marker}%")->pluck('id');
        DB::table('product_batches')->whereIn('product_id', $demoProdIds)->delete();
        DB::table('products')->whereIn('id', $demoProdIds)->delete();

        // Customers
        DB::table('customers')->where('notes', $marker)->delete();

        // Suppliers
        DB::table('suppliers')->where('name', 'LIKE', "%{$marker}%")->delete();

        // Storages
        DB::table('storages')->where('name', 'LIKE', "%{$marker}%")->delete();

        echo "✅ All demo data removed successfully!\n";
    }
}
