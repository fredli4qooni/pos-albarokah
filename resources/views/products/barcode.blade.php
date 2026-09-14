<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Barcode - {{ $product->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8fafc;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            padding: 8px 16px;
            background-color: #059669;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
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
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            box-sizing: border-box;
        }
        .store-name {
            font-size: 9px;
            font-weight: bold;
            color: #059669;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .product-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            margin: 4px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .barcode-box {
            margin: 6px 0;
        }
        .barcode-code {
            font-family: monospace;
            font-size: 10px;
            letter-spacing: 2px;
            color: #334155;
        }
        .price {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
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
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Lembar Cetak Label Barcode Produk</h2>
        <p style="font-size: 13px; color: #64748b; margin-top: 4px;">Tempelkan stiker ini pada rak display atau kemasan fisik produk.</p>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Print Stiker</button>
    </div>

    <div class="grid">
        @for ($i = 0; $i < 9; $i++)
            <div class="sticker">
                <div class="store-name">Toko Pertanian Al Barokah</div>
                <div class="product-name" title="{{ $product->name }}">{{ $product->name }}</div>
                <div class="barcode-box">
                    <svg style="height: 36px; width: 100%;" viewBox="0 0 160 36">
                        <rect x="5" y="2" width="2" height="30" fill="#000"/>
                        <rect x="9" y="2" width="1" height="30" fill="#000"/>
                        <rect x="12" y="2" width="3" height="30" fill="#000"/>
                        <rect x="17" y="2" width="1" height="30" fill="#000"/>
                        <rect x="20" y="2" width="2" height="30" fill="#000"/>
                        <rect x="25" y="2" width="3" height="30" fill="#000"/>
                        <rect x="30" y="2" width="1" height="30" fill="#000"/>
                        <rect x="33" y="2" width="4" height="30" fill="#000"/>
                        <rect x="39" y="2" width="1" height="30" fill="#000"/>
                        <rect x="43" y="2" width="2" height="30" fill="#000"/>
                        <rect x="47" y="2" width="3" height="30" fill="#000"/>
                        <rect x="52" y="2" width="1" height="30" fill="#000"/>
                        <rect x="55" y="2" width="4" height="30" fill="#000"/>
                        <rect x="61" y="2" width="2" height="30" fill="#000"/>
                        <rect x="65" y="2" width="1" height="30" fill="#000"/>
                        <rect x="68" y="2" width="3" height="30" fill="#000"/>
                        <rect x="73" y="2" width="2" height="30" fill="#000"/>
                        <rect x="77" y="2" width="4" height="30" fill="#000"/>
                        <rect x="83" y="2" width="1" height="30" fill="#000"/>
                        <rect x="86" y="2" width="3" height="30" fill="#000"/>
                        <rect x="91" y="2" width="2" height="30" fill="#000"/>
                        <rect x="95" y="2" width="1" height="30" fill="#000"/>
                        <rect x="98" y="2" width="4" height="30" fill="#000"/>
                        <rect x="104" y="2" width="2" height="30" fill="#000"/>
                        <rect x="108" y="2" width="3" height="30" fill="#000"/>
                        <rect x="113" y="2" width="1" height="30" fill="#000"/>
                        <rect x="116" y="2" width="3" height="30" fill="#000"/>
                        <rect x="121" y="2" width="2" height="30" fill="#000"/>
                        <rect x="125" y="2" width="4" height="30" fill="#000"/>
                        <rect x="131" y="2" width="1" height="30" fill="#000"/>
                        <rect x="134" y="2" width="2" height="30" fill="#000"/>
                        <rect x="138" y="2" width="3" height="30" fill="#000"/>
                        <rect x="143" y="2" width="1" height="30" fill="#000"/>
                        <rect x="146" y="2" width="4" height="30" fill="#000"/>
                        <rect x="152" y="2" width="2" height="30" fill="#000"/>
                    </svg>
                    <div class="barcode-code">{{ $product->barcode ?: $product->code }}</div>
                </div>
                <div class="price">Rp {{ number_format($product->selling_price, 0, ',', '.') }} / {{ $product->unit }}</div>
            </div>
        @endfor
    </div>

</body>
</html>
