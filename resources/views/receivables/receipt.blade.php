<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Piutang #{{ $payment->id }} - {{ $receivable->customer->name ?? 'Petani' }}</title>
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
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-top: 4px;
        }
        .total-grand {
            font-size: 13px;
            font-weight: 900;
        }
        .signatures {
            margin-top: 20px;
            width: 100%;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            font-size: 10px;
        }
        .sign-space {
            height: 35px;
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
        <button onclick="window.print()" class="btn">🖨️ Cetak Kuitansi</button>
        <button onclick="window.close()" class="btn btn-close">Tutup</button>
    </div>

    <div class="receipt">
        <!-- Header -->
        <div class="text-center">
            <div class="title">AL BAROKAH</div>
            <div class="subtitle">TOKO OBAT & ALAT PERTANIAN</div>
            <div class="subtitle">Jl. Raya Pertanian No. 12, Sumber Makmur</div>
            <div class="subtitle">Telp/WA: 0812-3456-7890</div>
        </div>

        <div class="divider"></div>

        <div class="text-center font-bold" style="margin-bottom: 4px;">
            BUKTI PEMBAYARAN PIUTANG
        </div>

        <div class="info-row">
            <span>No. Bukti: #PAY-{{ $payment->id }}</span>
            <span>{{ $payment->paid_at->format('d/m/y H:i') }}</span>
        </div>
        <div class="info-row">
            <span>Faktur Asal:</span>
            <span class="font-bold">{{ $receivable->sale->invoice_no ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span>Petani / Plg:</span>
            <span class="font-bold">{{ $receivable->customer->name ?? '-' }}</span>
        </div>

        <div class="divider"></div>

        <!-- Payment Details -->
        <div class="info-row">
            <span>Total Tagihan Awal:</span>
            <span>Rp {{ number_format($receivable->total_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row total-grand font-bold" style="margin: 6px 0;">
            <span>JUMLAH DIBAYAR:</span>
            <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
        </div>
        <div class="info-row">
            <span>Total Sudah Masuk:</span>
            <span>Rp {{ number_format($receivable->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row font-bold">
            <span>SISA SALDO PIUTANG:</span>
            <span>Rp {{ number_format($receivable->remaining_balance, 0, ',', '.') }}</span>
        </div>
        <div class="info-row" style="margin-top: 4px;">
            <span>Status Akhir:</span>
            <span class="font-bold">{{ strtoupper($receivable->status) }}</span>
        </div>

        @if($payment->notes)
            <div class="divider"></div>
            <div class="info-row">
                <span>Catatan:</span>
                <span>{{ $payment->notes }}</span>
            </div>
        @endif

        <div class="divider"></div>

        <!-- Tanda Tangan -->
        <table class="signatures">
            <tr>
                <td>
                    Penyetor / Petani,
                    <div class="sign-space"></div>
                    ({{ $receivable->customer->name ?? 'Petani' }})
                </td>
                <td>
                    Kasir Penerima,
                    <div class="sign-space"></div>
                    ({{ Auth::user()->name ?? 'Kasir Toko' }})
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>*** SIMPAN KUITANSI INI DENGAN BAIK ***</p>
            <p>Terima kasih atas pelunasan / cicilan Anda!</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            if (!window.location.search.includes('no_auto_print')) {
                window.print();
            }
        });
    </script>
</body>
</html>
