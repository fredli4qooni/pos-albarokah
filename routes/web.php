<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\RestockController;
use App\Http\Controllers\SaleController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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

    // Piutang Pelanggan Petani
    Route::get('/receivables', [ReceivableController::class, 'index'])->name('receivables.index');
    Route::get('/receivables/{receivable}', [ReceivableController::class, 'show'])->name('receivables.show');
    Route::post('/receivables/{receivable}/payments', [ReceivableController::class, 'storePayment'])->name('receivables.payments.store');
    Route::get('/receivables/{receivable}/payments/{payment}/print', [ReceivableController::class, 'printPaymentReceipt'])->name('receivables.payments.print');

    // Restock Barang Masuk (Penerimaan Pasokan)
    Route::resource('restocks', RestockController::class)->only(['index', 'create', 'store', 'show']);

    // Forecast Restok SMA (Decision Support System)
    Route::get('/forecast', [ForecastController::class, 'index'])->name('forecast.index');
    Route::post('/forecast/calculate', [ForecastController::class, 'calculate'])->name('forecast.calculate');
    Route::get('/forecast/{product}/history', [ForecastController::class, 'history'])->name('forecast.history');
});

require __DIR__.'/auth.php';
