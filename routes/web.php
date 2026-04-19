<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS
    Route::get('/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos');
    Route::post('/pos/checkout', [\App\Http\Controllers\PosController::class, 'checkout'])->name('pos.checkout');

    // Categories
    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Inventory
    Route::get('/inventory', [\App\Http\Controllers\InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory', [\App\Http\Controllers\InventoryController::class, 'store'])->name('inventory.store');
    Route::put('/inventory/{product}', [\App\Http\Controllers\InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{product}', [\App\Http\Controllers\InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/import', [\App\Http\Controllers\InventoryController::class, 'importCSV'])->name('inventory.import');
    Route::post('/inventory/{product}/restock', [\App\Http\Controllers\InventoryController::class, 'restock'])->name('inventory.restock');
    Route::get('/inventory/template', [\App\Http\Controllers\InventoryController::class, 'downloadTemplate'])->name('inventory.template');

    // Suppliers & PO
    Route::get('/suppliers', [\App\Http\Controllers\SupplierController::class, 'index'])->name('suppliers');
    Route::post('/suppliers', [\App\Http\Controllers\SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::post('/suppliers/po', [\App\Http\Controllers\SupplierController::class, 'storePO'])->name('suppliers.po.store');
    Route::post('/suppliers/receive/{purchaseOrder}', [\App\Http\Controllers\SupplierController::class, 'receivePO'])->name('suppliers.po.receive');
    Route::post('/suppliers/po/import', [\App\Http\Controllers\SupplierController::class, 'importPOs'])->name('suppliers.po.import');
    Route::get('/suppliers/po/template', [\App\Http\Controllers\SupplierController::class, 'downloadTemplate'])->name('suppliers.po.template');
    Route::post('/purchase-orders', [\App\Http\Controllers\SupplierController::class, 'storePO'])->name('purchase-orders.store');
    Route::post('/purchase-orders/{po}/receive', [\App\Http\Controllers\SupplierController::class, 'receivePO'])->name('purchase-orders.receive');

    // Customers
    Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers');
    Route::post('/customers', [\App\Http\Controllers\CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'destroy'])->name('customers.destroy');

    // Offers
    Route::get('/offers', [\App\Http\Controllers\OfferController::class, 'index'])->name('offers');
    Route::post('/offers', [\App\Http\Controllers\OfferController::class, 'store'])->name('offers.store');
    Route::put('/offers/{offer}', [\App\Http\Controllers\OfferController::class, 'update'])->name('offers.update');
    Route::delete('/offers/{offer}', [\App\Http\Controllers\OfferController::class, 'destroy'])->name('offers.destroy');

    // Returns
    Route::get('/returns', [\App\Http\Controllers\ReturnController::class, 'index'])->name('returns');
    Route::post('/returns/store', [\App\Http\Controllers\ReturnController::class, 'storeReturn'])->name('returns.store');
    Route::post('/defective/store', [\App\Http\Controllers\ReturnController::class, 'storeDefective'])->name('defective.store');
    Route::put('/defective/{defective}', [\App\Http\Controllers\ReturnController::class, 'updateDefective'])->name('defective.update');

    // Finance
    Route::get('/finance', [\App\Http\Controllers\FinanceController::class, 'index'])->name('finance');
    Route::post('/finance/expense', [\App\Http\Controllers\FinanceController::class, 'storeExpense'])->name('finance.expense.store');
    Route::post('/finance/drawer', [\App\Http\Controllers\FinanceController::class, 'storeDrawerEvent'])->name('finance.drawer.store');

    // Reports
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports');

    // Warranty
    Route::get('/warranty', [\App\Http\Controllers\WarrantyController::class, 'index'])->name('warranty');
    Route::post('/warranty', [\App\Http\Controllers\WarrantyController::class, 'store'])->name('warranty.store');
    Route::delete('/warranty/{warranty}', [\App\Http\Controllers\WarrantyController::class, 'destroy'])->name('warranty.destroy');

    // Transfers
    Route::get('/transfers', [\App\Http\Controllers\TransferController::class, 'index'])->name('transfers');
    Route::post('/transfers', [\App\Http\Controllers\TransferController::class, 'store'])->name('transfers.store');
    Route::delete('/transfers/{transfer}', [\App\Http\Controllers\TransferController::class, 'destroy'])->name('transfers.destroy');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings');
    Route::post('/settings/update', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/toggles', [\App\Http\Controllers\SettingController::class, 'updateToggles'])->name('settings.updateToggles');
    Route::get('/set-language/{lang}', [\App\Http\Controllers\SettingController::class, 'setLanguage'])->name('set-language');

    // Users
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users');
    Route::post('/users/store', [\App\Http\Controllers\UserController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroyUser'])->name('users.destroy');

    // Roles
    Route::post('/roles/store', [\App\Http\Controllers\UserController::class, 'storeRole'])->name('roles.store');
    Route::put('/roles/{role}', [\App\Http\Controllers\UserController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{role}', [\App\Http\Controllers\UserController::class, 'destroyRole'])->name('roles.destroy');
});
