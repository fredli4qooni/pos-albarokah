<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $sale->invoice_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            background-color: #f1f5f9;
            color: #000;
            font-size: 12px;
            line-height: 1.3;
            padding: 20px;
        }
        .receipt {
            width: 80mm;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .title {
            font-size: 14px;
            font-weight: 900;
            margin-bottom: 2px;
        }
        .subtitle {
            font-size: 10px;
            color: #333;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 3px;
        }
        .item-row {
            margin-bottom: 6px;
        }
        .item-name {
            font-weight: bold;
            font-size: 11px;
        }
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-top: 4px;
        }
        .total-grand {
            font-size: 14px;
            font-weight: 900;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 12px;
            color: #444;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            background: #0f172a;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-family: sans-serif;
            font-size: 13px;
            font-weight: bold;
        }
        .btn-close {
            background: #e2e8f0;
            color: #334155;
            margin-left: 8px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt {
                width: 100%;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨️ Cetak Struk</button>
        <button onclick="window.close()" class="btn btn-close">Tutup</button>
    </div>

    <div class="receipt">
        <!-- Header -->
        <div class="text-center">
            <div class="title">AL BAROKAH</div>
            <div class="subtitle">TOKO OBAT & ALAT PERTANIAN</div>
            <div class="subtitle">Jl. Raya Pertanian No. 12</div>
            <div class="subtitle">Telp/WA: 0812-3456-7890</div>
        </div>

        <div class="divider"></div>

        <!-- Meta -->
        <div class="info-row">
            <span>Nota: {{ $sale->invoice_no }}</span>
            <span>{{ $sale->sold_at->format('d/m/y H:i') }}</span>
        </div>
        <div class="info-row">
            <span>Kasir: {{ $sale->user->name ?? 'Admin' }}</span>
            <span>Metode: {{ strtoupper($sale->payment_method) }}</span>
        </div>
        @if($sale->customer)
            <div class="info-row">
                <span>Plg: {{ $sale->customer->name }}</span>
                <span>{{ $sale->customer->phone ?? '' }}</span>
            </div>
        @endif

        <div class="divider"></div>

        <!-- Items -->
        <div>
            @foreach($sale->items as $item)
                <div class="item-row">
                    <div class="item-name">{{ $item->product->name ?? 'Barang' }}</div>
                    <div class="item-detail">
                        <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                        <span class="font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <!-- Total -->
        <div class="total-row font-bold">
            <span>Subtotal</span>
            <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="total-row total-grand">
            <span>TOTAL</span>
            <span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
        </div>

        @if($sale->payment_method === 'cash')
            <div class="total-row" style="margin-top: 6px;">
                <span>Tunai</span>
                <span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span>Kembalian</span>
                <span>Rp 0</span>
            </div>
        @else
            <div class="total-row" style="margin-top: 6px; font-weight: bold;">
                <span>Status</span>
                <span>PIUTANG PETANI</span>
            </div>
            @if($sale->receivable)
                <div class="total-row">
                    <span>Sisa Piutang</span>
                    <span>Rp {{ number_format($sale->receivable->remaining_balance, 0, ',', '.') }}</span>
                </div>
            @endif
        @endif

        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer">
            <p>*** TERIMA KASIH ***</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
            <p>Semoga hasil panen berkah & melimpah!</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            // Auto print if not in test environment
            if (!window.location.search.includes('no_auto_print')) {
                window.print();
            }
        });
    </script>
</body>
</html>
