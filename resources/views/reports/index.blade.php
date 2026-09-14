<x-app-layout>
    @section('page_title', 'Pusat Laporan & Ekspor Dokumen')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Ekspor DomPDF Terintegrasi
                    </span>
                    <span class="text-xs text-slate-500">• Standar Format A4 Resmi</span>
                </div>
                <h1 class="text-2xl lg:text-3xl font-black text-slate-900 tracking-tight">Pusat Laporan & Ekspor PDF</h1>
                <p class="text-sm text-slate-600 mt-1 max-w-2xl">
                    Pilih modul laporan di bawah ini untuk melihat pratinjau data secara interaktif atau cetak dokumen rekapitulasi resmi dengan kop surat Toko Pertanian Al Barokah.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- 3 Core Report Category Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- 1. Laporan Penjualan -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between hover:border-emerald-500/50 transition-all duration-200 shadow-sm group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 group-hover:scale-105 transition transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Penjualan & Kas
                    </span>
                </div>

                <h2 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-emerald-600 transition">Laporan Penjualan</h2>
                <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                    Rekapitulasi omset harian/bulanan, komparasi transaksi tunai vs piutang (kredit), margin keuntungan, dan rincian per kasir dengan filter tanggal fleksibel.
                </p>

                <!-- Mini Metric Stats -->
                <div class="space-y-2 mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Penjualan Bulan Ini:</span>
                        <span class="font-bold text-emerald-700 font-mono">Rp {{ number_format($salesStats['month_sales'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Volume Transaksi:</span>
                        <span class="font-semibold text-slate-800 font-mono">{{ number_format($salesStats['month_transactions']) }} Nota</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Hari Ini:</span>
                        <span class="font-semibold text-slate-800 font-mono">Rp {{ number_format($salesStats['today_sales'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('reports.sales') }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Buka Filter & Pratinjau</span>
                </a>
                <a href="{{ route('reports.sales.pdf', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()]) }}" 
                   target="_blank"
                   class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh PDF Bulan Berjalan</span>
                </a>
            </div>
        </div>

        <!-- 2. Laporan Piutang Pelanggan -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between hover:border-amber-500/50 transition-all duration-200 shadow-sm group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 group-hover:scale-105 transition transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-50 text-amber-700 border border-amber-200">
                        Piutang & Tagihan
                    </span>
                </div>

                <h2 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-amber-600 transition">Laporan Piutang Petani</h2>
                <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                    Daftar saldo piutang berjalan per pelanggan petani, riwayat angsuran pembayaran, jatuh tempo, serta status lunas/belum lunas untuk audit penagihan.
                </p>

                <!-- Mini Metric Stats -->
                <div class="space-y-2 mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Total Saldo Belum Lunas:</span>
                        <span class="font-bold text-amber-700 font-mono">Rp {{ number_format($receivableStats['total_active_balance'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Jumlah Debitur Aktif:</span>
                        <span class="font-semibold text-slate-800 font-mono">{{ $receivableStats['active_debtors_count'] }} Petani</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Akumulasi Kredit:</span>
                        <span class="font-semibold text-slate-800 font-mono">Rp {{ number_format($receivableStats['total_credit_issued'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('reports.receivables') }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Buka Filter & Pratinjau</span>
                </a>
                <a href="{{ route('reports.receivables.pdf', ['status' => 'belum_lunas']) }}" 
                   target="_blank"
                   class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh PDF Tagihan Belum Lunas</span>
                </a>
            </div>
        </div>

        <!-- 3. Laporan Stok & Restok SMA -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between hover:border-teal-500/50 transition-all duration-200 shadow-sm group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-600 group-hover:scale-105 transition transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-teal-50 text-teal-700 border border-teal-200">
                        Inventori & DSS
                    </span>
                </div>

                <h2 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-teal-600 transition">Laporan Stok & Restok SMA</h2>
                <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                    Evaluasi aset fisik inventori, batas aman stok minimum, evaluasi peramalan Single Moving Average (SMA 7-hari), dan prioritas restok pasokan.
                </p>

                <!-- Mini Metric Stats -->
                <div class="space-y-2 mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Valuasi Modal Aset:</span>
                        <span class="font-bold text-teal-700 font-mono">Rp {{ number_format($inventoryStats['total_asset_value'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Total Kuantitas Fisik:</span>
                        <span class="font-semibold text-slate-800 font-mono">{{ number_format($inventoryStats['total_stock_units']) }} Unit ({{ $inventoryStats['total_products'] }} SKU)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Perlu Restok (SMA):</span>
                        <span class="font-bold text-rose-600 font-mono">{{ $inventoryStats['sma_restock_count'] }} Produk</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('reports.products') }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Buka Filter & Pratinjau</span>
                </a>
                <a href="{{ route('reports.products.pdf') }}" 
                   target="_blank"
                   class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh PDF Seluruh Stok Fisik</span>
                </a>
            </div>
        </div>

    </div>

    <!-- Quick Info Banner -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 text-xs text-slate-600 flex items-center gap-3 shadow-sm">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>
            <strong>Panduan Penggunaan:</strong> Anda dapat menyesuaikan rentang tanggal, status pembayaran, atau kategori produk di dalam masing-masing halaman pratinjau sebelum mengekspor dokumen PDF. File PDF dihasilkan secara real-time langsung dari server.
        </span>
    </div>
</div>
</x-app-layout>
