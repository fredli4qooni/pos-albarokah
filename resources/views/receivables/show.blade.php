<x-app-layout>
    @section('page_title', 'Detail Piutang - ' . ($receivable->customer->name ?? 'Petani'))

    <div class="space-y-6">
        
        <!-- Header Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('receivables.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Kartu Piutang Petani</h2>
                    <p class="text-xs text-slate-500">Nomor Nota Asal: <a href="{{ route('sales.show', $receivable->sale) }}" class="font-mono font-bold text-emerald-600 hover:underline">{{ $receivable->sale->invoice_no ?? '-' }}</a></p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sales.show', $receivable->sale) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Buka Faktur Asal</span>
                </a>
            </div>
        </div>

        <!-- Customer & Receivable Summary Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
            
            <!-- Banner Header -->
            <div class="p-6 sm:p-8 bg-white border-b border-slate-200 text-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <div>
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Pelanggan Petani</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $receivable->customer->name ?? '-' }}</h1>
                    <div class="mt-2 space-y-0.5 text-xs text-slate-600">
                        @if($receivable->customer && $receivable->customer->phone)
                            <p>No. HP / WhatsApp: <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $receivable->customer->phone) }}" target="_blank" class="text-emerald-700 hover:text-emerald-800 underline font-mono font-bold">{{ $receivable->customer->phone }}</a></p>
                        @endif
                        <p class="text-slate-500">Alamat: {{ $receivable->customer->address ?? '-' }}</p>
                    </div>
                </div>

                <div class="sm:text-right space-y-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Status Pelunasan</span>
                    @if($receivable->status === 'lunas')
                        <span class="inline-block px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            SUDAH LUNAS
                        </span>
                    @else
                        <span class="inline-block px-4 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            BELUM LUNAS
                        </span>
                    @endif
                    <p class="text-[11px] text-slate-500 mt-1">Tanggal Transaksi: {{ $receivable->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <!-- Balances 3-Card Grid -->
            <div class="p-6 bg-slate-50 border-b border-slate-200/80">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Total Awal -->
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block">Total Tagihan Awal</span>
                        <span class="text-lg font-black text-slate-800 font-mono mt-1 block">
                            Rp {{ number_format($receivable->total_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Total Terbayar -->
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block">Total Sudah Terbayar</span>
                        <span class="text-lg font-black text-emerald-600 font-mono mt-1 block">
                            Rp {{ number_format($receivable->paid_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Sisa Tagihan -->
                    <div class="bg-white p-4 rounded-2xl border {{ $receivable->status === 'lunas' ? 'border-emerald-200' : 'border-rose-200 bg-rose-50/20' }} shadow-sm">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block">Sisa Saldo Piutang</span>
                        <span class="text-xl font-black font-mono mt-1 block {{ $receivable->status === 'lunas' ? 'text-slate-400' : 'text-rose-600' }}">
                            Rp {{ number_format($receivable->remaining_balance, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Progress Bar -->
                @php
                    $percentage = (float) $receivable->total_amount > 0 
                        ? min(100, round(((float) $receivable->paid_amount / (float) $receivable->total_amount) * 100)) 
                        : 100;
                @endphp
                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-600 mb-1.5">
                        <span>Tingkat Pelunasan Tagihan</span>
                        <span class="font-mono font-bold">{{ $percentage }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                        <div class="h-2.5 rounded-full {{ $percentage >= 100 ? 'bg-emerald-500' : 'bg-amber-500' }} transition-all duration-500" 
                             style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Form Pencatatan Pembayaran Cicilan (Hanya Tampil Jika Belum Lunas) -->
            @if($receivable->status === 'belum_lunas')
                <div class="p-6 sm:p-8 bg-amber-50/50 border-b border-amber-200">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs">
                            +
                        </div>
                        <h3 class="text-sm font-bold text-amber-950">Catat Pembayaran Cicilan Baru</h3>
                    </div>

                    <form method="POST" action="{{ route('receivables.payments.store', $receivable) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Nominal -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Pembayaran (Rp) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">Rp</span>
                                    <input type="number" 
                                           name="amount" 
                                           min="1" 
                                           max="{{ $receivable->remaining_balance }}" 
                                           step="any" 
                                           value="{{ old('amount', $receivable->remaining_balance) }}" 
                                           required 
                                           class="w-full pl-9 pr-3 py-2 text-sm font-mono font-bold text-slate-900 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1">Maksimal sisa saldo: Rp {{ number_format($receivable->remaining_balance, 0, ',', '.') }}</p>
                            </div>

                            <!-- Waktu Bayar -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Waktu Pembayaran <span class="text-rose-500">*</span></label>
                                <input type="datetime-local" 
                                       name="paid_at" 
                                       value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" 
                                       required 
                                       class="w-full text-xs py-2 px-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Keterangan</label>
                                <input type="text" 
                                       name="notes" 
                                       value="{{ old('notes') }}" 
                                       placeholder="Misal: Cicilan ke-2 panen jagung" 
                                       class="w-full text-xs py-2 px-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                                Simpan Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Riwayat Cicilan / Pembayaran -->
            <div class="p-6 sm:p-8">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Riwayat Pembayaran Cicilan ({{ $receivable->payments->count() }})</span>
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="pb-2.5 text-center w-12">No</th>
                                <th class="pb-2.5">Waktu Pembayaran</th>
                                <th class="pb-2.5 text-right">Nominal Bayar</th>
                                <th class="pb-2.5">Catatan</th>
                                <th class="pb-2.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($receivable->payments as $index => $payment)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 text-center text-slate-400 font-mono">{{ $index + 1 }}</td>
                                    <td class="py-3 font-mono text-slate-700">
                                        {{ $payment->paid_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-3 text-right font-mono font-bold text-emerald-600 text-sm">
                                        + Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 text-slate-600">
                                        {{ $payment->notes ?: '-' }}
                                    </td>
                                    <td class="py-3 text-center">
                                        <a href="{{ route('receivables.payments.print', [$receivable, $payment]) }}" 
                                           target="_blank" 
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold transition" 
                                           title="Cetak Kuitansi">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>Kuitansi</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        Belum ada cicilan atau pembayaran yang disetor untuk piutang ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
