<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\BIController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionEditController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnalyticsController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Invoice View
Route::get('/invoice/{transaction}', [PosController::class, 'showInvoice'])->name('pos.invoice');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/barcode/{product}', [PosController::class, 'showBarcode'])->name('pos.barcode');
    Route::post('/pos/invoice/return', [PosController::class, 'processInvoiceReturn'])->name('pos.invoice.return');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::get('/categories/template', [CategoryController::class, 'downloadTemplate'])->name('categories.template');
    Route::post('/categories/import', [CategoryController::class, 'importCategories'])->name('categories.import');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Storages
    Route::resource('storages', StorageController::class);

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/inventory/expiring', [InventoryController::class, 'expiring'])->name('inventory.expiring');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{product}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/import', [InventoryController::class, 'importCSV'])->name('inventory.import');
    Route::post('/inventory/{product}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');
    Route::get('/inventory/template', [InventoryController::class, 'downloadTemplate'])->name('inventory.template');

    // Suppliers & PO
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers');
    Route::get('/suppliers/template', [SupplierController::class, 'downloadSupplierTemplate'])->name('suppliers.template');
    Route::post('/suppliers/import', [SupplierController::class, 'importSuppliers'])->name('suppliers.import');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::post('/suppliers/po', [SupplierController::class, 'storePO'])->name('suppliers.po.store');
    Route::post('/suppliers/receive/{purchaseOrder}', [SupplierController::class, 'receivePO'])->name('suppliers.po.receive');
    Route::post('/suppliers/po/import', [SupplierController::class, 'importPOs'])->name('suppliers.po.import');
    Route::get('/suppliers/po/template', [SupplierController::class, 'downloadTemplate'])->name('suppliers.po.template');
    Route::post('/purchase-orders', [SupplierController::class, 'storePO'])->name('purchase-orders.store');
    Route::post('/purchase-orders/{po}/receive', [SupplierController::class, 'receivePO'])->name('purchase-orders.receive');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/customers/{customer}/history', [CustomerController::class, 'history'])->name('customers.history');
    Route::post('/customers/{customer}/pay', [CustomerController::class, 'pay'])->name('customers.pay');
    Route::post('/customers/{customer}/add-debt', [CustomerController::class, 'addDebt'])->name('customers.add-debt');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Offers
    Route::get('/offers', [OfferController::class, 'index'])->name('offers');
    Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
    Route::put('/offers/{offer}', [OfferController::class, 'update'])->name('offers.update');
    Route::delete('/offers/{offer}', [OfferController::class, 'destroy'])->name('offers.destroy');

    // Returns
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns');
    Route::post('/returns/store', [ReturnController::class, 'storeReturn'])->name('returns.store');
    Route::post('/defective/store', [ReturnController::class, 'storeDefective'])->name('defective.store');
    Route::put('/defective/{defective}', [ReturnController::class, 'updateDefective'])->name('defective.update');

    // ===== EXPENSES (Dedicated) =====
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/',                  [ExpenseController::class, 'index'])->name('index');
        Route::post('/',                 [ExpenseController::class, 'store'])->name('store');
        Route::put('/{expense}',         [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}',      [ExpenseController::class, 'destroy'])->name('destroy');
        Route::post('/{expense}/approve',[ExpenseController::class, 'approve'])->name('approve');
        Route::post('/{expense}/reject', [ExpenseController::class, 'reject'])->name('reject');
        Route::get('/summary',           [ExpenseController::class, 'summary'])->name('summary');
    });

    // ===== BUSINESS INTELLIGENCE =====
    Route::prefix('bi')->name('bi.')->group(function () {
        Route::get('/',          [BIController::class, 'index'])->name('index');
        Route::get('/pnl',       [BIController::class, 'pnl'])->name('pnl');
        Route::get('/products',  [BIController::class, 'products'])->name('products');
        Route::get('/forecast',  [BIController::class, 'forecast'])->name('forecast');
    });

    // ===== ADVANCED ANALYTICS =====
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/executive',  [AnalyticsController::class, 'executive'])->name('executive');
        Route::get('/sales',      [AnalyticsController::class, 'sales'])->name('sales');
        Route::get('/inventory',  [AnalyticsController::class, 'inventory'])->name('inventory');
        Route::get('/finance',    [AnalyticsController::class, 'finance'])->name('finance');
        Route::get('/customers',  [AnalyticsController::class, 'customers'])->name('customers');
        Route::get('/operations', [AnalyticsController::class, 'operations'])->name('operations');
    });

    // ===== FINANCE (Expanded) =====
    Route::prefix('finance')->group(function () {
        Route::get('/',                [FinanceController::class, 'index'])->name('finance');
        Route::post('/expense',        [FinanceController::class, 'storeExpense'])->name('finance.expense.store');
        Route::post('/drawer',         [FinanceController::class, 'storeDrawerEvent'])->name('finance.drawer.store');
        Route::get('/cash-flow',       [FinanceController::class, 'cashFlow'])->name('finance.cashflow');
        Route::get('/tax',             [FinanceController::class, 'taxReport'])->name('finance.tax');
    });

    // Reports Old (Keep for compatibility if needed)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Transaction Edit
    Route::get('/transactions/{transaction}/edit', [TransactionEditController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionEditController::class, 'update'])->name('transactions.update');

    // Warranty
    Route::get('/warranty', [WarrantyController::class, 'index'])->name('warranty');
    Route::post('/warranty', [WarrantyController::class, 'store'])->name('warranty.store');
    Route::delete('/warranty/{warranty}', [WarrantyController::class, 'destroy'])->name('warranty.destroy');

    // Transfers
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::delete('/transfers/{transfer}', [TransferController::class, 'destroy'])->name('transfers.destroy');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/toggles', [SettingController::class, 'updateToggles'])->name('settings.updateToggles');
    Route::get('/set-language/{lang}', [SettingController::class, 'setLanguage'])->name('set-language');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users/store', [UserController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroyUser'])->name('users.destroy');

    // Roles
    Route::post('/roles/store', [UserController::class, 'storeRole'])->name('roles.store');
    Route::put('/roles/{role}', [UserController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{role}', [UserController::class, 'destroyRole'])->name('roles.destroy');
});
