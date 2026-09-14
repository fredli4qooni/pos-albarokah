<x-app-layout>
    @section('page_title', 'Kasir POS (Point of Sales)')

    <div x-data="posKasir({
            productsData: {{ Js::from($products) }},
            categoriesData: {{ Js::from($categories) }},
            customersData: {{ Js::from($customers) }},
            suggestedInvoiceNo: '{{ $suggestedInvoiceNo }}',
            storeUrl: '{{ route('sales.store') }}',
            csrfToken: '{{ csrf_token() }}'
         })" 
         class="space-y-6"
         @keydown.window.f9.prevent="submitSale()"
         @keydown.window.f2.prevent="focusSearch()">

        <!-- Top Header & Shortcuts Info -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white text-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                        <span>Kasir POS</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold font-mono" x-text="suggestedInvoiceNo"></span>
                    </h2>
                    <p class="text-xs text-slate-500">Kasir: <span class="text-slate-800 font-semibold">{{ Auth::user()->name }}</span> • Tanggal: <span class="text-slate-700 font-mono font-semibold">{{ now()->format('d/m/Y') }}</span></p>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button @click="openScannerModal()" 
                        type="button" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Scan Barcode Kamera</span>
                </button>
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-2 bg-slate-100 rounded-xl border border-slate-200 text-[11px] text-slate-600">
                    <span class="font-mono bg-white border border-slate-300 px-1.5 py-0.5 rounded text-slate-800 font-bold">F2</span> Cari Barang
                    <span class="text-slate-400 mx-1">•</span>
                    <span class="font-mono bg-white border border-slate-300 px-1.5 py-0.5 rounded text-slate-800 font-bold">F9</span> Selesaikan
                </div>
            </div>
        </div>

        <!-- Main Layout: 2 Columns (Catalog 60% / Cart 40%) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT PANEL: Product Catalog & Search (7 Cols) -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Search Box & Category Filters -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input x-ref="searchInput"
                               x-model="searchQuery" 
                               @keydown.enter.prevent="handleSearchEnter()"
                               type="text" 
                               placeholder="Cari produk (Nama, Kode SKU, Barcode)... [Tekan Enter]" 
                               class="w-full pl-11 pr-24 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <span class="text-xs text-slate-400 bg-slate-200/80 px-2 py-1 rounded-md font-mono" x-text="filteredProducts.length + ' barang'"></span>
                        </div>
                    </div>

                    <!-- Category Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                        <button @click="selectedCategory = 'all'" 
                                type="button" 
                                :class="selectedCategory === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition">
                            Semua Kategori
                        </button>
                        <template x-for="cat in categories" :key="cat.id">
                            <button @click="selectedCategory = cat.id" 
                                    type="button" 
                                    :class="selectedCategory === cat.id ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition"
                                    x-text="cat.name">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Product Catalog Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5 max-h-[580px] overflow-y-auto pr-1">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             :class="{
                                 'opacity-50 cursor-not-allowed bg-slate-100 border-slate-200': product.stock <= 0,
                                 'hover:border-emerald-500 hover:shadow-md cursor-pointer bg-white border-slate-200/80': product.stock > 0
                             }"
                             class="group p-3.5 rounded-xl border transition duration-150 flex flex-col justify-between relative select-none">
                            
                            <!-- Stock Indicator Badge -->
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase font-mono tracking-wider"
                                      :class="{
                                          'bg-rose-100 text-rose-700': product.stock <= 0,
                                          'bg-amber-100 text-amber-700': product.stock > 0 && product.stock <= product.min_stock,
                                          'bg-emerald-100 text-emerald-700': product.stock > product.min_stock
                                      }"
                                      x-text="product.stock <= 0 ? 'Habis' : 'Stok: ' + product.stock + ' ' + product.unit">
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="product.code"></span>
                            </div>

                            <!-- Product Name & Category -->
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 line-clamp-2 group-hover:text-emerald-700 transition" x-text="product.name"></h3>
                                <p class="text-[10px] text-slate-400 mt-0.5" x-text="product.category?.name || 'Pertanian'"></p>
                            </div>

                            <!-- Price & Action -->
                            <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-black text-emerald-600" x-text="formatRupiah(product.selling_price)"></span>
                                <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </span>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div x-show="filteredProducts.length === 0" class="col-span-full py-12 text-center bg-white rounded-2xl border border-dashed border-slate-300">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="text-sm font-semibold text-slate-600">Tidak ada produk yang cocok</p>
                        <p class="text-xs text-slate-400 mt-1">Coba kata kunci pencarian lain atau ganti filter kategori.</p>
                    </div>
                </div>

            </div>

            <!-- RIGHT PANEL: Cart & Payment Checkout (5 Cols) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden flex flex-col">
                
                <!-- Cart Header -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-800">Keranjang Belanja</h3>
                            <p class="text-[11px] text-slate-400" x-text="cart.length + ' item terdaftar'"></p>
                        </div>
                    </div>
                    <button x-show="cart.length > 0" 
                            @click="clearCart()" 
                            type="button" 
                            class="text-xs font-semibold text-rose-500 hover:text-rose-700 hover:underline">
                        Kosongkan
                    </button>
                </div>

                <!-- Cart Items List -->
                <div class="p-4 space-y-3 max-h-[300px] overflow-y-auto divide-y divide-slate-100">
                    <template x-for="(item, index) in cart" :key="item.product_id">
                        <div class="pt-2 first:pt-0 flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-slate-800 truncate" x-text="item.name"></h4>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                    <span class="font-mono text-emerald-600 font-semibold" x-text="formatRupiah(item.price)"></span>
                                    <span>/ <span x-text="item.unit"></span></span>
                                </div>
                            </div>

                            <!-- Qty Controller & Subtotal -->
                            <div class="flex flex-col items-end gap-1.5">
                                <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-slate-50">
                                    <button @click="updateQty(index, -1)" 
                                            type="button" 
                                            class="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition font-bold text-xs">-</button>
                                    <span class="w-8 text-center text-xs font-bold text-slate-800 font-mono" x-text="item.quantity"></span>
                                    <button @click="updateQty(index, 1)" 
                                            :disabled="item.quantity >= item.stock"
                                            :class="item.quantity >= item.stock ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-200'"
                                            type="button" 
                                            class="w-6 h-6 flex items-center justify-center text-slate-600 transition font-bold text-xs">+</button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black text-slate-900 font-mono" x-text="formatRupiah(item.subtotal)"></span>
                                    <button @click="removeFromCart(index)" type="button" class="text-slate-400 hover:text-rose-500 p-0.5" title="Hapus item">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Cart Empty State -->
                    <div x-show="cart.length === 0" class="py-10 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-xs font-medium">Keranjang masih kosong</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Klik barang di katalog atau scan barcode untuk menambah.</p>
                    </div>
                </div>

                <!-- Customer Selection & Payment Method -->
                <div class="p-4 bg-slate-50/70 border-t border-slate-200 space-y-3.5">
                    
                    <!-- Customer Selector -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-slate-700">Pelanggan Petani:</label>
                            <button @click="openNewCustomerModal()" type="button" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                + Petani Baru
                            </button>
                        </div>
                        <select x-model="customerId" class="w-full text-xs font-semibold py-2 px-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            <option value="">Umum / Pelanggan Anonim (Tunai Saja)</option>
                            <template x-for="cust in customers" :key="cust.id">
                                <option :value="cust.id" x-text="cust.name + (cust.phone ? ' (' + cust.phone + ')' : '')"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Payment Method Toggle -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran:</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="paymentMethod = 'cash'" 
                                    type="button" 
                                    :class="paymentMethod === 'cash' ? 'bg-emerald-600 text-white ring-2 ring-emerald-600/30' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100'"
                                    class="py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-2 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>TUNAI (CASH)</span>
                            </button>
                            <button @click="paymentMethod = 'credit'" 
                                    type="button" 
                                    :class="paymentMethod === 'credit' ? 'bg-amber-600 text-white ring-2 ring-amber-600/30' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100'"
                                    class="py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-2 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                <span>KREDIT (PIUTANG)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Cash Payment Calculator -->
                    <div x-show="paymentMethod === 'cash'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Uang Diterima (Rp):</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">Rp</span>
                            <input x-model="cashInput" 
                                   @input="formatCashInput()"
                                   type="text" 
                                   placeholder="0" 
                                   class="w-full pl-9 pr-3 py-2 text-base font-mono font-bold text-slate-900 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <!-- Quick Cash Shortcuts -->
                        <div class="grid grid-cols-4 gap-1.5 pt-1">
                            <button @click="setExactCash()" type="button" class="py-1 px-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-[10px] font-bold transition">Uang Pas</button>
                            <button @click="addCash(50000)" type="button" class="py-1 px-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-[10px] font-bold transition">50.000</button>
                            <button @click="addCash(100000)" type="button" class="py-1 px-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-[10px] font-bold transition">100.000</button>
                            <button @click="addCash(200000)" type="button" class="py-1 px-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-[10px] font-bold transition">200.000</button>
                        </div>

                        <!-- Change Display -->
                        <div class="p-2.5 rounded-xl border flex items-center justify-between"
                             :class="change >= 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-800'">
                            <span class="text-xs font-bold" x-text="change >= 0 ? 'Uang Kembalian:' : 'Uang Masih Kurang:'"></span>
                            <span class="text-sm font-black font-mono" x-text="formatRupiah(Math.abs(change))"></span>
                        </div>
                    </div>

                    <!-- Credit Payment Warning -->
                    <div x-show="paymentMethod === 'credit'" class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800 space-y-1">
                        <p class="font-bold flex items-center gap-1.5 text-amber-900">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pencatatan Piutang Petani
                        </p>
                        <p>Total transaksi ini (<span class="font-bold font-mono" x-text="formatRupiah(totalCart)"></span>) akan dicatat sebagai tagihan piutang belum lunas atas nama pelanggan yang dipilih.</p>
                    </div>

                </div>

                <!-- Cart Total Summary & Checkout Button -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 rounded-b-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pembayaran</span>
                        <span class="text-2xl font-black text-slate-900 font-mono tracking-tight" x-text="formatRupiah(totalCart)"></span>
                    </div>

                    <button @click="submitSale()" 
                            :disabled="cart.length === 0 || isSubmitting || (paymentMethod === 'cash' && change < 0)"
                            :class="{
                                'opacity-50 cursor-not-allowed bg-slate-300 text-slate-600': cart.length === 0 || isSubmitting || (paymentMethod === 'cash' && change < 0),
                                'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm active:scale-98': cart.length > 0 && !isSubmitting && (paymentMethod === 'credit' || change >= 0)
                            }"
                            type="button" 
                            class="w-full py-3.5 px-4 text-white text-sm font-extrabold rounded-xl transition duration-150 flex items-center justify-center gap-2">
                        <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <svg x-show="isSubmitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="isSubmitting ? 'Memproses Transaksi...' : 'Selesaikan Transaksi (F9)'"></span>
                    </button>
                </div>

            </div>

        </div>

        <!-- MODAL: Barcode Scanner Camera (html5-qrcode) -->
        <div x-show="scannerModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="closeScannerModal()" class="fixed inset-0 bg-slate-900/80 transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                    <div class="p-5 bg-white border-b border-slate-200 text-slate-900 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Scanner Barcode Kamera</h3>
                                <p class="text-[11px] text-slate-500">Arahkan kamera ke barcode kemasan produk</p>
                            </div>
                        </div>
                        <button @click="closeScannerModal()" type="button" class="text-slate-400 hover:text-slate-700 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-4 bg-slate-950">
                        <div id="reader" class="w-full rounded-2xl overflow-hidden bg-black text-white min-h-[260px] flex items-center justify-center"></div>
                        <p class="text-center text-xs text-slate-400 mt-3" x-text="scannerMessage || 'Mempersiapkan kamera...'"></p>
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                        <button @click="closeScannerModal()" type="button" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition">
                            Tutup Scanner
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: Quick Add Customer -->
        <div x-show="customerModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="customerModalOpen = false" class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900">Tambah Data Petani Cepat</h3>
                        <button @click="customerModalOpen = false" type="button" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveNewCustomer()" class="space-y-4 pt-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Petani / Pelanggan <span class="text-rose-500">*</span></label>
                            <input x-model="newCustomer.name" type="text" required placeholder="Contoh: Pak Sudirman" class="w-full text-xs py-2.5 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                            <input x-model="newCustomer.phone" type="text" placeholder="08xxxxxxxxxx" class="w-full text-xs py-2.5 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat / Dusun / RT</label>
                            <textarea x-model="newCustomer.address" rows="2" placeholder="Dusun Krajan RT 02/01" class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button @click="customerModalOpen = false" type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" :disabled="isSavingCustomer" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                                <span x-text="isSavingCustomer ? 'Menyimpan...' : 'Simpan & Pilih'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Post-Transaction Success & Receipt Print -->
        <div x-show="successModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/80 transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>

                    <h3 class="text-lg font-black text-slate-900">Transaksi Berhasil!</h3>
                    <p class="text-xs text-slate-500 mt-1">Nomor Nota: <span class="font-mono font-bold text-slate-800" x-text="lastSaleResult?.invoice_no"></span></p>

                    <div class="my-5 p-4 bg-slate-50 rounded-2xl border border-slate-200/80 text-left space-y-2">
                        <div class="flex justify-between text-xs text-slate-600">
                            <span>Total Tagihan:</span>
                            <span class="font-bold text-slate-900 font-mono" x-text="formatRupiah(lastSaleResult?.total || 0)"></span>
                        </div>
                        <template x-if="lastSaleResult?.cash_amount > 0">
                            <div class="flex justify-between text-xs text-slate-600">
                                <span>Uang Diterima:</span>
                                <span class="font-bold text-slate-900 font-mono" x-text="formatRupiah(lastSaleResult?.cash_amount || 0)"></span>
                            </div>
                        </template>
                        <template x-if="lastSaleResult?.cash_amount > 0">
                            <div class="flex justify-between text-xs text-emerald-700 font-bold border-t border-slate-200 pt-2">
                                <span>Kembalian:</span>
                                <span class="text-sm font-black font-mono" x-text="formatRupiah(lastSaleResult?.change || 0)"></span>
                            </div>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <a :href="lastSaleResult?.print_url" 
                               target="_blank" 
                               class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak Struk</span>
                            </a>
                            <a :href="'/sales/' + lastSaleResult?.sale_id + '/download-pdf'" 
                               class="w-full py-2.5 px-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Unduh PDF</span>
                            </a>
                        </div>
                        <button @click="resetForNewSale()" 
                                type="button" 
                                class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-lg shadow-emerald-600/20 transition">
                            + Transaksi Baru (Kasir Siap)
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function posKasir(config) {
            return {
                products: config.productsData || [],
                categories: config.categoriesData || [],
                customers: config.customersData || [],
                suggestedInvoiceNo: config.suggestedInvoiceNo || 'INV-...',
                storeUrl: config.storeUrl,
                csrfToken: config.csrfToken,

                searchQuery: '',
                selectedCategory: 'all',
                cart: [],
                customerId: '',
                paymentMethod: 'cash',
                cashInput: '',
                isSubmitting: false,

                // Scanner Modal
                scannerModalOpen: false,
                html5QrCode: null,
                scannerMessage: '',

                // Quick Customer Modal
                customerModalOpen: false,
                isSavingCustomer: false,
                newCustomer: { name: '', phone: '', address: '' },

                // Success Modal
                successModalOpen: false,
                lastSaleResult: null,

                get filteredProducts() {
                    let list = this.products;
                    if (this.selectedCategory !== 'all') {
                        list = list.filter(p => p.category_id == this.selectedCategory);
                    }
                    if (this.searchQuery.trim() !== '') {
                        const q = this.searchQuery.toLowerCase().trim();
                        list = list.filter(p => 
                            (p.name && p.name.toLowerCase().includes(q)) ||
                            (p.code && p.code.toLowerCase().includes(q)) ||
                            (p.barcode && p.barcode.toLowerCase().includes(q))
                        );
                    }
                    return list;
                },

                get totalCart() {
                    return this.cart.reduce((sum, item) => sum + item.subtotal, 0);
                },

                get numericCash() {
                    const clean = String(this.cashInput).replace(/[^0-9]/g, '');
                    return clean ? parseInt(clean, 10) : 0;
                },

                get change() {
                    return this.numericCash - this.totalCart;
                },

                focusSearch() {
                    this.$refs.searchInput?.focus();
                },

                handleSearchEnter() {
                    const filtered = this.filteredProducts;
                    if (filtered.length === 1) {
                        this.addToCart(filtered[0]);
                        this.searchQuery = '';
                    }
                },

                addToCart(product) {
                    if (product.stock <= 0) {
                        this.showNotification('error', 'Stok untuk barang ini sudah habis!');
                        return;
                    }

                    const existing = this.cart.find(i => i.product_id === product.id);
                    if (existing) {
                        if (existing.quantity >= product.stock) {
                            this.showNotification('warning', 'Kuantitas telah mencapai batas stok maksimal (' + product.stock + ' ' + product.unit + ')');
                            return;
                        }
                        existing.quantity++;
                        existing.subtotal = existing.quantity * existing.price;
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            code: product.code,
                            unit: product.unit,
                            price: parseFloat(product.selling_price),
                            stock: product.stock,
                            quantity: 1,
                            subtotal: parseFloat(product.selling_price)
                        });
                    }

                    this.playBeep(600, 70);
                },

                updateQty(index, delta) {
                    const item = this.cart[index];
                    if (!item) return;

                    const newQty = item.quantity + delta;
                    if (newQty <= 0) {
                        this.cart.splice(index, 1);
                        return;
                    }

                    if (newQty > item.stock) {
                        this.showNotification('warning', 'Stok maksimal yang tersedia: ' + item.stock + ' ' + item.unit);
                        return;
                    }

                    item.quantity = newQty;
                    item.subtotal = item.quantity * item.price;
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    if (confirm('Kosongkan semua barang dalam keranjang belanja?')) {
                        this.cart = [];
                        this.cashInput = '';
                    }
                },

                formatCashInput() {
                    const num = this.numericCash;
                    this.cashInput = num > 0 ? new Intl.NumberFormat('id-ID').format(num) : '';
                },

                setExactCash() {
                    this.cashInput = new Intl.NumberFormat('id-ID').format(this.totalCart);
                },

                addCash(amount) {
                    const current = this.numericCash;
                    this.cashInput = new Intl.NumberFormat('id-ID').format(current + amount);
                },

                openScannerModal() {
                    this.scannerModalOpen = true;
                    this.scannerMessage = 'Menghidupkan kamera...';

                    this.$nextTick(() => {
                        this.initHtml5QrCode();
                    });
                },

                closeScannerModal() {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().then(() => {
                            this.html5QrCode.clear();
                            this.html5QrCode = null;
                            this.scannerModalOpen = false;
                        }).catch(err => {
                            console.warn('Scanner stop error:', err);
                            this.scannerModalOpen = false;
                        });
                    } else {
                        this.scannerModalOpen = false;
                    }
                },

                initHtml5QrCode() {
                    if (!window.Html5Qrcode) {
                        this.scannerMessage = 'Library barcode scanner belum siap.';
                        return;
                    }

                    const html5QrCode = new window.Html5Qrcode("reader");
                    this.html5QrCode = html5QrCode;

                    const config = { fps: 10, qrbox: { width: 250, height: 180 } };

                    html5QrCode.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText) => {
                            this.onBarcodeScanned(decodedText);
                        },
                        (errorMessage) => {
                            // frame scan miss - no action needed
                        }
                    ).then(() => {
                        this.scannerMessage = 'Kamera aktif. Silakan dekatkan barcode produk.';
                    }).catch(err => {
                        console.error('Camera access error:', err);
                        this.scannerMessage = 'Gagal mengakses kamera. Pastikan izin kamera aktif (HTTPS / Localhost).';
                    });
                },

                onBarcodeScanned(code) {
                    this.playBeep(880, 120);
                    const cleanCode = code.trim();

                    const matched = this.products.find(p => 
                        (p.barcode && p.barcode === cleanCode) || 
                        (p.code && p.code.toLowerCase() === cleanCode.toLowerCase())
                    );

                    if (matched) {
                        this.addToCart(matched);
                        this.showNotification('success', 'Produk ' + matched.name + ' berhasil dimasukkan ke keranjang.');
                        this.closeScannerModal();
                    } else {
                        this.showNotification('warning', 'Barcode ' + cleanCode + ' tidak cocok dengan produk manapun.');
                    }
                },

                playBeep(frequency, duration) {
                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.type = 'sine';
                        osc.frequency.value = frequency || 750;
                        gain.gain.value = 0.1;
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.start();
                        setTimeout(() => {
                            osc.stop();
                            audioCtx.close();
                        }, duration || 80);
                    } catch (e) {
                        // audio not allowed
                    }
                },

                openNewCustomerModal() {
                    this.newCustomer = { name: '', phone: '', address: '' };
                    this.customerModalOpen = true;
                },

                async saveNewCustomer() {
                    if (!this.newCustomer.name) return;
                    this.isSavingCustomer = true;

                    try {
                        const response = await fetch('/customers', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify(this.newCustomer)
                        });

                        const data = await response.json();
                        if (response.ok && data.customer) {
                            this.customers.push(data.customer);
                            this.customerId = data.customer.id;
                            this.customerModalOpen = false;
                            this.showNotification('success', 'Petani ' + data.customer.name + ' berhasil didaftarkan.');
                        } else {
                            alert(data.message || 'Gagal menyimpan data pelanggan.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan koneksi server.');
                    } finally {
                        this.isSavingCustomer = false;
                    }
                },

                async submitSale() {
                    if (this.cart.length === 0) {
                        this.showNotification('error', 'Keranjang belanja masih kosong!');
                        return;
                    }

                    if (this.paymentMethod === 'credit' && !this.customerId) {
                        this.showNotification('error', 'Pilih pelanggan petani untuk transaksi kredit / piutang!');
                        return;
                    }

                    if (this.paymentMethod === 'cash' && this.change < 0) {
                        this.showNotification('error', 'Nominal uang tunai yang diterima kurang dari total belanja!');
                        return;
                    }

                    this.isSubmitting = true;

                    const payload = {
                        customer_id: this.customerId || null,
                        payment_method: this.paymentMethod,
                        cash_amount: this.paymentMethod === 'cash' ? this.numericCash : null,
                        items: this.cart.map(i => ({
                            product_id: i.product_id,
                            quantity: i.quantity
                        }))
                    };

                    try {
                        const response = await fetch(this.storeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.playBeep(900, 150);
                            this.lastSaleResult = data;
                            this.successModalOpen = true;
                            
                            // Update local product stocks
                            this.cart.forEach(cartItem => {
                                const prod = this.products.find(p => p.id === cartItem.product_id);
                                if (prod) {
                                    prod.stock -= cartItem.quantity;
                                }
                            });
                        } else {
                            const errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Gagal menyimpan transaksi.');
                            this.showNotification('error', errorMsg);
                        }
                    } catch (err) {
                        this.showNotification('error', 'Terjadi gangguan koneksi jaringan saat menyimpan transaksi.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                resetForNewSale() {
                    this.cart = [];
                    this.cashInput = '';
                    this.customerId = '';
                    this.paymentMethod = 'cash';
                    this.successModalOpen = false;
                    this.lastSaleResult = null;
                    this.focusSearch();
                },

                showNotification(type, message) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { type: type, message: message }
                    }));
                },

                formatRupiah(number) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(number || 0);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
