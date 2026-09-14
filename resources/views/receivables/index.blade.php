<x-app-layout>
    @section('page_title', 'Pengelolaan Piutang Petani')

    <div x-data="{
            paymentModalOpen: false,
            activeReceivable: null,
            paymentAmount: '',
            paymentDate: '{{ now()->format('Y-m-d\TH:i') }}',
            paymentNotes: '',
            isSubmitting: false,

            openPaymentModal(item) {
                this.activeReceivable = item;
                this.paymentAmount = item.remaining_balance;
                this.paymentDate = new Date().toISOString().slice(0, 16);
                this.paymentNotes = '';
                this.paymentModalOpen = true;
            },

            setFullPayment() {
                if (this.activeReceivable) {
                    this.paymentAmount = this.activeReceivable.remaining_balance;
                }
            },

            formatRupiah(number) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(number || 0);
            }
         }" 
         class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black tracking-tight text-slate-900">Pengelolaan Piutang Petani</h2>
                <p class="text-xs text-slate-500">Monitoring saldo hak tagih penjualan saprotan kredit dan riwayat pembayaran cicilan</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Penjualan Kredit Baru</span>
                </a>
            </div>
        </div>

        <!-- KPI Metrics -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Piutang Belum Lunas -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Sisa Piutang Aktif</span>
                    <span class="text-lg font-black text-rose-600 font-mono">Rp {{ number_format($stats['total_active_debt'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Total Terbayar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Terbayar</span>
                    <span class="text-lg font-black text-emerald-600 font-mono">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Faktur Belum Lunas -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Faktur Belum Lunas</span>
                    <span class="text-lg font-black text-amber-600 font-mono">{{ number_format($stats['unpaid_count'], 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">pelanggan</span>
                </div>
            </div>

            <!-- Faktur Lunas -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Faktur Lunas</span>
                    <span class="text-lg font-black text-teal-600 font-mono">{{ number_format($stats['paid_count'], 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">selesai</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <form method="GET" action="{{ route('receivables.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Piutang</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="No. Invoice atau nama petani..." 
                               class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Pelunasan</label>
                    <select name="status" class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Status</option>
                        <option value="belum_lunas" {{ request('status') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas (Aktif)</option>
                        <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>Sudah Lunas</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition">
                        Terapkan Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'customer_id']))
                        <a href="{{ route('receivables.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Status Pills Quick Filter -->
            <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Pilih Cepat:</span>
                <a href="{{ route('receivables.index') }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua
                </a>
                <a href="{{ route('receivables.index', ['status' => 'belum_lunas']) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ request('status') === 'belum_lunas' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Belum Lunas
                </a>
                <a href="{{ route('receivables.index', ['status' => 'lunas']) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ request('status') === 'lunas' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Sudah Lunas
                </a>
            </div>
        </div>

        <!-- Receivables Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">No. Invoice & Tanggal</th>
                            <th class="py-3.5 px-4">Petani / Pelanggan</th>
                            <th class="py-3.5 px-4 text-right">Total Tagihan</th>
                            <th class="py-3.5 px-4 text-right">Terbayar</th>
                            <th class="py-3.5 px-4 text-right">Sisa Tagihan</th>
                            <th class="py-3.5 px-4 text-center">Pelunasan</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($receivables as $item)
                            @php
                                $percentage = (float) $item->total_amount > 0 
                                    ? min(100, round(((float) $item->paid_amount / (float) $item->total_amount) * 100)) 
                                    : 100;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Invoice -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('sales.show', $item->sale) }}" class="font-mono font-bold text-slate-900 hover:text-emerald-600">
                                        {{ $item->sale->invoice_no ?? '-' }}
                                    </a>
                                    <span class="block text-[10px] text-slate-400 font-mono">
                                        {{ $item->sale->sold_at ? $item->sale->sold_at->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </td>

                                <!-- Customer -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('customers.show', $item->customer) }}" class="font-bold text-slate-800 hover:text-emerald-600 block">
                                        {{ $item->customer->name ?? '-' }}
                                    </a>
                                    @if($item->customer && $item->customer->phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->customer->phone) }}" target="_blank" class="text-[10px] text-emerald-600 hover:underline font-mono inline-flex items-center gap-1">
                                            <span>WA: {{ $item->customer->phone }}</span>
                                        </a>
                                    @endif
                                </td>

                                <!-- Total Amount -->
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-800">
                                    Rp {{ number_format($item->total_amount, 0, ',', '.') }}
                                </td>

                                <!-- Paid Amount -->
                                <td class="py-3.5 px-4 text-right font-mono font-semibold text-emerald-600">
                                    Rp {{ number_format($item->paid_amount, 0, ',', '.') }}
                                </td>

                                <!-- Remaining Balance -->
                                <td class="py-3.5 px-4 text-right font-mono font-black text-sm {{ $item->status === 'lunas' ? 'text-slate-400' : 'text-rose-600' }}">
                                    Rp {{ number_format($item->remaining_balance, 0, ',', '.') }}
                                </td>

                                <!-- Progress Bar -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="w-24 mx-auto">
                                        <div class="flex items-center justify-between text-[10px] font-mono text-slate-500 mb-0.5">
                                            <span>{{ $percentage }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ $percentage >= 100 ? 'bg-emerald-500' : 'bg-amber-500' }}" 
                                                 style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($item->status === 'lunas')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            LUNAS
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                            BELUM LUNAS
                                        </span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($item->status === 'belum_lunas')
                                            <button @click="openPaymentModal({
                                                        id: {{ $item->id }},
                                                        invoice_no: '{{ $item->sale->invoice_no ?? '-' }}',
                                                        customer_name: '{{ addslashes($item->customer->name ?? '-') }}',
                                                        total_amount: {{ (float) $item->total_amount }},
                                                        paid_amount: {{ (float) $item->paid_amount }},
                                                        remaining_balance: {{ (float) $item->remaining_balance }},
                                                        action_url: '{{ route('receivables.payments.store', $item) }}'
                                                    })"
                                                    type="button" 
                                                    class="px-2.5 py-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-lg text-[11px] shadow-sm transition">
                                                Bayar Cicilan
                                            </button>
                                        @endif
                                        <a href="{{ route('receivables.show', $item) }}" 
                                           class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" 
                                           title="Lihat Rincian & Riwayat Pembayaran">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-600">Tidak ada data piutang yang cocok</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Seluruh piutang sudah lunas atau sesuaikan filter Anda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($receivables->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $receivables->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL: Pencatatan Pembayaran Piutang (Alpine.js) -->
        <div x-show="paymentModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="paymentModalOpen = false" class="fixed inset-0 bg-slate-900/70 transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                    
                    <div class="p-5 bg-slate-900 text-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                                Rp
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Catat Pembayaran Piutang</h3>
                                <p class="text-[11px] text-slate-400" x-text="'Nota: ' + activeReceivable?.invoice_no"></p>
                            </div>
                        </div>
                        <button @click="paymentModalOpen = false" type="button" class="text-slate-400 hover:text-white p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="activeReceivable?.action_url" method="POST" class="p-6 space-y-4">
                        @csrf
                        
                        <!-- Info Ringkas Tagihan -->
                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Nama Petani:</span>
                                <span class="font-bold text-slate-800" x-text="activeReceivable?.customer_name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Total Tagihan Awal:</span>
                                <span class="font-mono text-slate-700" x-text="formatRupiah(activeReceivable?.total_amount)"></span>
                            </div>
                            <div class="flex justify-between border-t border-slate-200 pt-1.5">
                                <span class="font-bold text-rose-600">Sisa Saldo Tagihan:</span>
                                <span class="font-black text-rose-600 font-mono text-sm" x-text="formatRupiah(activeReceivable?.remaining_balance)"></span>
                            </div>
                        </div>

                        <!-- Nominal Pembayaran -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700">Nominal Pembayaran (Rp) <span class="text-rose-500">*</span></label>
                                <button @click="setFullPayment()" type="button" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                    Lunasi Penuh (Uang Pas)
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">Rp</span>
                                <input x-model="paymentAmount" 
                                       type="number" 
                                       name="amount" 
                                       :max="activeReceivable?.remaining_balance"
                                       min="1" 
                                       step="any" 
                                       required 
                                       placeholder="0" 
                                       class="w-full pl-9 pr-3 py-2 text-base font-mono font-bold text-slate-900 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Nominal tidak boleh melebihi sisa saldo piutang.</p>
                        </div>

                        <!-- Tanggal Pembayaran -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Waktu Pembayaran <span class="text-rose-500">*</span></label>
                            <input x-model="paymentDate" 
                                   type="datetime-local" 
                                   name="paid_at" 
                                   required 
                                   class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Catatan</label>
                            <input x-model="paymentNotes" 
                                   type="text" 
                                   name="notes" 
                                   placeholder="Contoh: Titipan cicilan hasil panen cabai" 
                                   class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <!-- Actions -->
                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button @click="paymentModalOpen = false" type="button" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" 
                                    :disabled="isSubmitting || !paymentAmount || paymentAmount <= 0 || paymentAmount > activeReceivable?.remaining_balance"
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Simpan Pembayaran
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
