<x-app-layout>
    @section('page_title', 'Detail Produk: ' . $product->name)

    <div class="space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-600 transition mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Katalog Produk</span>
                </a>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ $product->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kategori: <span class="font-bold text-slate-700">{{ $product->category->name }}</span> | Satuan: <span class="font-bold text-slate-700">{{ $product->unit }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.barcode', $product) }}" target="_blank" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 shadow-sm transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Cetak Label QR Code</span>
                </a>
                <a href="{{ route('products.edit', $product) }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Produk</span>
                </a>
            </div>
        </div>

        <!-- 3-Column Info Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <!-- Card 1: Stok Fisik & Status -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Persediaan Stok Fisik</span>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-800">{{ $product->stock }}</span>
                    <span class="text-sm font-bold text-slate-500">{{ $product->unit }}</span>
                </div>
                <div class="mt-3">
                    @if($product->isOutOfStock())
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            Stok Habis (0)
                        </span>
                    @elseif($product->isLowStock())
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Stok Menipis (Batas Min: {{ $product->min_stock }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Stok Aman (Batas Min: {{ $product->min_stock }})
                        </span>
                    @endif
                </div>
            </div>

            <!-- Card 2: Harga & Margin -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Harga Jual & Keuntungan</span>
                <div class="mt-3">
                    <span class="text-2xl font-black text-slate-900">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                    <span class="block text-xs text-slate-400 mt-0.5">Beli: Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</span>
                </div>
                @php
                    $marginRp = (float)$product->selling_price - (float)$product->purchase_price;
                    $marginPct = $product->purchase_price > 0 ? ($marginRp / (float)$product->purchase_price) * 100 : 0;
                @endphp
                <div class="mt-2 text-xs font-bold text-emerald-700">
                    Margin: +Rp {{ number_format($marginRp, 0, ',', '.') }} ({{ number_format($marginPct, 1) }}%)
                </div>
            </div>

            <!-- Card 3: Kode SKU & Barcode -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Identitas Produk</span>
                <div class="mt-3 space-y-1.5 font-mono text-xs">
                    <div>
                        <span class="text-slate-400">SKU:</span>
                        <span class="font-bold text-slate-800">{{ $product->code }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Kode QR:</span>
                        <span class="font-bold text-slate-800">{{ $product->barcode ?: $product->code }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Label Preview Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-1">Label Stiker QR Code Rak Toko</h3>
            <p class="text-xs text-slate-500 mb-4">Gunakan stiker QR Code ini untuk ditempel pada botol, karung pupuk, atau rak display toko agar kasir dapat memindai dengan kamera HP secara instan (360°).</p>

            <div class="inline-block p-4 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 text-center shadow-xs">
                <div class="text-[10px] font-bold text-emerald-700 tracking-wider uppercase">TOKO PERTANIAN AL BAROKAH</div>
                <div class="text-xs font-black text-slate-900 mt-0.5">{{ $product->name }}</div>
                <div class="my-3 flex justify-center">
                    <div class="bg-white p-2.5 border border-slate-200 rounded-xl shadow-xs inline-block">
                        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->margin(1)->generate($product->barcode ?: $product->code) !!}
                        <div class="text-[11px] font-mono font-bold tracking-wider text-slate-700 mt-1.5">
                            {{ $product->barcode ?: $product->code }}
                        </div>
                    </div>
                </div>
                <div class="text-sm font-black text-slate-900">Rp {{ number_format($product->selling_price, 0, ',', '.') }} / {{ $product->unit }}</div>
            </div>
        </div>

        <!-- Recent Restock History Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-4">Riwayat Pasokan / Restok Terakhir</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-2.5 px-4">Tanggal Masuk</th>
                            <th class="py-2.5 px-4">Jumlah Masuk</th>
                            <th class="py-2.5 px-4">Catatan / Supplier</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($product->restocks as $restock)
                            <tr>
                                <td class="py-3 px-4 font-semibold text-slate-700">{{ \Carbon\Carbon::parse($restock->restock_date)->translatedFormat('d F Y') }}</td>
                                <td class="py-3 px-4 font-bold text-emerald-700">+{{ $restock->quantity }} {{ $product->unit }}</td>
                                <td class="py-3 px-4 text-slate-500">{{ $restock->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400">Belum ada riwayat restok barang masuk untuk produk ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
