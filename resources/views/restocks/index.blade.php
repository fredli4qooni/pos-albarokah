<x-app-layout>
    @section('page_title', 'Barang Masuk (Restock Pasokan)')

    <div class="space-y-6">
        
        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black tracking-tight text-slate-900">Barang Masuk & Restock Pasokan</h2>
                <p class="text-xs text-slate-500">Pencatatan penerimaan stok barang dari distributor / supplier untuk menambah inventori toko</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('restocks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Catat Barang Masuk</span>
                </a>
            </div>
        </div>

        <!-- KPI Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Total Riwayat -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Transaksi Pasokan</span>
                    <span class="text-lg font-black text-slate-800 font-mono">{{ number_format($stats['total_records'], 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">penerimaan</span>
                </div>
            </div>

            <!-- Total Unit Bulan Ini -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Unit Diterima Bulan Ini</span>
                    <span class="text-lg font-black text-emerald-600 font-mono">{{ number_format($stats['this_month_units'], 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">barang</span>
                </div>
            </div>

            <!-- Produk Terpasok -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Jenis Produk Direstock</span>
                    <span class="text-lg font-black text-teal-600 font-mono">{{ number_format($stats['total_stocked_products'], 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">variasi</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('restocks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Pasokan</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Nama produk, SKU, atau nomor nota supplier..." 
                               class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Product Dropdown Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Produk Spesifik</label>
                    <select name="product_id" class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Produk</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal</label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ request('start_date') }}" 
                           class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'product_id', 'start_date', 'end_date']))
                        <a href="{{ route('restocks.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Restocks Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Tanggal Masuk</th>
                            <th class="py-3.5 px-4">Produk Saprotan</th>
                            <th class="py-3.5 px-4 text-center">Kuantitas Masuk</th>
                            <th class="py-3.5 px-4 text-center">Stok Fisik Saat Ini</th>
                            <th class="py-3.5 px-4">Catatan / Supplier</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($restocks as $restock)
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Tanggal -->
                                <td class="py-3.5 px-4 font-mono text-slate-700">
                                    {{ $restock->restock_date->format('d/m/Y') }}
                                    <span class="block text-[10px] text-slate-400">Jam {{ $restock->created_at->format('H:i') }}</span>
                                </td>

                                <!-- Produk -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('products.show', $restock->product) }}" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                        {{ $restock->product->name ?? '-' }}
                                    </a>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-mono mt-0.5">
                                        <span>SKU: {{ $restock->product->code ?? '-' }}</span>
                                        <span>•</span>
                                        <span>{{ $restock->product->category->name ?? 'Pertanian' }}</span>
                                    </div>
                                </td>

                                <!-- Kuantitas Masuk -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 font-mono">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        <span>+{{ number_format($restock->quantity, 0, ',', '.') }} {{ $restock->product->unit ?? 'pcs' }}</span>
                                    </span>
                                </td>

                                <!-- Stok Saat Ini -->
                                <td class="py-3.5 px-4 text-center font-mono">
                                    <span class="text-xs font-bold text-slate-800">
                                        {{ number_format($restock->product->stock, 0, ',', '.') }} {{ $restock->product->unit ?? 'pcs' }}
                                    </span>
                                </td>

                                <!-- Catatan / Supplier -->
                                <td class="py-3.5 px-4 text-slate-600">
                                    @if($restock->notes)
                                        <span>{{ $restock->notes }}</span>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('restocks.show', $restock) }}" 
                                       class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition inline-block" 
                                       title="Lihat Detail Dokumen Penerimaan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-600">Belum ada riwayat penerimaan barang masuk</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Catat pasokan barang masuk pertama Anda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($restocks->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $restocks->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
