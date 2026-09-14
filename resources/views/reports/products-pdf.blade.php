<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventori Stok & Rekomendasi Restok — Toko Pertanian Al Barokah</title>
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
            background-color: #0d9488;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #0d9488;
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
        .badge-safe {
            color: #047857;
            font-weight: bold;
        }
        .badge-restock {
            color: #dc2626;
            font-weight: bold;
        }
        .badge-empty {
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
        <h3>LAPORAN INVENTORI STOK FISIK & REKOMENDASI RESTOK (SMA)</h3>
    </div>

    <!-- Metadata Informasi Laporan -->
    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Kategori Produk</strong></td>
            <td style="width: 35%;">: {{ $filters['category_name'] }}</td>
            <td style="width: 18%;"><strong>Waktu Cetak</strong></td>
            <td style="width: 32%;">: {{ $generatedAt->translatedFormat('d F Y, H:i:s') }} WIB</td>
        </tr>
        <tr>
            <td><strong>Filter Kondisi Stok</strong></td>
            <td>
                : 
                @if($filters['stock_status'] === 'low_stock')
                    Stok Menipis Saja (Stok &le; Min)
                @elseif($filters['stock_status'] === 'out_of_stock')
                    Stok Kosong / Habis (0)
                @elseif($filters['stock_status'] === 'sma_needs_restock')
                    Produk Perlu Restok Berdasarkan Evaluasi SMA
                @else
                    Seluruh Kondisi Stok
                @endif
            </td>
            <td><strong>Dicetak Oleh</strong></td>
            <td>: {{ auth()->user()->name ?? 'Administrator Toko' }}</td>
        </tr>
    </table>

    <!-- Ringkasan Eksekutif Nilai Inventori -->
    <table class="summary-box">
        <tr>
            <td>
                <span class="label">Valuasi Modal Inventori</span>
                <span class="val" style="color: #0d9488;">Rp {{ number_format($summary['total_purchase_valuation'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Estimasi Nilai Jual</span>
                <span class="val" style="color: #047857;">Rp {{ number_format($summary['total_selling_valuation'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Total Unit Fisik Toko</span>
                <span class="val">{{ number_format($summary['total_units']) }} Unit</span>
            </td>
            <td>
                <span class="label">Peringatan Restok</span>
                <span class="val" style="color: #dc2626;">{{ $summary['low_stock_count'] + $summary['out_of_stock_count'] }} Produk</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Data Produk -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th style="width: 70px;">SKU</th>
                <th>Nama Produk</th>
                <th style="width: 80px;">Kategori</th>
                <th class="text-center" style="width: 45px;">Satuan</th>
                <th class="text-center" style="width: 50px;">Stok</th>
                <th class="text-center" style="width: 45px;">Min</th>
                <th class="text-center" style="width: 60px;">SMA 7H</th>
                <th class="text-center" style="width: 85px;">Status Restok</th>
                <th class="text-right" style="width: 85px;">Harga Beli</th>
                <th class="text-right" style="width: 95px;">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $p)
                @php
                    $forecast = $p->forecastResults->first();
                    $assetVal = $p->purchase_price * max(0, $p->stock);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-size: 8pt;">{{ $p->code }}</td>
                    <td style="font-weight: 500;">{{ $p->name }}</td>
                    <td>{{ $p->category->name ?? '-' }}</td>
                    <td class="text-center">{{ $p->unit }}</td>
                    <td class="text-center" style="font-weight: bold; {{ $p->stock <= 0 ? 'color: #dc2626;' : ($p->stock <= $p->min_stock ? 'color: #b45309;' : 'color: #047857;') }}">
                        {{ $p->stock }}
                    </td>
                    <td class="text-center" style="color: #64748b;">{{ $p->min_stock }}</td>
                    <td class="text-center font-mono">
                        {{ $forecast && $forecast->forecast_value !== null ? number_format($forecast->forecast_value, 1) : '-' }}
                    </td>
                    <td class="text-center">
                        @if(!$forecast || $forecast->status === 'insufficient_data')
                            <span style="color: #64748b; font-size: 7.5pt;">Data Kurang</span>
                        @elseif($forecast->status === 'perlu_restok')
                            <span class="badge-restock">+{{ $forecast->shortage_quantity }} Restok</span>
                        @else
                            <span class="badge-safe">Aman</span>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($assetVal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada catatan produk ditemukan dengan kriteria filter saat ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($products->isNotEmpty())
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #0d9488;">
                    <td colspan="5" class="text-right">TOTAL ASET INVENTORI:</td>
                    <td class="text-center">{{ number_format($summary['total_units']) }}</td>
                    <td colspan="4" class="text-right">VALUASI TOTAL:</td>
                    <td class="text-right" style="color: #0d9488; font-size: 9.5pt;">
                        Rp {{ number_format($summary['total_purchase_valuation'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan & Pengesahan -->
    <div class="footer-section">
        <div class="signature-box">
            <p style="margin-bottom: 4px;">Lampung, {{ $generatedAt->translatedFormat('d F Y') }}</p>
            <p style="font-weight: bold; margin: 0;">Pengelola Gudang / Pimpinan</p>
            <div class="signature-space"></div>
            <p style="font-weight: bold; text-decoration: underline; margin: 0;">( TOKO PERTANIAN AL BAROKAH )</p>
            <p style="font-size: 8pt; color: #64748b; margin: 2px 0 0 0;">Dokumen Rekapitulasi Inventori</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
