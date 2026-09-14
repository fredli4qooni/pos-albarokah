<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">
                    Ringkasan Toko Pertanian
                </h2>
                <p class="text-sm text-slate-500">
                    Selamat datang kembali, <span class="font-bold text-emerald-700">{{ Auth::user()->name }}</span>! Pantau performa toko dan stok saprotan Anda di sini.
                </p>
            </div>
            <div>
                <a href="{{ Route::has('sales.create') ? route('sales.create') : url('/sales/create') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/20 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Transaksi Baru (Kasir)</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Welcome & Quick Status Overview -->
    <div class="space-y-6">
        
        <!-- Status Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Card 1: Penjualan Hari Ini -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Penjualan Hari Ini</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-2xl font-black text-slate-800">Rp 0</h3>
                    <p class="text-xs text-slate-500 mt-1">0 transaksi berhasil hari ini</p>
                </div>
            </div>

            <!-- Card 2: Total Piutang Aktif -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Piutang Petani</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-2xl font-black text-slate-800">Rp 0</h3>
                    <p class="text-xs text-amber-600 font-semibold mt-1">0 tagihan belum lunas</p>
                </div>
            </div>

            <!-- Card 3: Rekomendasi Restock (SMA) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Rekomendasi Restok (SMA)</span>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-2xl font-black text-slate-800">0 Produk</h3>
                    <p class="text-xs text-slate-500 mt-1">Metode Moving Average 7 Hari</p>
                </div>
            </div>

            <!-- Card 4: Total Master Produk -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Produk Aktif</span>
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-2xl font-black text-slate-800">0 Produk</h3>
                    <p class="text-xs text-slate-500 mt-1">Pupuk, Pestisida, Benih, Alat</p>
                </div>
            </div>

        </div>

        <!-- Quick Launch Module Grid -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Akses Cepat Modul Utama</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <a href="{{ Route::has('sales.create') ? route('sales.create') : url('/sales/create') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Kasir POS</span>
                </a>

                <a href="{{ Route::has('products.index') ? route('products.index') : url('/products') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Stok Barang</span>
                </a>

                <a href="{{ Route::has('receivables.index') ? route('receivables.index') : url('/receivables') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Piutang Petani</span>
                </a>

                <a href="{{ Route::has('forecast.index') ? route('forecast.index') : url('/forecast') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Forecast SMA</span>
                </a>

                <a href="{{ Route::has('restocks.index') ? route('restocks.index') : url('/restocks') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Restok Masuk</span>
                </a>

                <a href="{{ Route::has('reports.index') ? route('reports.index') : url('/reports') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition group">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 text-center">Laporan PDF</span>
                </a>
            </div>
        </div>

    </div>
</x-app-layout>
