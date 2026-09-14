<x-app-layout>
    @section('page_title', 'Detail Faktur ' . $sale->invoice_no)

    <div class="space-y-6">
        
        <!-- Header Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900 font-mono">{{ $sale->invoice_no }}</h2>
                    <p class="text-xs text-slate-500">Waktu Transaksi: {{ $sale->sold_at->translatedFormat('d F Y - H:i:s') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sales.print', $sale) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Struk Thermal</span>
                </a>
                <a href="{{ route('sales.download-pdf', $sale) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh Faktur PDF</span>
                </a>
            </div>
        </div>

        <!-- Invoice Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
            
            <!-- Store & Invoice Header -->
            <div class="p-6 sm:p-8 bg-white border-b border-slate-200 text-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2.5 mb-1">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <h1 class="text-xl font-black tracking-tight text-slate-900">TOKO PERTANIAN AL BAROKAH</h1>
                    </div>
                    <p class="text-xs text-slate-500">Pusat Obat Pertanian, Pupuk, Benih & Alat Saprotan</p>
                    <p class="text-xs text-slate-500">Jl. Raya Pertanian No. 12, Desa Sumber Makmur</p>
                </div>

                <div class="sm:text-right space-y-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Faktur Penjualan</span>
                    <span class="text-2xl font-black text-slate-900 font-mono">{{ $sale->invoice_no }}</span>
                    <div class="pt-1">
                        @if($sale->payment_method === 'cash')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                LUNAS • TUNAI
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                KREDIT (PIUTANG PETANI)
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Meta Data: Customer & Cashier Info -->
            <div class="p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50/60 border-b border-slate-100 text-xs">
                <div>
                    <h3 class="font-bold text-slate-400 uppercase tracking-wider mb-2 text-[11px]">Informasi Pelanggan</h3>
                    @if($sale->customer)
                        <p class="text-sm font-bold text-slate-900">{{ $sale->customer->name }}</p>
                        <p class="text-slate-600 mt-0.5">No. HP / WA: {{ $sale->customer->phone ?? '-' }}</p>
                        <p class="text-slate-500 mt-0.5">Alamat: {{ $sale->customer->address ?? '-' }}</p>
                    @else
                        <p class="text-sm font-bold text-slate-700">Pelanggan Umum (Anonim)</p>
                        <p class="text-slate-400 mt-0.5">Penjualan langsung tunai di toko.</p>
                    @endif
                </div>

                <div class="sm:text-right space-y-1">
                    <h3 class="font-bold text-slate-400 uppercase tracking-wider mb-2 text-[11px]">Petugas Kasir</h3>
                    <p class="text-sm font-bold text-slate-900">{{ $sale->user->name ?? 'Admin' }}</p>
                    <p class="text-slate-500">Status Nota: <span class="font-semibold uppercase text-emerald-600 font-mono">{{ $sale->status }}</span></p>
                </div>
            </div>

            <!-- Purchased Items Table -->
            <div class="p-6 sm:p-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="pb-3 text-center w-12">No</th>
                                <th class="pb-3">Deskripsi Barang</th>
                                <th class="pb-3 text-right">Harga Satuan</th>
                                <th class="pb-3 text-center">Jumlah</th>
                                <th class="pb-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($sale->items as $index => $item)
                                <tr>
                                    <td class="py-3 text-center text-slate-400 font-mono">{{ $index + 1 }}</td>
                                    <td class="py-3">
                                        <p class="font-bold text-slate-900">{{ $item->product->name ?? 'Produk Dihapus' }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">
                                            SKU: {{ $item->product->code ?? '-' }} • Kategori: {{ $item->product->category->name ?? 'Umum' }}
                                        </p>
                                    </td>
                                    <td class="py-3 text-right font-mono text-slate-700">
                                        Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 text-center font-mono font-bold text-slate-800">
                                        {{ $item->quantity }} {{ $item->product->unit ?? 'pcs' }}
                                    </td>
                                    <td class="py-3 text-right font-mono font-black text-slate-900">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-200">
                                <td colspan="4" class="pt-4 text-right font-bold text-slate-600 uppercase">Subtotal Belanja:</td>
                                <td class="pt-4 text-right font-mono font-bold text-slate-800">
                                    Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="pt-2 text-right font-extrabold text-slate-900 text-sm uppercase">Total Pembayaran:</td>
                                <td class="pt-2 text-right font-mono font-black text-emerald-600 text-lg">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Credit / Receivable Section if Credit -->
            @if($sale->payment_method === 'credit' && $sale->receivable)
                <div class="p-6 sm:p-8 bg-amber-50/70 border-t border-amber-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-200 text-amber-800 flex items-center justify-center font-bold text-xs">!</span>
                            <h3 class="text-sm font-bold text-amber-900">Status Pencatatan Piutang Petani</h3>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase font-mono {{ $sale->receivable->status === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $sale->receivable->status === 'lunas' ? 'Sudah Lunas' : 'Belum Lunas' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-4 text-xs font-mono pt-2">
                        <div class="bg-white p-3 rounded-xl border border-amber-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-sans">Total Tagihan</span>
                            <span class="font-bold text-slate-900 text-sm">Rp {{ number_format($sale->receivable->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-amber-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-sans">Total Terbayar</span>
                            <span class="font-bold text-emerald-600 text-sm">Rp {{ number_format($sale->receivable->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-amber-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-sans">Sisa Saldo Piutang</span>
                            <span class="font-black text-rose-600 text-sm">Rp {{ number_format($sale->receivable->remaining_balance, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($sale->receivable->payments->count() > 0)
                        <div class="pt-2">
                            <h4 class="text-xs font-bold text-slate-700 mb-1.5">Riwayat Pembayaran Cicilan:</h4>
                            <div class="space-y-1 text-xs">
                                @foreach($sale->receivable->payments as $payment)
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-amber-100 font-mono">
                                        <span>{{ $payment->paid_at->format('d/m/Y H:i') }} ({{ $payment->notes ?: 'Pembayaran piutang' }})</span>
                                        <span class="font-bold text-emerald-600">+ Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Footer Note -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 text-center text-xs text-slate-400">
                Terima kasih atas kunjungan dan kepercayaan Anda di Toko Pertanian Al Barokah.
            </div>

        </div>

    </div>
</x-app-layout>
