<x-app-layout>
    @section('page_title', 'Riwayat Transaksi Penjualan')

    <div class="space-y-6">
        
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black tracking-tight text-slate-900">Riwayat Penjualan Kasir</h2>
                <p class="text-xs text-slate-500">Semua catatan transaksi kasir tunai dan kredit piutang toko</p>
            </div>
            <div>
                <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Buka Terminal Kasir (POS)</span>
                </a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Transaksi Hari Ini -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Transaksi Hari Ini</span>
                    <span class="text-lg font-black text-slate-800 font-mono">{{ number_format($stats['today_count'], 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">nota</span>
                </div>
            </div>

            <!-- Omset Hari Ini -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Omset Hari Ini</span>
                    <span class="text-lg font-black text-emerald-600 font-mono">Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Omset Tunai -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Tunai Diterima</span>
                    <span class="text-lg font-black text-teal-600 font-mono">Rp {{ number_format($stats['today_cash_revenue'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Kredit / Piutang -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Penjualan Kredit</span>
                    <span class="text-lg font-black text-amber-600 font-mono">{{ number_format($stats['today_credit_count'], 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">(Rp {{ number_format($stats['today_credit_amount'], 0, ',', '.') }})</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Transaksi</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="No. Invoice atau nama petani..." 
                               class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Metode Bayar</label>
                    <select name="payment_method" class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="credit" {{ request('payment_method') === 'credit' ? 'selected' : '' }}>Kredit (Piutang)</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal</label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ request('start_date') }}" 
                           class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Action Filter -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'payment_method', 'start_date', 'end_date']))
                        <a href="{{ route('sales.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Sales Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">No. Invoice</th>
                            <th class="py-3.5 px-4">Waktu Transaksi</th>
                            <th class="py-3.5 px-4">Pelanggan Petani</th>
                            <th class="py-3.5 px-4 text-center">Metode</th>
                            <th class="py-3.5 px-4 text-center">Item</th>
                            <th class="py-3.5 px-4 text-right">Total Transaksi</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('sales.show', $sale) }}" class="font-mono font-bold text-slate-900 hover:text-emerald-600">
                                        {{ $sale->invoice_no }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 font-mono">
                                    {{ $sale->sold_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($sale->customer)
                                        <a href="{{ route('customers.show', $sale->customer) }}" class="font-bold text-slate-800 hover:text-emerald-600 block">
                                            {{ $sale->customer->name }}
                                        </a>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $sale->customer->phone ?? '-' }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Umum / Anonim</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($sale->payment_method === 'cash')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            TUNAI
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                            KREDIT
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-semibold text-slate-700">
                                    {{ $sale->items->count() }} jenis
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 text-sm">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('sales.show', $sale) }}" class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Lihat Faktur">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('sales.print', $sale) }}" target="_blank" class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition" title="Cetak Struk Thermal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                        <a href="{{ route('sales.download-pdf', $sale) }}" class="p-1.5 text-slate-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition" title="Unduh PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-600">Belum ada catatan transaksi</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Buka terminal kasir untuk memulai penjualan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sales->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
