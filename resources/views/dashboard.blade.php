<x-app-layout>
    @section('page_title', 'Dashboard Eksekutif')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Dashboard Operasional Toko Pertanian
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Selamat datang kembali, <span class="font-bold text-emerald-700">{{ Auth::user()->name }}</span>! Pantau performa omset kasir, piutang, dan rekomendasi restok inventori saprotan Anda.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sales.create') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Kasir (POS)</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        
        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Penjualan Hari Ini -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Penjualan Hari Ini</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-slate-900 font-mono tracking-tight">
                        Rp {{ number_format($stats['today_sales_revenue'], 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <span class="font-bold text-emerald-600 font-mono">{{ $stats['today_sales_count'] }}</span> nota transaksi berhasil hari ini
                    </p>
                </div>
            </div>

            <!-- Card 2: Omset Bulan Ini -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Omset Bulan Berjalan</span>
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-teal-700 font-mono tracking-tight">
                        Rp {{ number_format($stats['month_sales_revenue'], 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Bulan {{ now()->translatedFormat('F Y') }} ({{ $stats['month_sales_count'] }} transaksi)
                    </p>
                </div>
            </div>

            <!-- Card 3: Total Piutang Petani Aktif -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sisa Piutang Petani</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-rose-600 font-mono tracking-tight">
                        Rp {{ number_format($stats['total_active_debt'], 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-amber-700 font-semibold mt-1">
                        {{ $stats['total_active_debtors'] }} petani memiliki tagihan aktif
                    </p>
                </div>
            </div>

            <!-- Card 4: Rekomendasi Restok SMA -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rekomendasi Restok SMA</span>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-2xl font-black text-rose-700 font-mono tracking-tight">
                            {{ $stats['sma_needs_restock_count'] }}
                        </h3>
                        <span class="text-xs font-bold text-slate-400">Produk</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        <span class="text-amber-600 font-semibold">{{ $stats['low_stock_count'] }} menipis</span> • <span class="text-rose-600 font-semibold">{{ $stats['out_of_stock_count'] }} habis</span>
                    </p>
                </div>
            </div>

        </div>

        <!-- 14-Day Sales Trend Chart (Chart.js) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        <span>Tren Omset Penjualan 14 Hari Terakhir</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Visualisasi perbandingan pendapatan tunai dan kredit harian toko</p>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="text-slate-600 font-medium">Tunai</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                        <span class="text-slate-600 font-medium">Kredit</span>
                    </div>
                </div>
            </div>

            <!-- Canvas Container -->
            <div class="relative mt-4 h-72 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- 2 Columns: Urgent Restock Alerts & Top Debtors -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            
            <!-- Column 1: Urgent Restock Alerts (SMA & Min Stock) -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                                !
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Peringatan Restok Kritis</h3>
                                <p class="text-[11px] text-slate-400">Produk perlu pasokan berdasarkan SMA & batas minimum</p>
                            </div>
                        </div>
                        <a href="{{ route('forecast.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    <!-- List Items -->
                    <div class="divide-y divide-slate-100 mt-2">
                        @forelse($urgentRestocks as $item)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-bold text-slate-900 truncate">{{ $item->name }}</h4>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-400 font-mono mt-0.5">
                                        <span>SKU: {{ $item->code }}</span>
                                        <span>•</span>
                                        <span class="text-rose-600 font-semibold">Stok: {{ $item->stock }} {{ $item->unit }}</span>
                                        <span>(Min: {{ $item->min_stock }})</span>
                                    </div>
                                </div>

                                <div>
                                    <a href="{{ route('restocks.create', ['product_id' => $item->id]) }}" 
                                       class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-[11px] shadow-sm transition inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        <span>Restock</span>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto mb-1 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-xs font-semibold text-slate-700">Semua stok barang dalam kondisi aman</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Column 2: 5 Top Debtors (Petani Piutang Terbesar) -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                Rp
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Piutang Petani Terbesar</h3>
                                <p class="text-[11px] text-slate-400">5 pelanggan dengan akumulasi sisa utang tertinggi</p>
                            </div>
                        </div>
                        <a href="{{ route('receivables.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                            Kelola Piutang &rarr;
                        </a>
                    </div>

                    <!-- List Items -->
                    <div class="divide-y divide-slate-100 mt-2">
                        @forelse($topDebtors as $debtor)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-bold text-slate-900 truncate">{{ $debtor->name }}</h4>
                                    @if($debtor->phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $debtor->phone) }}" target="_blank" class="text-[10px] text-emerald-600 hover:underline font-mono block mt-0.5">
                                            WA: {{ $debtor->phone }}
                                        </a>
                                    @else
                                        <span class="text-[10px] text-slate-400">Tanpa nomor telepon</span>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <span class="text-xs font-black text-rose-600 font-mono block">
                                        Rp {{ number_format($debtor->receivables_sum_remaining_balance, 0, ',', '.') }}
                                    </span>
                                    <a href="{{ route('customers.show', $debtor) }}" class="text-[10px] text-slate-500 hover:text-emerald-600 hover:underline">
                                        Lihat Profil &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto mb-1 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-xs font-semibold text-slate-700">Tidak ada piutang aktif yang belum lunas</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Sales Table -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Transaksi Kasir Terkini</h3>
                    <p class="text-[11px] text-slate-400">5 transaksi penjualan terakhir yang diproses kasir</p>
                </div>
                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                    Semua Transaksi &rarr;
                </a>
            </div>

            <div class="overflow-x-auto mt-2">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                            <th class="py-3 px-3">No. Invoice</th>
                            <th class="py-3 px-3">Waktu</th>
                            <th class="py-3 px-3">Pelanggan</th>
                            <th class="py-3 px-3 text-center">Metode</th>
                            <th class="py-3 px-3 text-right">Total Transaksi</th>
                            <th class="py-3 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentSales as $sale)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                    {{ $sale->invoice_no }}
                                </td>
                                <td class="py-3 px-3 text-slate-500 font-mono">
                                    {{ $sale->sold_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-800">
                                    {{ $sale->customer->name ?? 'Pelanggan Umum' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($sale->payment_method === 'cash')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                            TUNAI
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">
                                            KREDIT
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-black text-slate-900">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">
                                    Belum ada transaksi penjualan yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Launch Module Grid -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Akses Cepat Modul Toko</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <a href="{{ route('sales.create') }}" class="flex flex-col items-center justify-center p-3.5 rounded-2xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Kasir POS</span>
                </a>

                <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center p-3.5 rounded-2xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Stok Barang</span>
                </a>

                <a href="{{ route('receivables.index') }}" class="flex flex-col items-center justify-center p-3.5 rounded-2xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Piutang Petani</span>
                </a>

                <a href="{{ route('forecast.index') }}" class="flex flex-col items-center justify-center p-3.5 rounded-2xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Forecast SMA</span>
                </a>

                <a href="{{ route('restocks.index') }}" class="flex flex-col items-center justify-center p-3.5 rounded-2xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Restok Masuk</span>
                </a>

                <a href="{{ Route::has('reports.index') ? route('reports.index') : url('/reports') }}" class="flex flex-col items-center justify-center p-3.5 rounded-2xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Laporan PDF</span>
                </a>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartData = {{ Js::from($chartData) }};
            const ctx = document.getElementById('salesChart');

            if (ctx && window.Chart) {
                new window.Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: 'Penjualan Tunai (Rp)',
                                data: chartData.cash,
                                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                                borderColor: 'rgb(16, 185, 129)',
                                borderWidth: 1,
                                borderRadius: 6,
                                stack: 'Combined',
                            },
                            {
                                label: 'Penjualan Kredit (Rp)',
                                data: chartData.credit,
                                backgroundColor: 'rgba(245, 158, 11, 0.85)',
                                borderColor: 'rgb(245, 158, 11)',
                                borderWidth: 1,
                                borderRadius: 6,
                                stack: 'Combined',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11,
                                        family: 'monospace'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(226, 232, 240, 0.6)'
                                },
                                ticks: {
                                    font: {
                                        size: 11,
                                        family: 'monospace'
                                    },
                                    callback: function(value) {
                                        if (value >= 1000000) {
                                            return (value / 1000000).toFixed(1) + ' jt';
                                        } else if (value >= 1000) {
                                            return (value / 1000).toFixed(0) + ' rb';
                                        }
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
