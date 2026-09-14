<x-app-layout>
    @section('page_title', 'Laporan Piutang Petani')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('reports.index') }}" class="hover:text-emerald-600 transition font-semibold">Pusat Laporan</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Laporan Piutang</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-black text-slate-900 tracking-tight">Laporan Piutang & Tagihan Petani</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                Audit saldo piutang aktif, histori cicilan angsuran, jatuh tempo, dan rekapitulasi per pelanggan.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('reports.receivables.pdf', request()->query()) }}" 
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Cetak / Ekspor PDF</span>
            </a>
            <a href="{{ route('reports.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <form method="GET" action="{{ route('reports.receivables') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Status Pelunasan</label>
                <select name="status" 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="belum_lunas" {{ $filters['status'] === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas (Aktif)</option>
                    <option value="lunas" {{ $filters['status'] === 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Pelanggan / Petani</label>
                <select name="customer_id" 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Semua Pelanggan</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ (string)$filters['customer_id'] === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->phone ?? 'Tanpa No' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Rentang Nota (Opsional)</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}" placeholder="Mulai"
                           class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500">
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}" placeholder="Akhir"
                           class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                    Terapkan Filter
                </button>
                <a href="{{ route('reports.receivables') }}" 
                   title="Reset Filter"
                   class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 text-sm font-semibold rounded-xl border border-slate-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pokok Piutang</span>
            <div class="mt-2 text-2xl font-black text-slate-900 font-mono">
                Rp {{ number_format($summary['total_credit_issued'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-slate-500 mt-1 block">Dari {{ number_format($summary['total_records']) }} catatan piutang</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Telah Terbayar</span>
            <div class="mt-2 text-2xl font-black text-emerald-700 font-mono">
                Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-slate-500 mt-1 block">{{ $summary['paid_count'] }} transaksi lunas</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Sisa Saldo Aktif</span>
            <div class="mt-2 text-2xl font-black text-amber-700 font-mono">
                Rp {{ number_format($summary['total_remaining_balance'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-slate-500 mt-1 block">{{ $summary['unpaid_count'] }} transaksi belum lunas</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat Kolektibilitas</span>
            <div class="mt-2 text-2xl font-black text-teal-700 font-mono">
                @php
                    $rate = $summary['total_credit_issued'] > 0 
                        ? ($summary['total_paid'] / $summary['total_credit_issued']) * 100 
                        : 0;
                @endphp
                {{ number_format($rate, 1) }}%
            </div>
            <span class="text-xs text-slate-500 mt-1 block">Rasio terbayar terhadap total kredit</span>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Daftar Piutang & Riwayat Pembayaran</h2>
            <span class="text-xs text-slate-500">
                Status: {{ $filters['status'] === 'belum_lunas' ? 'Belum Lunas' : ($filters['status'] === 'lunas' ? 'Lunas' : 'Semua Status') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">No. Nota</th>
                        <th class="py-3 px-4">Tanggal Nota</th>
                        <th class="py-3 px-4">Nama Pelanggan</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-right">Total Kredit</th>
                        <th class="py-3 px-4 text-right">Terbayar</th>
                        <th class="py-3 px-4 text-right">Sisa Saldo</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($receivables as $rec)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-emerald-700">
                                {{ $rec->sale->invoice_no ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600">
                                {{ $rec->created_at ? $rec->created_at->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-900">{{ $rec->customer->name ?? 'Anonim' }}</span>
                                @if($rec->customer?->phone)
                                    <span class="block text-[11px] text-slate-500">{{ $rec->customer->phone }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs">
                                @if($rec->due_date)
                                    @php
                                        $isOverdue = $rec->status === 'belum_lunas' && $rec->due_date->isPast();
                                    @endphp
                                    <span class="{{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                        {{ $rec->due_date->format('d/m/Y') }}
                                        @if($isOverdue)
                                            <span class="block text-[10px] text-rose-600 font-bold">Jatuh Tempo!</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-medium text-slate-800 font-mono">
                                Rp {{ number_format($rec->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-emerald-700 font-mono">
                                Rp {{ number_format($rec->paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold font-mono {{ $rec->remaining_balance > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                                Rp {{ number_format($rec->remaining_balance, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($rec->status === 'lunas')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Lunas
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('receivables.show', $rec) }}" 
                                   title="Lihat Rincian & Pembayaran"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-emerald-700 hover:text-white bg-emerald-50 hover:bg-emerald-600 rounded-lg border border-emerald-200 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-sm font-semibold text-slate-700">Tidak ada catatan piutang ditemukan dengan kriteria filter saat ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receivables->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $receivables->links() }}
            </div>
        @endif
    </div>
</div>
</x-app-layout>
