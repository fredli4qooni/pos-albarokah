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
                    <span>Scan QR Code Kamera</span>
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
                               placeholder="Cari produk (Nama, SKU, QR Code)... [Tekan Enter]" 
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
                        <p class="text-[11px] text-slate-400 mt-0.5">Klik barang di katalog atau scan QR Code kemasan untuk menambah.</p>
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

        <!-- MODAL: Barcode Scanner Camera (html5-qrcode with 1D Barcode & 2D QR Optimization) -->
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
                    <div class="p-4 sm:p-5 bg-white border-b border-slate-200 text-slate-900 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Scanner QR Code Kamera</h3>
                                <p class="text-[11px] text-slate-500">Arahkan kamera ke QR Code kemasan produk</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <!-- Mirror / Flip Toggle Button (Front Cam / Laptop) -->
                            <button @click="toggleMirror()" 
                                    type="button" 
                                    :class="isMirrored ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 ring-1 ring-emerald-500/50' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700'"
                                    class="px-2.5 py-1.5 rounded-xl border text-xs font-semibold flex items-center gap-1.5 transition"
                                    :title="isMirrored ? 'Matikan Efek Cermin' : 'Aktifkan Efek Cermin (Kamera Depan Laptop)'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <span class="text-[11px]" x-text="isMirrored ? 'Cermin: ON' : 'Cermin: OFF'"></span>
                            </button>

                            <!-- Torch Button if supported -->
                            <button x-show="hasTorch" 
                                    @click="toggleTorch()" 
                                    type="button" 
                                    :class="isTorchOn ? 'bg-amber-100 text-amber-800 border-amber-300 ring-2 ring-amber-400' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700'"
                                    class="p-2 rounded-xl border text-xs font-bold flex items-center gap-1 transition"
                                    title="Nyalakan/Matikan Senter Flash">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </button>
                            <button @click="closeScannerModal()" type="button" class="text-slate-400 hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-800 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Viewfinder Container with Square QR Reticle -->
                    <div class="p-4 bg-slate-950">
                        <div class="relative w-full rounded-2xl overflow-hidden bg-black text-white min-h-[300px] sm:min-h-[360px] flex items-center justify-center border border-slate-800">
                            <!-- High-Performance Direct Video Stream with Mirror Support -->
                            <video x-ref="videoElement" 
                                   class="w-full h-full object-cover min-h-[300px] sm:min-h-[360px] transition-transform duration-200" 
                                   :class="isMirrored ? '-scale-x-100' : ''"
                                   playsinline 
                                   autoplay 
                                   muted>
                            </video>
                            
                            <!-- Offscreen Canvas for jsQR Real-Time Processing -->
                            <canvas x-ref="scanCanvas" class="hidden"></canvas>
                            
                            <!-- Hidden container for Html5Qrcode fallback -->
                            <div id="reader" class="hidden w-full"></div>
                            
                            <!-- Square QR Aiming Reticle (4 corner brackets) -->
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <div class="relative w-52 h-52 sm:w-60 sm:h-60 transition-all duration-200">
                                    <!-- Top-Left Corner -->
                                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 rounded-tl-xl transition-all duration-200"
                                         :class="scanFeedback ? (scanFeedback.success ? 'border-emerald-400 shadow-[0_0_15px_rgba(52,211,153,1)]' : 'border-rose-400 shadow-[0_0_15px_rgba(251,113,133,1)]') : 'border-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]'"></div>
                                    <!-- Top-Right Corner -->
                                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 rounded-tr-xl transition-all duration-200"
                                         :class="scanFeedback ? (scanFeedback.success ? 'border-emerald-400 shadow-[0_0_15px_rgba(52,211,153,1)]' : 'border-rose-400 shadow-[0_0_15px_rgba(251,113,133,1)]') : 'border-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]'"></div>
                                    <!-- Bottom-Left Corner -->
                                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 rounded-bl-xl transition-all duration-200"
                                         :class="scanFeedback ? (scanFeedback.success ? 'border-emerald-400 shadow-[0_0_15px_rgba(52,211,153,1)]' : 'border-rose-400 shadow-[0_0_15px_rgba(251,113,133,1)]') : 'border-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]'"></div>
                                    <!-- Bottom-Right Corner -->
                                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 rounded-br-xl transition-all duration-200"
                                         :class="scanFeedback ? (scanFeedback.success ? 'border-emerald-400 shadow-[0_0_15px_rgba(52,211,153,1)]' : 'border-rose-400 shadow-[0_0_15px_rgba(251,113,133,1)]') : 'border-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]'"></div>
                                    
                                    <!-- Center Scanning Line Indicator -->
                                    <div x-show="!scanFeedback" class="absolute inset-x-2 top-1/2 -translate-y-1/2 h-0.5 bg-emerald-400 shadow-[0_0_10px_2px_rgba(52,211,153,0.9)] animate-pulse"></div>

                                    <!-- Scanned Success Checkmark Overlay -->
                                    <div x-show="scanFeedback && scanFeedback.success" class="absolute inset-0 flex items-center justify-center bg-emerald-950/40 rounded-xl backdrop-blur-xs">
                                        <div class="w-14 h-14 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scan Feedback Banner -->
                        <div x-show="scanFeedback" 
                             x-cloak
                             class="mt-2.5 p-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition"
                             :class="scanFeedback?.success ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40'">
                            <span x-text="scanFeedback?.text"></span>
                        </div>

                        <!-- Camera Switcher & File Upload Alternative -->
                        <div class="mt-3 flex items-center justify-between text-xs text-slate-400 gap-2">
                            <div class="flex items-center gap-1.5 overflow-hidden">
                                <span class="text-[11px] whitespace-nowrap">Lensa:</span>
                                <select x-show="availableCameras.length > 0" 
                                        @change="switchCamera($event.target.value)" 
                                        class="bg-slate-900 border border-slate-800 text-slate-300 text-[11px] rounded-lg px-2 py-1 max-w-[150px] truncate focus:ring-1 focus:ring-emerald-500">
                                    <template x-for="cam in availableCameras" :key="cam.id">
                                        <option :value="cam.id" :selected="cam.id === selectedCameraId" x-text="cam.label || 'Kamera ' + cam.id.substring(0,6)"></option>
                                    </template>
                                </select>
                                <span x-show="availableCameras.length === 0" class="text-[11px] text-slate-500 italic">Kamera Default</span>
                            </div>

                            <!-- Scan File Upload Button -->
                            <div>
                                <label class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg border border-slate-700 text-[11px] font-medium transition" title="Pilih berkas foto/gambar QR Code jika tidak menggunakan kamera live">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Scan Berkas Foto QR</span>
                                    <input type="file" accept="image/*" class="hidden" @change="scanQrFromFile($event)">
                                </label>
                            </div>
                        </div>

                        <p class="text-center text-xs text-slate-300 mt-2 font-medium" x-text="scannerMessage || 'Mempersiapkan kamera...'"></p>

                        <!-- Scanning Tips Guide -->
                        <div class="mt-2.5 p-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-[11px] text-slate-400 space-y-1">
                            <p class="text-slate-200 font-bold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Petunjuk & Dukungan Kamera Depan (Laptop):</span>
                            </p>
                            <p class="leading-relaxed">• Sistem dilengkapi <strong>Auto-Detect Dual Orientation</strong> — otomatis mendeteksi QR Code posisi normal maupun terbalik (efek mirror webcam laptop).</p>
                            <p class="leading-relaxed">• Tekan tombol <strong>Cermin</strong> di kanan atas jika ingin membalik pratinjau kamera agar gerakan tangan terasa natural seperti cermin.</p>
                            <p class="leading-relaxed">• Anda juga bisa memindai dengan <strong>Barcode Scanner Gun (USB / Nirkabel)</strong> langsung di kasir tanpa membuka popup ini.</p>
                        </div>
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

                // Real-time Fast Camera Scanner State
                scannerModalOpen: false,
                mediaStream: null,
                animFrameId: null,
                isScanningActive: false,
                hardwareDetector: null,
                html5QrCode: null,
                scannerMessage: '',
                scanFeedback: null,
                hasTorch: false,
                isTorchOn: false,
                isMirrored: false,
                availableCameras: [],
                selectedCameraId: null,

                // Quick Customer Modal
                customerModalOpen: false,
                isSavingCustomer: false,
                newCustomer: { name: '', phone: '', address: '' },

                // Success Modal
                successModalOpen: false,
                lastSaleResult: null,

                init() {
                    // Initialize Native Hardware BarcodeDetector if supported by OS/browser (Google Lens on Android, Vision on iOS)
                    if ('BarcodeDetector' in window) {
                        try {
                            this.hardwareDetector = new window.BarcodeDetector({ formats: ['qr_code'] });
                        } catch (e) {
                            this.hardwareDetector = null;
                        }
                    }

                    // Global physical barcode scanner listener (HID keyboard mode)
                    let barcodeBuffer = '';
                    let lastKeyTime = Date.now();

                    window.addEventListener('keydown', (e) => {
                        const activeEl = document.activeElement;
                        const isOtherInput = activeEl && (
                            (activeEl.tagName === 'INPUT' && activeEl !== this.$refs.searchInput) || 
                            activeEl.tagName === 'TEXTAREA' || 
                            activeEl.tagName === 'SELECT'
                        );

                        // Don't intercept if user is typing into modal inputs or other form fields
                        if (isOtherInput || this.customerModalOpen || this.successModalOpen) {
                            return;
                        }

                        const currentTime = Date.now();
                        // Physical scanners emit keypresses within 20-60ms intervals
                        if (currentTime - lastKeyTime > 100) {
                            barcodeBuffer = '';
                        }
                        lastKeyTime = currentTime;

                        if (e.key === 'Enter') {
                            const code = barcodeBuffer.trim();
                            if (code.length >= 2) {
                                e.preventDefault();
                                this.onBarcodeScanned(code);
                                barcodeBuffer = '';
                                if (this.$refs.searchInput) {
                                    this.searchQuery = '';
                                }
                            }
                        } else if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                            barcodeBuffer += e.key;
                        }
                    });
                },

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
                    const q = this.searchQuery.trim();
                    if (!q) return;

                    // 1. Direct match on barcode or code across all products
                    const exactMatch = this.products.find(p => 
                        (p.barcode && String(p.barcode).trim().toLowerCase() === q.toLowerCase()) || 
                        (p.code && String(p.code).trim().toLowerCase() === q.toLowerCase())
                    );

                    if (exactMatch) {
                        this.addToCart(exactMatch);
                        this.showNotification('success', '✓ ' + exactMatch.name + ' berhasil ditambahkan.');
                        this.searchQuery = '';
                        return;
                    }

                    // 2. Filtered list match
                    const filtered = this.filteredProducts;
                    if (filtered.length === 1) {
                        this.addToCart(filtered[0]);
                        this.showNotification('success', '✓ ' + filtered[0].name + ' berhasil ditambahkan.');
                        this.searchQuery = '';
                    } else if (filtered.length > 1) {
                        this.showNotification('info', 'Ditemukan ' + filtered.length + ' produk. Klik salah satu untuk memilih.');
                    } else {
                        this.showNotification('warning', 'Produk dengan kode/nama "' + q + '" tidak ditemukan.');
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
                    this.scannerMessage = 'Mengaktifkan sensor kamera...';
                    this.scanFeedback = null;
                    this.isTorchOn = false;

                    this.$nextTick(() => {
                        this.startFastScanner();
                    });
                },

                closeScannerModal() {
                    this.stopCameraStream();
                    this.isTorchOn = false;
                    this.scanFeedback = null;
                    this.scannerModalOpen = false;
                },

                toggleMirror() {
                    this.isMirrored = !this.isMirrored;
                    this.showNotification('info', this.isMirrored ? 'Mode Cermin (Mirror) Diaktifkan' : 'Mode Cermin (Mirror) Dinonaktifkan');
                },

                stopCameraStream() {
                    this.isScanningActive = false;
                    if (this.animFrameId) {
                        cancelAnimationFrame(this.animFrameId);
                        this.animFrameId = null;
                    }
                    if (this.mediaStream) {
                        this.mediaStream.getTracks().forEach(track => {
                            try { track.stop(); } catch(e) {}
                        });
                        this.mediaStream = null;
                    }
                    if (this.$refs.videoElement) {
                        this.$refs.videoElement.srcObject = null;
                    }
                },

                async startFastScanner() {
                    this.stopCameraStream();

                    // 1. Enumerate video devices
                    try {
                        const devices = await navigator.mediaDevices.enumerateDevices();
                        this.availableCameras = devices
                            .filter(d => d.kind === 'videoinput')
                            .map(d => ({ id: d.deviceId, label: d.label || 'Kamera ' + d.deviceId.substring(0, 6) }));
                    } catch (e) {
                        this.availableCameras = [];
                    }

                    // 2. Determine target camera constraints (ideal 1280x720 for crisp QR edge detection)
                    let videoConstraints = {
                        width: { ideal: 1280, min: 640 },
                        height: { ideal: 720, min: 480 }
                    };

                    if (this.selectedCameraId && this.availableCameras.some(c => c.id === this.selectedCameraId)) {
                        videoConstraints.deviceId = { exact: this.selectedCameraId };
                    } else if (this.availableCameras.length > 0) {
                        const backCam = this.availableCameras.find(c => /back|rear|environment|belakang/i.test(c.label));
                        const chosen = backCam || this.availableCameras[0];
                        this.selectedCameraId = chosen.id;
                        videoConstraints.deviceId = { exact: chosen.id };
                    } else {
                        videoConstraints.facingMode = { ideal: "environment" };
                    }

                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: videoConstraints,
                            audio: false
                        });
                        this.mediaStream = stream;

                        const video = this.$refs.videoElement;
                        if (!video) return;

                        video.srcObject = stream;
                        video.setAttribute("playsinline", "true");
                        await video.play();

                        this.scannerMessage = 'Arahkan kamera ke QR Code (Deteksi Instan)';
                        this.isScanningActive = true;

                        // Check torch support, continuous autofocus, & front camera detection
                        const track = stream.getVideoTracks()[0];
                        if (track) {
                            try {
                                const capabilities = track.getCapabilities ? track.getCapabilities() : {};
                                const settings = track.getSettings ? track.getSettings() : {};
                                this.hasTorch = Boolean(capabilities && capabilities.torch);
                                if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
                                    await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
                                }
                                const isUserFacing = settings.facingMode === 'user' || /front|user|depan|integrated|face/i.test(track.label || '');
                                if (isUserFacing && !this.selectedCameraId) {
                                    this.isMirrored = true;
                                }
                            } catch(e) {
                                this.hasTorch = false;
                            }
                        }

                        // Run real-time high-speed frame loop
                        this.runRealtimeScanLoop();

                    } catch (err) {
                        console.error('Fast scanner start error:', err);
                        this.scannerMessage = 'Gagal mengakses kamera. Berikan izin kamera di peramban Anda.';
                        this.showNotification('error', 'Izin kamera ditolak atau perangkat kamera tidak terdeteksi.');
                    }
                },

                runRealtimeScanLoop() {
                    if (!this.scannerModalOpen || !this.isScanningActive) return;

                    const video = this.$refs.videoElement;
                    const canvas = this.$refs.scanCanvas;

                    const checkFrame = async () => {
                        if (!this.scannerModalOpen || !this.isScanningActive) return;

                        if (video && video.readyState >= 2 && !this.scanFeedback && video.videoWidth > 0) {
                            let foundText = null;

                            // Engine 1: Native Hardware BarcodeDetector (Google Lens Engine on Android / Vision on iOS)
                            if (this.hardwareDetector) {
                                try {
                                    const codes = await this.hardwareDetector.detect(video);
                                    if (codes && codes.length > 0 && codes[0].rawValue) {
                                        foundText = codes[0].rawValue;
                                    }
                                } catch(e) {}
                            }

                            // Engine 2: Ultra-Fast Canvas jsQR with Dual-Orientation (Normal + Mirrored for laptop webcams)
                            if (!foundText && canvas) {
                                try {
                                    const ctx = canvas.getContext('2d', { willReadFrequently: true });
                                    // Scale to max 640px for ideal balance of crisp edges & sub-10ms performance
                                    const maxDim = Math.max(video.videoWidth, video.videoHeight);
                                    const scale = maxDim > 640 ? (640 / maxDim) : 1.0;
                                    const w = Math.round(video.videoWidth * scale);
                                    const h = Math.round(video.videoHeight * scale);

                                    if (canvas.width !== w || canvas.height !== h) {
                                        canvas.width = w;
                                        canvas.height = h;
                                    }

                                    // Orientation scanner helper (flipHorizontal = true mirrors the image horizontally)
                                    const scanOrientation = (flipHorizontal) => {
                                        ctx.save();
                                        ctx.clearRect(0, 0, w, h);
                                        if (flipHorizontal) {
                                            ctx.translate(w, 0);
                                            ctx.scale(-1, 1);
                                        }
                                        ctx.drawImage(video, 0, 0, w, h);
                                        ctx.restore();

                                        if (window.jsQR) {
                                            const imgData = ctx.getImageData(0, 0, w, h);
                                            const qr = window.jsQR(imgData.data, w, h, { inversionAttempts: "dontInvert" });
                                            if (qr && qr.data) {
                                                return qr.data;
                                            }
                                        }
                                        return null;
                                    };

                                    // Pass 1: Try current preview orientation (mirrored first if isMirrored is ON, else normal)
                                    foundText = scanOrientation(this.isMirrored);

                                    // Pass 2: If not found, immediately try opposite orientation (catches mirrored laptop webcam feed)
                                    if (!foundText) {
                                        foundText = scanOrientation(!this.isMirrored);

                                        // Pass 3: Test hardware detector on flipped canvas if available
                                        if (!foundText && this.hardwareDetector) {
                                            try {
                                                const codes = await this.hardwareDetector.detect(canvas);
                                                if (codes && codes.length > 0 && codes[0].rawValue) {
                                                    foundText = codes[0].rawValue;
                                                }
                                            } catch(e) {}
                                        }
                                    }
                                } catch(e) {}
                            }

                            if (foundText) {
                                this.isScanningActive = false;
                                this.onBarcodeScanned(foundText);
                                return;
                            }
                        }

                        this.animFrameId = requestAnimationFrame(checkFrame);
                    };

                    this.animFrameId = requestAnimationFrame(checkFrame);
                },

                async scanQrFromFile(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file) return;

                    this.scannerMessage = 'Membaca QR Code dari berkas gambar...';

                    // 1. Safely stop active camera scan if running
                    this.stopCameraStream();

                    let decodedCode = null;

                    // 2. Engine 1: Client-side Canvas Image + jsQR (multi-scale downsampling for high-res photos)
                    try {
                        const img = new Image();
                        const objectUrl = URL.createObjectURL(file);
                        await new Promise((resolve, reject) => {
                            img.onload = resolve;
                            img.onerror = reject;
                            img.src = objectUrl;
                        });

                        if (window.jsQR) {
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d', { willReadFrequently: true });
                            
                            // Multi-scale passes: original size, 1000px, 600px
                            const scales = [1.0];
                            const maxDim = Math.max(img.width, img.height);
                            if (maxDim > 1000) scales.push(1000 / maxDim);
                            if (maxDim > 600) scales.push(600 / maxDim);

                            for (const scale of scales) {
                                const targetW = Math.round(img.width * scale);
                                const targetH = Math.round(img.height * scale);
                                canvas.width = targetW;
                                canvas.height = targetH;

                                // Pass 1: Normal orientation
                                ctx.clearRect(0, 0, targetW, targetH);
                                ctx.drawImage(img, 0, 0, targetW, targetH);
                                let imgData = ctx.getImageData(0, 0, targetW, targetH);
                                let result = window.jsQR(imgData.data, targetW, targetH, {
                                    inversionAttempts: "attemptBoth"
                                });
                                if (result && result.data) {
                                    decodedCode = result.data;
                                    break;
                                }

                                // Pass 2: Horizontally flipped / mirrored orientation (catches selfie/webcam photos)
                                ctx.save();
                                ctx.clearRect(0, 0, targetW, targetH);
                                ctx.translate(targetW, 0);
                                ctx.scale(-1, 1);
                                ctx.drawImage(img, 0, 0, targetW, targetH);
                                ctx.restore();
                                imgData = ctx.getImageData(0, 0, targetW, targetH);
                                result = window.jsQR(imgData.data, targetW, targetH, {
                                    inversionAttempts: "attemptBoth"
                                });
                                if (result && result.data) {
                                    decodedCode = result.data;
                                    break;
                                }
                            }
                        }
                        URL.revokeObjectURL(objectUrl);
                    } catch (err) {
                        console.warn('jsQR scan attempt failed:', err);
                    }

                    // 3. Engine 2: Html5Qrcode.scanFile fallback if jsQR did not detect
                    if (!decodedCode) {
                        try {
                            if (!this.html5QrCode) {
                                this.html5QrCode = new window.Html5Qrcode("reader", { verbose: false });
                            }
                            const text = await this.html5QrCode.scanFile(file, true);
                            if (text) {
                                decodedCode = text;
                            }
                        } catch (err) {
                            console.warn('Html5Qrcode file scan fallback error:', err);
                        }
                    }

                    // 4. Handle Result
                    if (decodedCode) {
                        this.onBarcodeScanned(decodedCode);
                    } else {
                        this.scannerMessage = 'QR Code tidak terdeteksi pada gambar.';
                        this.showNotification('warning', 'QR Code tidak terdeteksi pada berkas gambar yang dipilih.');
                    }

                    event.target.value = '';
                },

                async toggleTorch() {
                    if (!this.mediaStream || !this.hasTorch) return;
                    this.isTorchOn = !this.isTorchOn;
                    const track = this.mediaStream.getVideoTracks()[0];
                    if (track) {
                        try {
                            await track.applyConstraints({
                                advanced: [{ torch: this.isTorchOn }]
                            });
                        } catch(err) {
                            console.warn('Torch toggle error:', err);
                        }
                    }
                },

                async switchCamera(deviceId) {
                    if (!deviceId) return;
                    this.selectedCameraId = deviceId;
                    await this.startFastScanner();
                },

                onBarcodeScanned(code) {
                    let raw = code ? String(code).trim() : '';
                    if (!raw) return;

                    this.playBeep(880, 150);

                    // 1. Direct match on barcode or code
                    let matched = this.products.find(p => 
                        (p.barcode && String(p.barcode).trim().toLowerCase() === raw.toLowerCase()) || 
                        (p.code && String(p.code).trim().toLowerCase() === raw.toLowerCase())
                    );

                    // 2. Fallback: try stripping URL prefixes or query parameters
                    if (!matched && (raw.includes('/') || raw.includes('='))) {
                        const parts = raw.split(/[\/=]/);
                        const candidate = parts[parts.length - 1].trim();
                        if (candidate) {
                            matched = this.products.find(p => 
                                (p.barcode && String(p.barcode).trim().toLowerCase() === candidate.toLowerCase()) || 
                                (p.code && String(p.code).trim().toLowerCase() === candidate.toLowerCase())
                            );
                        }
                    }

                    if (matched) {
                        this.scanFeedback = { success: true, text: '✓ Ditemukan: ' + matched.name + ' (Rp ' + this.formatRupiah(matched.selling_price) + ')' };
                        this.addToCart(matched);
                        this.showNotification('success', '✓ ' + matched.name + ' berhasil dimasukkan ke keranjang.');
                        
                        // If camera modal is open, brief delay so user sees green feedback
                        if (this.scannerModalOpen) {
                            setTimeout(() => {
                                this.closeScannerModal();
                            }, 500);
                        }
                    } else {
                        this.scanFeedback = { success: false, text: '⚠️ Kode "' + raw + '" tidak terdaftar di katalog produk!' };
                        this.showNotification('warning', '⚠️ Kode QR "' + raw + '" tidak cocok dengan produk manapun.');
                        if (this.scannerModalOpen) {
                            setTimeout(() => {
                                this.scanFeedback = null;
                            }, 3500);
                        }
                    }
                },

                playBeep(frequency, duration) {
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        if (!AudioContext) return;
                        const audioCtx = new AudioContext();
                        if (audioCtx.state === 'suspended') {
                            audioCtx.resume();
                        }
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(frequency || 750, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + ((duration || 100) / 1000));
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.start();
                        osc.stop(audioCtx.currentTime + ((duration || 100) / 1000));
                    } catch (e) {
                        // audio playback not allowed
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
