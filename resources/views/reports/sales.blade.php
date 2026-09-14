<x-app-layout>
    @section('page_title', 'Laporan Penjualan')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('reports.index') }}" class="hover:text-emerald-400 transition">Pusat Laporan</a>
                <span>/</span>
                <span class="text-slate-200">Laporan Penjualan</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight">Laporan Penjualan & Pendapatan</h1>
            <p class="text-sm text-slate-400 mt-0.5">
                Pantau histori transaksi, perputaran kas, dan komparasi pembayaran tunai vs piutang.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('reports.sales.pdf', request()->query()) }}" 
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
        <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Metode Pembayaran</label>
                <select name="payment_method" 
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="all" {{ $filters['payment_method'] === 'all' ? 'selected' : '' }}>Semua Metode</option>
                    <option value="cash" {{ $filters['payment_method'] === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                    <option value="credit" {{ $filters['payment_method'] === 'credit' ? 'selected' : '' }}>Piutang (Kredit)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-600/20 transition">
                    Terapkan Filter
                </button>
                <a href="{{ route('reports.sales') }}" 
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
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Omset Penjualan</span>
            <div class="mt-2 text-2xl font-extrabold text-emerald-400">
                Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-slate-400 mt-1 block">Dari {{ number_format($summary['total_transactions']) }} transaksi</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Penerimaan Tunai (Cash)</span>
            <div class="mt-2 text-2xl font-extrabold text-teal-400">
                Rp {{ number_format($summary['cash_sales'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-slate-400 mt-1 block">Kas masuk langsung</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Penjualan Kredit (Piutang)</span>
            <div class="mt-2 text-2xl font-extrabold text-amber-400">
                Rp {{ number_format($summary['credit_sales'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-slate-400 mt-1 block">Tercatat ke buku piutang</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Volume Produk Terjual</span>
            <div class="mt-2 text-2xl font-extrabold text-blue-400">
                {{ number_format($summary['total_items_sold']) }} <span class="text-sm font-normal text-slate-400">Unit</span>
            </div>
            <span class="text-xs text-slate-400 mt-1 block">Kuantitas item keluar</span>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Rincian Transaksi Penjualan</h2>
            <span class="text-xs text-slate-400">
                Periode: {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/60 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-4">No. Nota</th>
                        <th class="py-3 px-4">Tanggal & Jam</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4 text-center">Metode</th>
                        <th class="py-3 px-4 text-center">Item</th>
                        <th class="py-3 px-4 text-right">Total Transaksi</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-mono font-bold text-emerald-400">
                                {{ $sale->invoice_no }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-300">
                                {{ $sale->sold_at ? $sale->sold_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($sale->customer)
                                    <span class="font-medium text-white">{{ $sale->customer->name }}</span>
                                    @if($sale->customer->phone)
                                        <span class="block text-[11px] text-slate-400">{{ $sale->customer->phone }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Umum / Tunai</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-300">
                                {{ $sale->user->name ?? 'Admin' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($sale->payment_method === 'cash')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                        Tunai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        Piutang
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center text-xs font-semibold text-slate-200">
                                {{ $sale->items->sum('quantity') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-white">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('sales.show', $sale) }}" 
                                       title="Detail Nota"
                                       class="p-1.5 text-slate-400 hover:text-emerald-400 hover:bg-slate-800 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('sales.download-pdf', $sale) }}" 
                                       title="Download Struk PDF"
                                       class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm font-semibold">Tidak ada transaksi ditemukan pada rentang filter ini.</p>
                                <p class="text-xs text-slate-500 mt-1">Coba ubah tanggal mulai atau tanggal akhir filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
</x-app-layout>
