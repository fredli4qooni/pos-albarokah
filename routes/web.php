<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

use App\Http\Controllers\SaleController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data Routes
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::get('/products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode');
    Route::resource('products', ProductController::class);
    Route::resource('customers', CustomerController::class);

    // POS Kasir & Transaksi Penjualan
    Route::get('/sales/{sale}/print', [SaleController::class, 'printReceipt'])->name('sales.print');
    Route::get('/sales/{sale}/download-pdf', [SaleController::class, 'downloadPdf'])->name('sales.download-pdf');
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
});

require __DIR__.'/auth.php';
