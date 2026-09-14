<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Piutang Petani — Toko Pertanian Al Barokah</title>
    <style>
        @page {
            margin: 12mm 15mm;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header h1 {
            font-size: 16pt;
            margin: 0 0 2px 0;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 10pt;
            font-weight: normal;
            margin: 0 0 2px 0;
            color: #334155;
        }
        .header p {
            font-size: 8pt;
            margin: 0;
            color: #64748b;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .summary-box td {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: center;
            width: 25%;
        }
        .summary-box .label {
            font-size: 7.5pt;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        .summary-box .val {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 16px;
        }
        .data-table th {
            background-color: #b45309;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #b45309;
            font-size: 8pt;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge-paid {
            color: #047857;
            font-weight: bold;
        }
        .badge-unpaid {
            color: #dc2626;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer-section {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 220px;
            text-align: center;
            float: right;
            font-size: 9pt;
        }
        .signature-space {
            height: 55px;
        }
    </style>
</head>
<body>

    <!-- Header Kop Surat -->
    <div class="header">
        <h1>TOKO PERTANIAN AL BAROKAH</h1>
        <h2>Penyedia Sarana Produksi Pertanian, Pupuk, Obat-obatan & Benih Unggul</h2>
        <p>Jl. Raya Lintas Pertanian No. 45, Lampung • Telp/WhatsApp: 0812-3456-7890</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        <h3>LAPORAN REKAPITULASI PIUTANG PETANI</h3>
    </div>

    <!-- Metadata Informasi Laporan -->
    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Status Piutang</strong></td>
            <td style="width: 35%;">: {{ $filters['status'] === 'belum_lunas' ? 'Belum Lunas Saja' : ($filters['status'] === 'lunas' ? 'Lunas Saja' : 'Semua Status (Lunas & Belum Lunas)') }}</td>
            <td style="width: 18%;"><strong>Waktu Cetak</strong></td>
            <td style="width: 32%;">: {{ $generatedAt->translatedFormat('d F Y, H:i:s') }} WIB</td>
        </tr>
        <tr>
            <td><strong>Filter Pelanggan</strong></td>
            <td>: {{ $filters['customer_name'] }}</td>
            <td><strong>Dicetak Oleh</strong></td>
            <td>: {{ auth()->user()->name ?? 'Administrator Toko' }}</td>
        </tr>
    </table>

    <!-- Ringkasan Eksekutif Piutang -->
    <table class="summary-box">
        <tr>
            <td>
                <span class="label">Total Pokok Piutang</span>
                <span class="val">Rp {{ number_format($summary['total_credit_issued'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Total Telah Terbayar</span>
                <span class="val" style="color: #047857;">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Sisa Saldo Belum Lunas</span>
                <span class="val" style="color: #dc2626;">Rp {{ number_format($summary['total_remaining_balance'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Tingkat Kolektibilitas</span>
                @php
                    $rate = $summary['total_credit_issued'] > 0 
                        ? ($summary['total_paid'] / $summary['total_credit_issued']) * 100 
                        : 0;
                @endphp
                <span class="val" style="color: #0f766e;">{{ number_format($rate, 1) }}%</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Data Piutang -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th style="width: 100px;">No. Nota</th>
                <th style="width: 80px;">Tgl Nota</th>
                <th>Nama Pelanggan</th>
                <th style="width: 90px;">No. Telepon</th>
                <th style="width: 80px;">Jatuh Tempo</th>
                <th class="text-right" style="width: 95px;">Total Kredit</th>
                <th class="text-right" style="width: 95px;">Terbayar</th>
                <th class="text-right" style="width: 95px;">Sisa Saldo</th>
                <th class="text-center" style="width: 75px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receivables as $index => $rec)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $rec->sale->invoice_no ?? '-' }}</td>
                    <td>{{ $rec->created_at ? $rec->created_at->format('d/m/Y') : '-' }}</td>
                    <td style="font-weight: 500;">{{ $rec->customer->name ?? '-' }}</td>
                    <td>{{ $rec->customer->phone ?? '-' }}</td>
                    <td>{{ $rec->due_date ? $rec->due_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($rec->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #047857;">Rp {{ number_format($rec->paid_amount, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: {{ $rec->remaining_balance > 0 ? '#b45309' : '#64748b' }};">
                        Rp {{ number_format($rec->remaining_balance, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($rec->status === 'lunas')
                            <span class="badge-paid">LUNAS</span>
                        @else
                            <span class="badge-unpaid">BELUM LUNAS</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada catatan piutang ditemukan dengan kriteria filter saat ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($receivables->isNotEmpty())
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #b45309;">
                    <td colspan="6" class="text-right">TOTAL KESELURUHAN:</td>
                    <td class="text-right">Rp {{ number_format($summary['total_credit_issued'], 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #047857;">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #dc2626; font-size: 9.5pt;">
                        Rp {{ number_format($summary['total_remaining_balance'], 0, ',', '.') }}
                    </td>
                    <td class="text-center">{{ $summary['unpaid_count'] }} Aktif</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan & Pengesahan -->
    <div class="footer-section">
        <div class="signature-box">
            <p style="margin-bottom: 4px;">Lampung, {{ $generatedAt->translatedFormat('d F Y') }}</p>
            <p style="font-weight: bold; margin: 0;">Pengelola Piutang / Pimpinan</p>
            <div class="signature-space"></div>
            <p style="font-weight: bold; text-decoration: underline; margin: 0;">( TOKO PERTANIAN AL BAROKAH )</p>
            <p style="font-size: 8pt; color: #64748b; margin: 2px 0 0 0;">Dokumen Rekapitulasi Piutang</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
