<x-app-layout>
    @section('page_title', 'Laporan Stok & Restok SMA')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('reports.index') }}" class="hover:text-emerald-400 transition">Pusat Laporan</a>
                <span>/</span>
                <span class="text-slate-200">Laporan Stok & Restok SMA</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight">Laporan Inventori & Rekomendasi Restok</h1>
            <p class="text-sm text-slate-400 mt-0.5">
                Audit valuasi aset fisik toko, evaluasi batas aman stok minimum, dan prioritas pengadaan berbasis Single Moving Average (SMA 7-hari).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('reports.products.pdf', request()->query()) }}" 
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-rose-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Cetak / Ekspor PDF</span>
            </a>
            <a href="{{ route('reports.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl border border-slate-700 transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <form method="GET" action="{{ route('reports.products') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kategori Produk</label>
                <select name="category_id" 
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$filters['category_id'] === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kondisi & Status Stok</label>
                <select name="stock_status" 
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="all" {{ $filters['stock_status'] === 'all' ? 'selected' : '' }}>Semua Kondisi Stok</option>
                    <option value="low_stock" {{ $filters['stock_status'] === 'low_stock' ? 'selected' : '' }}>Stok Menipis (Stok ≤ Min)</option>
                    <option value="out_of_stock" {{ $filters['stock_status'] === 'out_of_stock' ? 'selected' : '' }}>Stok Habis (Stok = 0)</option>
                    <option value="sma_needs_restock" {{ $filters['stock_status'] === 'sma_needs_restock' ? 'selected' : '' }}>Perlu Restok (Evaluasi SMA)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-teal-600/20 transition">
                    Terapkan Filter
                </button>
                <a href="{{ route('reports.products') }}" 
                   title="Reset Filter"
                   class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-sm font-semibold rounded-xl border border-slate-700 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Valuasi Modal Inventori</span>
            <div class="mt-2 text-2xl font-extrabold text-teal-400">
                Rp {{ number_format($summary['total_purchase_valuation'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-slate-400 mt-1 block">Berdasarkan harga beli modal</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Estimasi Nilai Jual</span>
            <div class="mt-2 text-2xl font-extrabold text-emerald-400">
                Rp {{ number_format($summary['total_selling_valuation'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-slate-400 mt-1 block">Potensi perputaran kas</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Kuantitas Fisik</span>
            <div class="mt-2 text-2xl font-extrabold text-white">
                {{ number_format($summary['total_units']) }} <span class="text-sm font-normal text-slate-400">Unit</span>
            </div>
            <span class="text-xs text-slate-400 mt-1 block">Dari {{ number_format($summary['total_products']) }} varian SKU</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Peringatan Restok</span>
            <div class="mt-2 text-2xl font-extrabold text-rose-400">
                {{ $summary['low_stock_count'] + $summary['out_of_stock_count'] }} <span class="text-sm font-normal text-slate-400">Produk</span>
            </div>
            <span class="text-xs text-slate-400 mt-1 block">{{ $summary['out_of_stock_count'] }} kosong, {{ $summary['low_stock_count'] }} menipis</span>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Daftar Stok Produk & Evaluasi Restok</h2>
            <span class="text-xs text-slate-400">
                Menampilkan {{ $products->count() }} dari {{ $products->total() }} produk
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/60 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-4">Kode / SKU</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-center">Stok Fisik</th>
                        <th class="py-3 px-4 text-center">Min. Stok</th>
                        <th class="py-3 px-4 text-center">Forecast SMA</th>
                        <th class="py-3 px-4 text-center">Status SMA</th>
                        <th class="py-3 px-4 text-right">Harga Beli</th>
                        <th class="py-3 px-4 text-right">Nilai Aset</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    @forelse($products as $p)
                        @php
                            $forecast = $p->forecastResults->first();
                            $assetVal = $p->purchase_price * max(0, $p->stock);
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-mono text-xs text-slate-300">
                                {{ $p->code }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-white block">{{ $p->name }}</span>
                                <span class="text-[11px] text-slate-400">{{ $p->unit }}</span>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-300">
                                {{ $p->category->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($p->stock <= 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        Habis (0)
                                    </span>
                                @elseif($p->stock <= $p->min_stock)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        {{ $p->stock }} {{ $p->unit }}
                                    </span>
                                @else
                                    <span class="font-semibold text-emerald-400">
                                        {{ $p->stock }} {{ $p->unit }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center text-xs text-slate-400">
                                {{ $p->min_stock }}
                            </td>
                            <td class="py-3 px-4 text-center text-xs font-mono">
                                @if($forecast && $forecast->forecast_value !== null)
                                    {{ number_format($forecast->forecast_value, 1) }}
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if(!$forecast || $forecast->status === 'insufficient_data')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                        Belum Cukup Data
                                    </span>
                                @elseif($forecast->status === 'perlu_restok')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                        Perlu Restok (+{{ $forecast->shortage_quantity }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        Stok Aman
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-xs text-slate-300">
                                Rp {{ number_format($p->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-medium text-teal-400">
                                Rp {{ number_format($assetVal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('restocks.create', ['product_id' => $p->id, 'quantity' => max(1, $p->min_stock - $p->stock)]) }}" 
                                   title="Restock Produk"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-teal-400 hover:text-white bg-teal-500/10 hover:bg-teal-600 rounded-lg border border-teal-500/20 transition">
                                    Restock
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="text-sm font-semibold">Tidak ada produk ditemukan sesuai filter yang dipilih.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
</x-app-layout>
