<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur - {{ $sale->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header {
            border-bottom: 2px solid #059669;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .store-title {
            font-size: 16px;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 2px;
        }
        .store-sub {
            font-size: 9px;
            color: #64748b;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            vertical-align: top;
            font-size: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .total-box {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .total-box td {
            padding: 4px 6px;
            font-size: 10px;
        }
        .grand-total {
            font-size: 12px;
            font-weight: bold;
            color: #065f46;
            border-top: 1px solid #059669;
            border-bottom: 1px solid #059669;
        }
        .signatures {
            margin-top: 30px;
            width: 100%;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            font-size: 10px;
        }
        .sign-space {
            height: 50px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-cash { background-color: #d1fae5; color: #065f46; }
        .badge-credit { background-color: #fef3c7; color: #92400e; }
    </style>
</head>
<body>

    <!-- Header Toko -->
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <div class="store-title">TOKO PERTANIAN AL BAROKAH</div>
                    <div class="store-sub">Penyedia Obat Pertanian, Benih, Pupuk & Perlengkapan Tani</div>
                    <div class="store-sub">Jl. Raya Pertanian No. 12, Sumber Makmur • HP/WA: 0812-3456-7890</div>
                </td>
                <td style="width: 40%; text-align: right;">
                    <div style="font-size: 14px; font-weight: bold; color: #0f172a;">FAKTUR PENJUALAN</div>
                    <div style="font-size: 11px; font-weight: bold; color: #059669; font-family: monospace;">{{ $sale->invoice_no }}</div>
                    <div style="margin-top: 4px;">
                        @if($sale->payment_method === 'cash')
                            <span class="badge badge-cash">LUNAS • TUNAI</span>
                        @else
                            <span class="badge badge-credit">KREDIT (PIUTANG)</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Informasi Transaksi & Pelanggan -->
    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>Kepada Yth:</strong><br>
                @if($sale->customer)
                    <strong>{{ $sale->customer->name }}</strong><br>
                    No. HP / WA: {{ $sale->customer->phone ?? '-' }}<br>
                    Alamat: {{ $sale->customer->address ?? '-' }}
                @else
                    Pelanggan Umum (Tunai)
                @endif
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Tanggal Transaksi:</strong> {{ $sale->sold_at->format('d/m/Y H:i') }}<br>
                <strong>Kasir:</strong> {{ $sale->user->name ?? 'Admin' }}<br>
                <strong>Metode Bayar:</strong> {{ strtoupper($sale->payment_method) }}
            </td>
        </tr>
    </table>

    <!-- Rincian Produk -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 45%;">Nama Produk / Barang</th>
                <th class="text-right" style="width: 18%;">Harga Satuan</th>
                <th class="text-center" style="width: 12%;">Jumlah</th>
                <th class="text-right" style="width: 20%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name ?? 'Item Produk' }}</strong><br>
                        <span style="font-size: 8px; color: #64748b;">SKU: {{ $item->product->code ?? '-' }}</span>
                    </td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->quantity }} {{ $item->product->unit ?? 'pcs' }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Ringkasan Total -->
    <table class="total-box">
        <tr>
            <td class="text-right font-bold">Subtotal:</td>
            <td class="text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr class="grand-total">
            <td class="text-right">TOTAL PEMBAYARAN:</td>
            <td class="text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
        </tr>
        @if($sale->payment_method === 'credit' && $sale->receivable)
            <tr>
                <td class="text-right" style="color: #92400e;">Sisa Saldo Piutang:</td>
                <td class="text-right font-bold" style="color: #b91c1c;">Rp {{ number_format($sale->receivable->remaining_balance, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    <!-- Tanda Tangan -->
    <table class="signatures">
        <tr>
            <td>
                Pelanggan / Petani,
                <div class="sign-space"></div>
                ( {{ $sale->customer->name ?? '.......................' }} )
            </td>
            <td>
                Kasir / Petugas Toko,
                <div class="sign-space"></div>
                ( {{ $sale->user->name ?? 'Admin Toko' }} )
            </td>
        </tr>
    </table>

</body>
</html>
