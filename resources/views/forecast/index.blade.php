<x-app-layout>
    @section('page_title', 'Rekomendasi Restok (Demand Forecasting SMA 7-Hari)')

    <div x-data="{
            detailModalOpen: false,
            selectedItem: null,

            openDetailModal(item) {
                this.selectedItem = item;
                this.detailModalOpen = true;
            },

            formatNumber(val) {
                return new Intl.NumberFormat('id-ID').format(val || 0);
            }
         }" 
         class="space-y-6">

        <!-- Banner Header & Formula Explainer -->
        <div class="bg-white text-slate-800 p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>Decision Support System (DSS) • Single Moving Average</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900">
                    Peramalan Permintaan & Rekomendasi Restok
                </h2>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Sistem menganalisis histori penjualan harian dengan jendela waktu tetap <strong class="text-slate-900">n = {{ $windowDays }} Hari</strong> sesuai formula:
                    <span class="inline-block px-2 py-0.5 mx-1 bg-emerald-50 rounded-md font-mono text-emerald-800 font-bold border border-emerald-200">
                        F(t+1) = (X_t + ... + X_t-6) / 7
                    </span>.
                    Produk yang memiliki histori < 7 hari otomatis berstatus <em>"Data Belum Mencukupi"</em>.
                </p>
            </div>

            <!-- On-Demand Refresh Calculation Button -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-shrink-0">
                <form method="POST" action="{{ route('forecast.calculate') }}">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Hitung Ulang Rekomendasi</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- KPI Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Produk -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Produk</span>
                    <span class="text-lg font-black text-slate-800 font-mono">{{ $stats['total_products'] }}</span>
                    <span class="text-[10px] text-slate-400">saprotan</span>
                </div>
            </div>

            <!-- Perlu Restok (Mendesak) -->
            <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-sm flex items-center gap-3 bg-rose-50/20">
                <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider block">Perlu Restok Segera</span>
                    <span class="text-lg font-black text-rose-700 font-mono">{{ $stats['needs_restock_count'] }}</span>
                    <span class="text-[10px] text-rose-500 font-medium">Stok &lt; Forecast</span>
                </div>
            </div>

            <!-- Stok Aman -->
            <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm flex items-center gap-3 bg-emerald-50/20">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider block">Stok Aman</span>
                    <span class="text-lg font-black text-emerald-700 font-mono">{{ $stats['safe_stock_count'] }}</span>
                    <span class="text-[10px] text-emerald-600 font-medium">Stok &ge; Forecast</span>
                </div>
            </div>

            <!-- Data Belum Mencukupi -->
            <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm flex items-center gap-3 bg-amber-50/20">
                <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider block">Data Belum Mencukupi</span>
                    <span class="text-lg font-black text-amber-800 font-mono">{{ $stats['insufficient_data_count'] }}</span>
                    <span class="text-[10px] text-amber-600 font-medium">&lt; 7 Hari Penjualan</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <form method="GET" action="{{ route('forecast.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Produk</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Nama produk, kode SKU..." 
                               class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                    <select name="category_id" class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Rekomendasi</label>
                    <select name="status" class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Status</option>
                        <option value="perlu_restok" {{ request('status') === 'perlu_restok' ? 'selected' : '' }}>Perlu Restok Segera</option>
                        <option value="aman" {{ request('status') === 'aman' ? 'selected' : '' }}>Stok Aman</option>
                        <option value="insufficient_data" {{ request('status') === 'insufficient_data' ? 'selected' : '' }}>Data Belum Mencukupi</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        Terapkan
                    </button>
                    @if(request()->anyFilled(['search', 'category_id', 'status']))
                        <a href="{{ route('forecast.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Quick Filter Pills -->
            <div class="flex items-center gap-2 pt-1 border-t border-slate-100 overflow-x-auto">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Status:</span>
                <a href="{{ route('forecast.index') }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Status ({{ $stats['total_products'] }})
                </a>
                <a href="{{ route('forecast.index', ['status' => 'perlu_restok']) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ request('status') === 'perlu_restok' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    Perlu Restok ({{ $stats['needs_restock_count'] }})
                </a>
                <a href="{{ route('forecast.index', ['status' => 'aman']) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ request('status') === 'aman' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Stok Aman ({{ $stats['safe_stock_count'] }})
                </a>
                <a href="{{ route('forecast.index', ['status' => 'insufficient_data']) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ request('status') === 'insufficient_data' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Data &lt; 7 Hari ({{ $stats['insufficient_data_count'] }})
                </a>
            </div>
        </div>

        <!-- Main Forecast & Restock Recommendation Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Produk Saprotan</th>
                            <th class="py-3.5 px-4 text-center">Stok Fisik Saat Ini</th>
                            <th class="py-3.5 px-4 text-center">Histori Terdata</th>
                            <th class="py-3.5 px-4 text-center">Prediksi Permintaan (SMA 7-Hari)</th>
                            <th class="py-3.5 px-4 text-center">Status Rekomendasi</th>
                            <th class="py-3.5 px-4 text-center">Saran Tambah Stok</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($results as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Nama Produk -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('products.show', $item['product']) }}" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                        {{ $item['product']->name }}
                                    </a>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-mono mt-0.5">
                                        <span>SKU: {{ $item['product']->code }}</span>
                                        <span>•</span>
                                        <span>{{ $item['product']->category->name ?? 'Pertanian' }}</span>
                                    </div>
                                </td>

                                <!-- Stok Fisik Saat Ini -->
                                <td class="py-3.5 px-4 text-center font-mono">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-lg {{ $item['product']->stock <= 0 ? 'bg-rose-100 text-rose-700' : 'text-slate-800' }}">
                                        {{ number_format($item['actual_stock'], 0, ',', '.') }} {{ $item['product']->unit }}
                                    </span>
                                </td>

                                <!-- Histori Data -->
                                <td class="py-3.5 px-4 text-center font-mono">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold {{ $item['has_enough_data'] ? 'text-slate-700' : 'text-amber-600' }}">
                                        <span>{{ $item['days_recorded'] }} / {{ $item['window_days'] }} Hari</span>
                                    </span>
                                </td>

                                <!-- Prediksi SMA -->
                                <td class="py-3.5 px-4 text-center font-mono">
                                    @if($item['has_enough_data'])
                                        <span class="text-xs font-extrabold text-slate-900">
                                            {{ number_format($item['forecast_value'], 2, ',', '.') }} {{ $item['product']->unit }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">-</span>
                                    @endif
                                </td>

                                <!-- Status Rekomendasi Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($item['status'] === 'perlu_restok')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200">
                                            PERLU RESTOK
                                        </span>
                                    @elseif($item['status'] === 'aman')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            STOK AMAN
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200" title="Data penjualan kurang dari 7 hari">
                                            DATA BELUM CUKUP
                                        </span>
                                    @endif
                                </td>

                                <!-- Saran Tambah Stok -->
                                <td class="py-3.5 px-4 text-center font-mono">
                                    @if($item['status'] === 'perlu_restok')
                                        <span class="text-xs font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                                            +{{ number_format($item['shortage_qty'], 0, ',', '.') }} {{ $item['product']->unit }}
                                        </span>
                                    @elseif($item['status'] === 'aman')
                                        <span class="text-slate-500 text-[11px] font-medium">0 {{ $item['product']->unit }} (Aman)</span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">-</span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($item['status'] === 'perlu_restok')
                                            <a href="{{ route('restocks.create', ['product_id' => $item['product']->id, 'quantity' => $item['shortage_qty']]) }}" 
                                               class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-[11px] shadow-sm transition inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                <span>Restock</span>
                                            </a>
                                        @endif

                                        <button @click="openDetailModal({{ Js::from($item) }})" 
                                                type="button" 
                                                class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[11px] transition" 
                                                title="Lihat Rincian Rumus Matematis SMA">
                                            Rumus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-600">Tidak ada data produk yang cocok</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Sesuaikan kata kunci pencarian atau filter status Anda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL: Transparansi Rumus Matematis SMA (Alpine.js) -->
        <div x-show="detailModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="detailModalOpen = false" class="fixed inset-0 bg-slate-900/70 transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    
                    <!-- Modal Header -->
                    <div class="p-5 bg-white border-b border-slate-200 text-slate-900 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-emerald-600 font-mono uppercase tracking-wider block font-bold">Transparansi Formula SMA</span>
                            <h3 class="text-sm font-bold text-slate-900" x-text="selectedItem?.product?.name"></h3>
                        </div>
                        <button @click="detailModalOpen = false" type="button" class="text-slate-400 hover:text-slate-700 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-5 text-xs">
                        
                        <!-- Penjelasan Rumus Banner -->
                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 font-mono text-center">
                            <p class="text-[11px] text-slate-500 mb-1">Rumus Single Moving Average (n = 7 Hari):</p>
                            <p class="text-xs font-black text-slate-800">
                                F(t+1) = (X₁ + X₂ + X₃ + X₄ + X₅ + X₆ + X₇) / 7
                            </p>
                        </div>

                        <!-- 7 Data Points Table -->
                        <div>
                            <h4 class="font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-2">
                                Riwayat Penjualan Harian (<span x-text="selectedItem?.sales_history?.length || 0"></span> Titik Data):
                            </h4>
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <table class="w-full text-left font-mono text-[11px]">
                                    <thead class="bg-slate-100 text-slate-600 text-[10px] uppercase font-bold">
                                        <tr>
                                            <th class="py-2 px-3">Tanggal Penjualan</th>
                                            <th class="py-2 px-3 text-right">Kuantitas Terjual</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="item in selectedItem?.sales_history" :key="item.id">
                                            <tr>
                                                <td class="py-2 px-3 text-slate-600" x-text="item.sale_date ? item.sale_date.substring(0, 10) : '-'"></td>
                                                <td class="py-2 px-3 text-right font-bold text-slate-800" x-text="item.quantity_sold + ' ' + (selectedItem?.product?.unit || '')"></td>
                                            </tr>
                                        </template>
                                        <template x-if="!selectedItem?.sales_history?.length">
                                            <tr>
                                                <td colspan="2" class="py-4 text-center text-slate-400">Belum ada riwayat penjualan</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                                        <tr>
                                            <td class="py-2 px-3 text-slate-700">Total Penjualan (<span x-text="selectedItem?.days_recorded"></span> Hari):</td>
                                            <td class="py-2 px-3 text-right text-emerald-600" x-text="(selectedItem?.total_quantity || 0) + ' ' + (selectedItem?.product?.unit || '')"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Step by Step Calculation Breakdown -->
                        <div class="p-4 rounded-2xl border space-y-2"
                             :class="{
                                 'bg-rose-50 border-rose-200 text-rose-950': selectedItem?.status === 'perlu_restok',
                                 'bg-emerald-50 border-emerald-200 text-emerald-950': selectedItem?.status === 'aman',
                                 'bg-amber-50 border-amber-200 text-amber-950': selectedItem?.status === 'insufficient_data'
                             }">
                            <div class="flex justify-between font-mono">
                                <span>Nilai Peramalan (SMA):</span>
                                <span class="font-black" x-text="selectedItem?.has_enough_data ? (selectedItem?.forecast_value + ' ' + selectedItem?.product?.unit) : 'Tidak dapat dihitung (< 7 hari)'"></span>
                            </div>
                            <div class="flex justify-between font-mono">
                                <span>Stok Fisik di Toko:</span>
                                <span class="font-bold" x-text="(selectedItem?.actual_stock || 0) + ' ' + (selectedItem?.product?.unit || '')"></span>
                            </div>
                            <div class="flex justify-between font-mono border-t border-slate-300/60 pt-1.5">
                                <span class="font-bold">Status Evaluasi:</span>
                                <span class="font-black uppercase" x-text="selectedItem?.status_label"></span>
                            </div>
                            <template x-if="selectedItem?.status === 'perlu_restok'">
                                <div class="flex justify-between font-mono text-rose-700 font-bold">
                                    <span>Kekurangan Stok:</span>
                                    <span class="font-black" x-text="'+' + selectedItem?.shortage_qty + ' ' + selectedItem?.product?.unit"></span>
                                </div>
                            </template>
                            <p class="text-[11px] pt-1 font-sans italic" x-text="selectedItem?.message"></p>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">PRD §2.1 Toko Pertanian Al Barokah</span>
                        <div class="flex items-center gap-2">
                            <template x-if="selectedItem?.status === 'perlu_restok'">
                                <a :href="'/restocks/create?product_id=' + selectedItem?.product?.id + '&quantity=' + selectedItem?.shortage_qty" 
                                   class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition">
                                    Restock Sekarang
                                </a>
                            </template>
                            <button @click="detailModalOpen = false" type="button" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs transition">
                                Tutup
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
