<x-app-layout>
    @section('page_title', 'Profil Pelanggan: ' . $customer->name)

    <div class="space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-600 transition mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Data Pelanggan</span>
                </a>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ $customer->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Alamat: {{ $customer->address ?: '-' }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($customer->phone)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone);
                        $wa = str_starts_with($cleanPhone, '0') ? '62' . substr($cleanPhone, 1) : $cleanPhone;
                    @endphp
                    <a href="https://wa.me/{{ $wa }}" target="_blank" 
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
                        <span>Chat WhatsApp</span>
                    </a>
                @endif
                <a href="{{ route('customers.edit', $customer) }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Profil</span>
                </a>
            </div>
        </div>

        <!-- 2-Column Info Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <!-- Card 1: Saldo Piutang Aktif -->
            @php $outstandingDebt = $customer->totalOutstandingDebt(); @endphp
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Piutang Berjalan</span>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black {{ $outstandingDebt > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                        Rp {{ number_format($outstandingDebt, 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-2">
                    @if($outstandingDebt > 0)
                        <span class="text-xs font-semibold text-rose-600">Ada tagihan yang belum lunas.</span>
                    @else
                        <span class="text-xs font-semibold text-emerald-600">Tidak ada sisa tagihan piutang (Lunas).</span>
                    @endif
                </div>
            </div>

            <!-- Card 2: Kontak & Identitas -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kontak & Alamat</span>
                <div class="mt-3 space-y-1.5 text-xs">
                    <div>
                        <span class="text-slate-400">Nomor HP / WA:</span>
                        <span class="font-bold text-slate-800 ml-1">{{ $customer->phone ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Alamat Tempat Tinggal:</span>
                        <span class="font-bold text-slate-800 ml-1">{{ $customer->address ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receivables Breakdown Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-4">Daftar Tagihan Piutang</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-2.5 px-4">Invoice</th>
                            <th class="py-2.5 px-4 text-right">Total Transaksi</th>
                            <th class="py-2.5 px-4 text-right">Sudah Dibayar</th>
                            <th class="py-2.5 px-4 text-right">Sisa Tagihan</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customer->receivables as $rec)
                            <tr>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                    {{ $rec->sale->invoice_no ?? ('REC-' . $rec->id) }}
                                </td>
                                <td class="py-3 px-4 text-right font-medium text-slate-600">
                                    Rp {{ number_format($rec->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700">
                                    Rp {{ number_format($rec->paid_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-black {{ $rec->remaining_balance > 0 ? 'text-rose-600' : 'text-slate-700' }}">
                                    Rp {{ number_format($rec->remaining_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($rec->status === 'lunas')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Lunas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Belum Lunas
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat transaksi kredit/piutang untuk pelanggan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
