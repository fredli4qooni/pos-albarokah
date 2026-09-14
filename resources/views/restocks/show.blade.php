<x-app-layout>
    @section('page_title', 'Detail Penerimaan Barang #' . $restock->id)

    <div class="space-y-6">
        
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('restocks.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Dokumen Penerimaan Barang #{{ $restock->id }}</h2>
                    <p class="text-xs text-slate-500">Tanggal Masuk: {{ $restock->restock_date->translatedFormat('d F Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('products.show', $restock->product) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Lihat Master Produk</span>
                </a>
            </div>
        </div>

        <!-- Document Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
            
            <!-- Banner Header -->
            <div class="p-6 sm:p-8 bg-white border-b border-slate-200 text-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Produk Pertanian</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">{{ $restock->product->name ?? '-' }}</h1>
                    <p class="text-xs text-slate-500 font-mono mt-1">
                        SKU: {{ $restock->product->code ?? '-' }} • Kategori: {{ $restock->product->category->name ?? 'Pertanian' }}
                    </p>
                </div>

                <div class="text-right">
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold">Kuantitas Masuk</span>
                    <span class="text-3xl font-black text-emerald-600 font-mono mt-0.5 block">
                        +{{ number_format($restock->quantity, 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-bold text-slate-500">{{ $restock->product->unit ?? 'pcs' }}</span>
                </div>
            </div>

            <!-- Details Table -->
            <div class="p-6 sm:p-8 divide-y divide-slate-100 text-xs space-y-4">
                
                <div class="flex justify-between items-center py-2 first:pt-0">
                    <span class="text-slate-500 font-medium">Nomor Dokumen:</span>
                    <span class="font-mono font-bold text-slate-800">#REC-{{ str_pad($restock->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>

                <div class="flex justify-between items-center py-2">
                    <span class="text-slate-500 font-medium">Tanggal Pasokan Diterima:</span>
                    <span class="font-semibold text-slate-800">{{ $restock->restock_date->translatedFormat('l, d F Y') }}</span>
                </div>

                <div class="flex justify-between items-center py-2">
                    <span class="text-slate-500 font-medium">Waktu Pencatatan Sistem:</span>
                    <span class="font-mono text-slate-700">{{ $restock->created_at->format('d/m/Y H:i:s') }}</span>
                </div>

                <div class="flex justify-between items-center py-2">
                    <span class="text-slate-500 font-medium">Stok Fisik Terkini Produk:</span>
                    <span class="font-mono font-black text-sm text-emerald-700">
                        {{ number_format($restock->product->stock, 0, ',', '.') }} {{ $restock->product->unit ?? 'pcs' }}
                    </span>
                </div>

                <div class="flex justify-between items-start py-2">
                    <span class="text-slate-500 font-medium">Catatan / No. Surat Jalan Supplier:</span>
                    <span class="font-medium text-slate-800 text-right max-w-sm">
                        {{ $restock->notes ?: 'Tidak ada catatan khusus' }}
                    </span>
                </div>

            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Toko Pertanian Al Barokah • Inventori Saprotan</span>
                <a href="{{ route('restocks.index') }}" class="font-bold text-emerald-600 hover:underline">
                    &larr; Kembali ke Riwayat Penerimaan
                </a>
            </div>

        </div>

    </div>
</x-app-layout>
