<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan — Toko Pertanian Al Barokah</title>
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
            width: 20%;
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
            background-color: #047857;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #047857;
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
        .badge-cash {
            color: #047857;
            font-weight: bold;
        }
        .badge-credit {
            color: #b45309;
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
        <h3>LAPORAN REKAPITULASI PENJUALAN</h3>
    </div>

    <!-- Metadata Informasi Laporan -->
    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Periode Transaksi</strong></td>
            <td style="width: 35%;">: {{ \Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d F Y') }}</td>
            <td style="width: 18%;"><strong>Waktu Cetak</strong></td>
            <td style="width: 32%;">: {{ $generatedAt->translatedFormat('d F Y, H:i:s') }} WIB</td>
        </tr>
        <tr>
            <td><strong>Metode Pembayaran</strong></td>
            <td>: {{ $filters['payment_method'] === 'cash' ? 'Tunai Saja' : ($filters['payment_method'] === 'credit' ? 'Piutang Saja' : 'Semua Metode (Tunai & Piutang)') }}</td>
            <td><strong>Dicetak Oleh</strong></td>
            <td>: {{ auth()->user()->name ?? 'Administrator Toko' }}</td>
        </tr>
    </table>

    <!-- Ringkasan Eksekutif Keuangan -->
    <table class="summary-box">
        <tr>
            <td>
                <span class="label">Total Omset Penjualan</span>
                <span class="val" style="color: #047857;">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Penerimaan Tunai</span>
                <span class="val" style="color: #0f766e;">Rp {{ number_format($summary['cash_sales'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Penjualan Piutang</span>
                <span class="val" style="color: #b45309;">Rp {{ number_format($summary['credit_sales'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Total Transaksi</span>
                <span class="val">{{ number_format($summary['total_transactions']) }} Nota</span>
            </td>
            <td>
                <span class="label">Total Item Terjual</span>
                <span class="val">{{ number_format($summary['total_items_sold']) }} Unit</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Data Transaksi -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th style="width: 110px;">No. Nota</th>
                <th style="width: 95px;">Tanggal & Jam</th>
                <th>Nama Pelanggan</th>
                <th style="width: 80px;">Kasir</th>
                <th class="text-center" style="width: 65px;">Metode</th>
                <th class="text-center" style="width: 55px;">Item</th>
                <th class="text-right" style="width: 105px;">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $index => $sale)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->sold_at ? $sale->sold_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $sale->customer->name ?? 'Umum / Tunai' }}</td>
                    <td>{{ $sale->user->name ?? 'Admin' }}</td>
                    <td class="text-center">
                        @if($sale->payment_method === 'cash')
                            <span class="badge-cash">TUNAI</span>
                        @else
                            <span class="badge-credit">PIUTANG</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $sale->items->sum('quantity') }}</td>
                    <td class="text-right" style="font-weight: bold;">
                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada catatan transaksi penjualan pada rentang filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($sales->isNotEmpty())
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #047857;">
                    <td colspan="6" class="text-right">GRAND TOTAL PENJUALAN:</td>
                    <td class="text-center">{{ number_format($summary['total_items_sold']) }}</td>
                    <td class="text-right" style="color: #047857; font-size: 9.5pt;">
                        Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan & Pengesahan -->
    <div class="footer-section">
        <div class="signature-box">
            <p style="margin-bottom: 4px;">Lampung, {{ $generatedAt->translatedFormat('d F Y') }}</p>
            <p style="font-weight: bold; margin: 0;">Pemilik / Pimpinan Toko</p>
            <div class="signature-space"></div>
            <p style="font-weight: bold; text-decoration: underline; margin: 0;">( TOKO PERTANIAN AL BAROKAH )</p>
            <p style="font-size: 8pt; color: #64748b; margin: 2px 0 0 0;">Dokumen Laporan Resmi</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
