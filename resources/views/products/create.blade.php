<x-app-layout>
    @section('page_title', 'Tambah Produk Baru')

    <div class="max-w-4xl mx-auto space-y-6" 
         x-data="{
             purchasePrice: {{ old('purchase_price', 0) }},
             sellingPrice: {{ old('selling_price', 0) }},
             code: '{{ old('code', '') }}',
             generateCode() {
                 const rand = Math.floor(1000 + Math.random() * 9000);
                 this.code = 'PRD-' + rand;
             },
             get marginRp() {
                 return (parseFloat(this.sellingPrice) || 0) - (parseFloat(this.purchasePrice) || 0);
             },
             get marginPct() {
                 const buy = parseFloat(this.purchasePrice) || 0;
                 if (buy <= 0) return 0;
                 return ((this.marginRp / buy) * 100).toFixed(1);
             }
         }">
        
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-600 transition mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Katalog Produk</span>
                </a>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">Tambah Barang Baru</h2>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
            <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
                @csrf

                <!-- Section: Informasi Dasar -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>1. Informasi Produk & Identitas</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Kategori Produk <span class="text-rose-500">*</span>
                            </label>
                            <select id="category_id" name="category_id" required 
                                    class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-1.5" />
                        </div>

                        <!-- Unit -->
                        <div>
                            <label for="unit" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Satuan Barang <span class="text-rose-500">*</span>
                            </label>
                            <select id="unit" name="unit" required 
                                    class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                                @foreach ($units as $u)
                                    <option value="{{ $u }}" {{ old('unit', 'Botol') === $u ? 'selected' : '' }}>
                                        {{ $u }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('unit')" class="mt-1.5" />
                        </div>

                        <!-- Name (Full width) -->
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Nama Produk Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required 
                                   placeholder="Contoh: Gramoxone 276 SL 1 Liter, Pupuk NPK Mutiara 16-16-16 1 Kg" 
                                   class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                        </div>

                        <!-- SKU Code -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Kode SKU Unik <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" @click="generateCode()" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">
                                    + Buat Acak
                                </button>
                            </div>
                            <input id="code" type="text" name="code" x-model="code" required 
                                   placeholder="Contoh: HRB-GRM-01" 
                                   class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                            <x-input-error :messages="$errors->get('code')" class="mt-1.5" />
                        </div>

                        <!-- Barcode -->
                        <div>
                            <label for="barcode" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Kode Barcode Fisik (Opsional)
                            </label>
                            <input id="barcode" type="text" name="barcode" value="{{ old('barcode') }}" 
                                   placeholder="Contoh: 8991234001011 (Bisa discan nanti)" 
                                   class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                            <x-input-error :messages="$errors->get('barcode')" class="mt-1.5" />
                        </div>
                    </div>
                </div>

                <!-- Section: Harga & Kalkulasi Margin -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>2. Harga & Estimasi Keuntungan</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 items-center">
                        <!-- Harga Beli (Modal) -->
                        <div>
                            <label for="purchase_price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Harga Beli / Kulak (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input id="purchase_price" type="number" name="purchase_price" x-model="purchasePrice" min="0" required 
                                   placeholder="80000" 
                                   class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                            <x-input-error :messages="$errors->get('purchase_price')" class="mt-1.5" />
                        </div>

                        <!-- Harga Jual -->
                        <div>
                            <label for="selling_price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Harga Jual Kasir (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input id="selling_price" type="number" name="selling_price" x-model="sellingPrice" min="0" required 
                                   placeholder="95000" 
                                   class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                            <x-input-error :messages="$errors->get('selling_price')" class="mt-1.5" />
                        </div>

                        <!-- Live Margin Card -->
                        <div class="p-3.5 rounded-xl bg-emerald-50/75 border border-emerald-200/80">
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-emerald-700">Estimasi Margin Laba</span>
                            <div class="mt-1 flex items-baseline gap-2">
                                <span class="text-base font-black text-emerald-800" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(marginRp))"></span>
                                <span class="text-xs font-bold text-emerald-600" x-text="'(' + marginPct + '%)'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Stok Gudang & Ambang Batas -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>3. Persediaan Stok & Ambang Minimum</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Stock -->
                        <div>
                            <label for="stock" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Jumlah Stok Awal <span class="text-rose-500">*</span>
                            </label>
                            <input id="stock" type="number" name="stock" value="{{ old('stock', 0) }}" min="0" required 
                                   class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                            <p class="text-[11px] text-slate-400 mt-1">Stok fisik aktual yang siap dijual di toko.</p>
                            <x-input-error :messages="$errors->get('stock')" class="mt-1.5" />
                        </div>

                        <!-- Min Stock -->
                        <div>
                            <label for="min_stock" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Ambang Stok Minimum (Peringatan) <span class="text-rose-500">*</span>
                            </label>
                            <input id="min_stock" type="number" name="min_stock" value="{{ old('min_stock', 5) }}" min="0" required 
                                   class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                            <p class="text-[11px] text-slate-400 mt-1">Sistem akan memunculkan status 'Menipis' jika stok ≤ nilai ini.</p>
                            <x-input-error :messages="$errors->get('min_stock')" class="mt-1.5" />
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-600/20 transition duration-150 transform hover:-translate-y-0.5">
                        Simpan Produk
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
