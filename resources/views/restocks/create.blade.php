<x-app-layout>
    @section('page_title', 'Catat Barang Masuk (Restock)')

    <div x-data="{
            products: {{ Js::from($products) }},
            selectedId: '{{ $selectedProductId ?? '' }}',
            quantity: {{ $suggestedQuantity ?? 1 }},

            get currentProduct() {
                return this.products.find(p => p.id == this.selectedId) || null;
            },

            get currentStock() {
                return this.currentProduct ? this.currentProduct.stock : 0;
            },

            get projectedStock() {
                const qty = parseInt(this.quantity) || 0;
                return this.currentStock + qty;
            },

            addQty(delta) {
                const current = parseInt(this.quantity) || 0;
                this.quantity = Math.max(1, current + delta);
            }
         }" 
         class="max-w-3xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('restocks.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Penerimaan Barang Masuk</h2>
                    <p class="text-xs text-slate-500">Form input penambahan stok produk fisik dari supplier atau distributor</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden p-6 sm:p-8">
            <form method="POST" action="{{ route('restocks.store') }}" class="space-y-6">
                @csrf

                <!-- Pilih Produk -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Produk Pertanian <span class="text-rose-500">*</span>
                    </label>
                    <select x-model="selectedId" 
                            name="product_id" 
                            required 
                            class="w-full text-xs py-3 px-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white font-medium">
                        <option value="">-- Pilih Produk yang Masuk --</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" 
                                    :selected="p.id == selectedId"
                                    x-text="p.name + ' (' + p.code + ') • Stok Saat Ini: ' + p.stock + ' ' + p.unit">
                            </option>
                        </template>
                    </select>
                    <x-input-error :messages="$errors->get('product_id')" class="mt-1" />
                </div>

                <!-- Live Stock Projection Card -->
                <div x-show="currentProduct" 
                     x-transition 
                     class="p-4 bg-slate-50 border border-slate-200 rounded-2xl shadow-sm grid grid-cols-3 gap-4 text-center">
                    <div>
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-bold">Stok Saat Ini</span>
                        <span class="text-lg font-black font-mono mt-0.5 block text-slate-700" x-text="currentStock + ' ' + (currentProduct?.unit || '')"></span>
                    </div>
                    <div class="border-x border-slate-200 px-2">
                        <span class="text-[10px] text-emerald-700 uppercase tracking-wider block font-bold">+ Barang Masuk</span>
                        <span class="text-lg font-black font-mono mt-0.5 block text-emerald-700" x-text="'+' + (quantity || 0) + ' ' + (currentProduct?.unit || '')"></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-teal-700 uppercase tracking-wider block font-bold">Estimasi Stok Baru</span>
                        <span class="text-xl font-black font-mono mt-0.5 block text-teal-700" x-text="projectedStock + ' ' + (currentProduct?.unit || '')"></span>
                    </div>
                </div>

                <!-- Kuantitas & Shortcut -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Jumlah Kuantitas Masuk <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input x-model="quantity" 
                               type="number" 
                               name="quantity" 
                               min="1" 
                               required 
                               placeholder="Jumlah barang masuk" 
                               class="w-full text-base font-mono font-bold py-2.5 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <span class="text-xs font-bold text-slate-400" x-text="currentProduct ? currentProduct.unit : 'Unit'"></span>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('quantity')" class="mt-1" />

                    <!-- Quick Add Quantity Buttons -->
                    <div class="flex items-center gap-2 pt-1">
                        <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Tambah Cepat:</span>
                        <button @click="addQty(5)" type="button" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">+5</button>
                        <button @click="addQty(10)" type="button" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">+10</button>
                        <button @click="addQty(20)" type="button" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">+20</button>
                        <button @click="addQty(50)" type="button" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">+50</button>
                    </div>
                </div>

                <!-- Tanggal Penerimaan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tanggal Penerimaan Barang <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" 
                           name="restock_date" 
                           value="{{ old('restock_date', now()->format('Y-m-d')) }}" 
                           required 
                           class="w-full text-xs py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                    <x-input-error :messages="$errors->get('restock_date')" class="mt-1" />
                </div>

                <!-- Catatan / No. Nota / Distributor -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nomor Surat Jalan / Catatan Supplier / Distributor
                    </label>
                    <input type="text" 
                           name="notes" 
                           value="{{ old('notes') }}" 
                           placeholder="Contoh: PT Petrokimia Gresik - Surat Jalan #SJ-88910" 
                           class="w-full text-xs py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                    <p class="text-[10px] text-slate-400 mt-1">Opsional: cantumkan nama distributor atau referensi nomor faktur pembelian.</p>
                    <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('restocks.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" 
                            :disabled="!selectedId || quantity <= 0"
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span>Konfirmasi & Simpan Stok Masuk</span>
                    </button>
                </div>

            </form>
        </div>

    </div>
</x-app-layout>
