<x-app-layout>
    @section('page_title', 'Master Data Barang & Inventori')

    <div class="space-y-6">
        
        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">Katalog Barang Pertanian</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data pupuk, herbisida, insektisida, benih, harga jual, dan stok gudang</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('categories.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Kelola Kategori</span>
                </a>
                <a href="{{ route('products.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-600/20 transition duration-150 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Produk</span>
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('products.index') }}" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:border-emerald-300 transition flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Katalog Produk</span>
                    <p class="text-xl font-black text-slate-800 mt-1">{{ $totalProductsCount }} Produk</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </a>

            <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:border-amber-300 transition flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Stok Menipis (≤ Min)</span>
                    <p class="text-xl font-black text-amber-600 mt-1">{{ $lowStockCount }} Produk</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </a>

            <a href="{{ route('products.index', ['stock_status' => 'out']) }}" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:border-rose-300 transition flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Stok Habis (0)</span>
                    <p class="text-xl font-black text-rose-600 mt-1">{{ $outOfStockCount }} Produk</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
            </a>
        </div>

        <!-- Filter & Search Toolbar Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col md:flex-row md:items-center gap-3">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama produk, SKU, atau kode barcode..." 
                           class="block w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />
                </div>

                <!-- Category Dropdown Filter -->
                <div class="w-full md:w-56">
                    <select name="category_id" onchange="this.form.submit()" 
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Status Filter -->
                <div class="w-full md:w-48">
                    <select name="stock_status" onchange="this.form.submit()" 
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        <option value="">Status Stok: Semua</option>
                        <option value="safe" {{ $stockStatus === 'safe' ? 'selected' : '' }}>Stok Aman</option>
                        <option value="low" {{ $stockStatus === 'low' ? 'selected' : '' }}>Stok Menipis (≤ Min)</option>
                        <option value="out" {{ $stockStatus === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl transition shadow-sm">
                        Filter
                    </button>
                    @if($search || $categoryId || $stockStatus)
                        <a href="{{ route('products.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Products Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-5">Produk / Barang</th>
                            <th class="py-3.5 px-5">Kategori</th>
                            <th class="py-3.5 px-5">Satuan</th>
                            <th class="py-3.5 px-5 text-right">Harga Beli</th>
                            <th class="py-3.5 px-5 text-right">Harga Jual</th>
                            <th class="py-3.5 px-5 text-right">Margin / Laba</th>
                            <th class="py-3.5 px-5 text-center">Stok Fisik</th>
                            <th class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($products as $product)
                            @php
                                $marginRp = (float)$product->selling_price - (float)$product->purchase_price;
                                $marginPct = $product->purchase_price > 0 ? ($marginRp / (float)$product->purchase_price) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-slate-900">{{ $product->name }}</div>
                                    <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500 font-mono">
                                        <span>SKU: {{ $product->code }}</span>
                                        @if($product->barcode)
                                            <span class="text-slate-300">|</span>
                                            <span>BC: {{ $product->barcode }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700">
                                        {{ $product->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 font-semibold text-slate-600">
                                    {{ $product->unit }}
                                </td>
                                <td class="py-3.5 px-5 text-right font-medium text-slate-500">
                                    Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-5 text-right font-bold text-slate-900">
                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <span class="font-bold text-emerald-700">+Rp {{ number_format($marginRp, 0, ',', '.') }}</span>
                                    <span class="block text-[10px] text-slate-400">({{ number_format($marginPct, 1) }}%)</span>
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    @if($product->isOutOfStock())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                            0 {{ $product->unit }} (Habis)
                                        </span>
                                    @elseif($product->isLowStock())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Batas Minimum: {{ $product->min_stock }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            {{ $product->stock }} {{ $product->unit }} (Menipis)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            {{ $product->stock }} {{ $product->unit }} (Aman)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Detail & Barcode -->
                                        <a href="{{ route('products.show', $product) }}" 
                                           class="p-1.5 text-slate-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition" 
                                           title="Detail & Barcode">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('products.edit', $product) }}" 
                                           class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" 
                                           title="Edit Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ addslashes($product->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" 
                                                    title="Hapus Produk">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Tidak ada produk yang cocok dengan pencarian atau filter Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
