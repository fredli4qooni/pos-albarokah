<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Stiker QR Code - {{ $product->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8fafc;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .btn-print {
            padding: 9px 18px;
            background-color: #059669;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
            transition: background 0.2s;
        }
        .btn-print:hover {
            background-color: #047857;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            max-width: 800px;
            margin: 0 auto;
        }
        .sticker {
            background: white;
            border: 1px dashed #cbd5e1;
            padding: 14px 10px;
            border-radius: 12px;
            text-align: center;
            box-sizing: border-box;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }
        .store-name {
            font-size: 9px;
            font-weight: 800;
            color: #059669;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .product-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            margin: 4px 0 6px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }
        .qr-box {
            margin: 4px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .qr-box svg {
            width: 76px;
            height: 76px;
        }
        .qr-code-text {
            font-family: monospace;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #334155;
            margin-top: 4px;
        }
        .price {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 4px;
        }
        @media print {
            .header {
                display: none;
            }
            body {
                background: white;
                margin: 0;
            }
            .grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }
            .sticker {
                border: 1px solid #cbd5e1;
                page-break-inside: avoid;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2 style="margin: 0 0 6px 0; color: #0f172a;">Lembar Cetak Stiker QR Code Produk</h2>
        <p style="font-size: 13px; color: #64748b; margin: 0 0 16px 0;">Cetak dan tempelkan stiker QR Code ini pada rak display atau kemasan fisik produk untuk pemindaian kasir.</p>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Print Stiker QR Code</button>
    </div>

    <div class="grid">
        @for ($i = 0; $i < 9; $i++)
            <div class="sticker">
                <div class="store-name">Toko Pertanian Al Barokah</div>
                <div class="product-name" title="{{ $product->name }}">{{ $product->name }}</div>
                <div class="qr-box">
                    {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(76)->margin(1)->generate($product->barcode ?: $product->code) !!}
                    <div class="qr-code-text">{{ $product->barcode ?: $product->code }}</div>
                </div>
                <div class="price">Rp {{ number_format($product->selling_price, 0, ',', '.') }} / {{ $product->unit }}</div>
            </div>
        @endfor
    </div>

</body>
</html>
