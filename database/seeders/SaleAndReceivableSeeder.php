<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DailySalesSummary;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Restock;
use App\Models\Sale;
use App\Models\User;
use App\Services\ForecastService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleAndReceivableSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $gramoxone = Product::where('code', 'HRB-GRM-01')->first();
        $roundup = Product::where('code', 'HRB-RND-01')->first();
        $prevathon = Product::where('code', 'INS-PRV-01')->first();
        $urea = Product::where('code', 'SKU-UREA-50KG')->first();
        $npk = Product::where('code', 'PPK-NPK-01')->first();
        $phonska = Product::where('code', 'PPK-PHO-50KG')->first();
        $benihJagung = Product::where('code', 'BNH-JGH-01')->first();

        $supardi = Customer::where('name', 'Pak Supardi')->first();
        $joko = Customer::where('name', 'Pak Joko Sutrisno')->first();
        $wayan = Customer::where('name', 'Pak Wayan Sudirga')->first();

        // 1. Historical Restocks
        Restock::create([
            'product_id' => $gramoxone->id,
            'quantity' => 20,
            'restock_date' => Carbon::today()->subDays(15),
            'notes' => 'Penerimaan stok supplier PT Agro Kimia Nusantara',
        ]);
        Restock::create([
            'product_id' => $roundup->id,
            'quantity' => 15,
            'restock_date' => Carbon::today()->subDays(12),
            'notes' => 'Pasokan rutin toko',
        ]);

        // 2. 14 Days Historical Sales for Gramoxone (>= 7 days for SMA verification)
        // Quantities sold on days 14..1 ago: [3, 4, 2, 5, 3, 6, 4, 3, 5, 2, 4, 6, 3, 5]
        $gramoxoneSalesQty = [3, 4, 2, 5, 3, 6, 4, 3, 5, 2, 4, 6, 3, 5];
        foreach ($gramoxoneSalesQty as $index => $qty) {
            $daysAgo = 14 - $index;
            $date = Carbon::today()->subDays($daysAgo)->setTime(10 + ($index % 5), 15, 0);

            $subtotal = $qty * (float) $gramoxone->selling_price;
            $sale = Sale::create([
                'invoice_no' => 'INV-'.$date->format('Ymd').'-00'.($index + 1),
                'user_id' => $admin->id,
                'customer_id' => null,
                'payment_method' => 'cash',
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'completed',
                'sold_at' => $date,
            ]);

            $sale->items()->create([
                'product_id' => $gramoxone->id,
                'quantity' => $qty,
                'price' => $gramoxone->selling_price,
                'subtotal' => $subtotal,
            ]);

            DailySalesSummary::updateOrCreate(
                ['product_id' => $gramoxone->id, 'sale_date' => $date->toDateString()],
                ['quantity_sold' => $qty]
            );
        }

        // 3. 9 Days Historical Sales for Roundup (>= 7 days)
        $roundupSalesQty = [2, 3, 4, 2, 5, 3, 4, 3, 4];
        foreach ($roundupSalesQty as $index => $qty) {
            $daysAgo = 9 - $index;
            $date = Carbon::today()->subDays($daysAgo)->setTime(11 + ($index % 4), 30, 0);

            $subtotal = $qty * (float) $roundup->selling_price;
            $sale = Sale::create([
                'invoice_no' => 'INV-'.$date->format('Ymd').'-RND'.($index + 1),
                'user_id' => $admin->id,
                'customer_id' => null,
                'payment_method' => 'cash',
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'completed',
                'sold_at' => $date,
            ]);

            $sale->items()->create([
                'product_id' => $roundup->id,
                'quantity' => $qty,
                'price' => $roundup->selling_price,
                'subtotal' => $subtotal,
            ]);

            DailySalesSummary::updateOrCreate(
                ['product_id' => $roundup->id, 'sale_date' => $date->toDateString()],
                ['quantity_sold' => $qty]
            );
        }

        // 4. 7 Days Historical Sales for Prevathon (>= 7 days for SMA calculation)
        $prevathonSalesQty = [2, 3, 2, 4, 3, 2, 3];
        foreach ($prevathonSalesQty as $index => $qty) {
            $daysAgo = 7 - $index;
            $date = Carbon::today()->subDays($daysAgo)->setTime(14, 20, 0);

            $subtotal = $qty * (float) $prevathon->selling_price;
            $sale = Sale::create([
                'invoice_no' => 'INV-'.$date->format('Ymd').'-PRV'.($index + 1),
                'user_id' => $admin->id,
                'customer_id' => null,
                'payment_method' => 'cash',
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'completed',
                'sold_at' => $date,
            ]);

            $sale->items()->create([
                'product_id' => $prevathon->id,
                'quantity' => $qty,
                'price' => $prevathon->selling_price,
                'subtotal' => $subtotal,
            ]);

            DailySalesSummary::updateOrCreate(
                ['product_id' => $prevathon->id, 'sale_date' => $date->toDateString()],
                ['quantity_sold' => $qty]
            );
        }

        // 5. 7 Days Historical Sales for NPK Phonska 50kg (>= 7 days for SMA calculation & Restock test)
        // Quantities sold on days 7..1 ago: [5, 4, 6, 5, 4, 7, 5] -> total 36 sak (rata-rata 5.14 sak/hari)
        // Stok aktual = 3 sak -> Stok (3) < Forecast (5.14) -> Status: 'Perlu Restok', Shortage: 3 sak
        if ($phonska) {
            $phonskaSalesQty = [5, 4, 6, 5, 4, 7, 5];
            foreach ($phonskaSalesQty as $index => $qty) {
                $daysAgo = 7 - $index;
                $date = Carbon::today()->subDays($daysAgo)->setTime(11, 45, 0);

                $subtotal = $qty * (float) $phonska->selling_price;
                $sale = Sale::create([
                    'invoice_no' => 'INV-'.$date->format('Ymd').'-PHO'.($index + 1),
                    'user_id' => $admin->id,
                    'customer_id' => null,
                    'payment_method' => 'cash',
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'status' => 'completed',
                    'sold_at' => $date,
                ]);

                $sale->items()->create([
                    'product_id' => $phonska->id,
                    'quantity' => $qty,
                    'price' => $phonska->selling_price,
                    'subtotal' => $subtotal,
                ]);

                DailySalesSummary::updateOrCreate(
                    ['product_id' => $phonska->id, 'sale_date' => $date->toDateString()],
                    ['quantity_sold' => $qty]
                );
            }
        }

        // 6. 7 Days Sales for Benih jagung hibrida 1kg (>= 7 days for SMA calculation)
        if ($benihJagung) {
            $benihSalesQty = [2, 3, 2, 4, 3, 2, 3];
            foreach ($benihSalesQty as $index => $qty) {
                $daysAgo = 7 - $index;
                $date = Carbon::today()->subDays($daysAgo)->setTime(14, 15, 0);

                $subtotal = $qty * (float) $benihJagung->selling_price;
                $sale = Sale::create([
                    'invoice_no' => 'INV-'.$date->format('Ymd').'-JGH'.($index + 1),
                    'user_id' => $admin->id,
                    'customer_id' => null,
                    'payment_method' => 'cash',
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'status' => 'completed',
                    'sold_at' => $date,
                ]);

                $sale->items()->create([
                    'product_id' => $benihJagung->id,
                    'quantity' => $qty,
                    'price' => $benihJagung->selling_price,
                    'subtotal' => $subtotal,
                ]);

                DailySalesSummary::updateOrCreate(
                    ['product_id' => $benihJagung->id, 'sale_date' => $date->toDateString()],
                    ['quantity_sold' => $qty]
                );
            }
        }

        // 7. Credit Transactions & Receivables
        // Credit Sale 1: Pak Supardi (Total Rp 300.000, partially paid Rp 200.000 -> Sisa Saldo Persis Rp 100.000)
        // Disiapkan khusus untuk pengujian UAT:
        // - Uji Kasus 6: Bayar angsuran Rp 150.000 (melebihi sisa Rp 100.000) -> Ditolak sistem
        // - Uji Kasus 7: Pelunasan pas Rp 100.000 -> Berhasil status lunas
        $date1 = Carbon::today()->subDays(5)->setTime(9, 30, 0);
        $total1 = 300000;
        $sale1 = Sale::create([
            'invoice_no' => 'INV-'.$date1->format('Ymd').'-CR01',
            'user_id' => $admin->id,
            'customer_id' => $supardi->id,
            'payment_method' => 'credit',
            'subtotal' => $total1,
            'total' => $total1,
            'status' => 'completed',
            'sold_at' => $date1,
        ]);
        $sale1->items()->create([
            'product_id' => $gramoxone->id,
            'quantity' => 2,
            'price' => 95000,
            'subtotal' => 190000,
        ]);
        $sale1->items()->create([
            'product_id' => $roundup->id,
            'quantity' => 1,
            'price' => 110000,
            'subtotal' => 110000,
        ]);
        $rec1 = Receivable::create([
            'sale_id' => $sale1->id,
            'customer_id' => $supardi->id,
            'total_amount' => $total1,
            'paid_amount' => 0,
            'remaining_balance' => $total1,
            'status' => 'belum_lunas',
        ]);
        // Cicilan awal: Rp 200.000 -> Menyisakan saldo tepat Rp 100.000!
        $rec1->recordPayment(200000, Carbon::today()->subDays(2)->toDateTimeString(), 'Cicilan awal tunai saat panen');

        // Credit Sale 2: Pak Joko Sutrisno (1 Urea = Rp 310.000, paid off in full)
        $date2 = Carbon::today()->subDays(10)->setTime(13, 45, 0);
        $total2 = 1 * (float) $urea->selling_price;
        $sale2 = Sale::create([
            'invoice_no' => 'INV-'.$date2->format('Ymd').'-CR02',
            'user_id' => $admin->id,
            'customer_id' => $joko->id,
            'payment_method' => 'credit',
            'subtotal' => $total2,
            'total' => $total2,
            'status' => 'completed',
            'sold_at' => $date2,
        ]);
        $sale2->items()->create([
            'product_id' => $urea->id,
            'quantity' => 1,
            'price' => $urea->selling_price,
            'subtotal' => $total2,
        ]);
        $rec2 = Receivable::create([
            'sale_id' => $sale2->id,
            'customer_id' => $joko->id,
            'total_amount' => $total2,
            'paid_amount' => 0,
            'remaining_balance' => $total2,
            'status' => 'belum_lunas',
        ]);
        // Pelunasan penuh
        $rec2->recordPayment(310000, Carbon::today()->subDays(3)->toDateTimeString(), 'Pelunasan penuh via transfer/tunai');

        // Credit Sale 3: Pak Wayan Sudirga (10 Gramoxone = Rp 950.000, unpaid)
        $date3 = Carbon::today()->subDays(4)->setTime(15, 10, 0);
        $total3 = 10 * (float) $gramoxone->selling_price;
        $sale3 = Sale::create([
            'invoice_no' => 'INV-'.$date3->format('Ymd').'-CR03',
            'user_id' => $admin->id,
            'customer_id' => $wayan->id,
            'payment_method' => 'credit',
            'subtotal' => $total3,
            'total' => $total3,
            'status' => 'completed',
            'sold_at' => $date3,
        ]);
        $sale3->items()->create([
            'product_id' => $gramoxone->id,
            'quantity' => 10,
            'price' => $gramoxone->selling_price,
            'subtotal' => $total3,
        ]);
        Receivable::create([
            'sale_id' => $sale3->id,
            'customer_id' => $wayan->id,
            'total_amount' => $total3,
            'paid_amount' => 0,
            'remaining_balance' => $total3,
            'status' => 'belum_lunas',
        ]);

        // 8. 7 Days Historical Sales for All Remaining Active Products
        // Memastikan seluruh 369 produk memiliki minimal 7 hari histori penjualan
        // sehingga seluruh produk dapat dihitung estimasi SMA 7-Hari (tanpa ada 'Data Belum Mencukupi')
        $seededProductIds = array_filter([
            $gramoxone?->id,
            $roundup?->id,
            $prevathon?->id,
            $phonska?->id,
            $benihJagung?->id,
        ]);

        $remainingProducts = Product::whereNotIn('id', $seededProductIds)->get();
        $customers = Customer::all();
        $customerList = $customers->isNotEmpty() ? $customers->pluck('id')->toArray() : [];
        $today = Carbon::today();
        $invoiceIndex = 1000;

        $saleItemsBatch = [];
        $summariesBatch = [];

        for ($daysAgo = 7; $daysAgo >= 1; $daysAgo--) {
            $date = $today->copy()->subDays($daysAgo);
            // Chunk products into groups of 6 items per transaction
            $chunks = $remainingProducts->chunk(6);

            foreach ($chunks as $chunkIndex => $chunk) {
                $invoiceIndex++;
                $invoiceNo = 'INV-'.$date->format('Ymd').'-'.str_pad($invoiceIndex, 5, '0', STR_PAD_LEFT);
                $soldAt = $date->copy()->setTime(8 + ($chunkIndex % 9), ($chunkIndex * 7) % 60, 0);
                $customerId = (! empty($customerList) && ($chunkIndex % 3 === 0))
                    ? $customerList[$chunkIndex % count($customerList)]
                    : null;

                $saleSubtotal = 0;
                $itemsToCreate = [];

                foreach ($chunk as $prod) {
                    // Realistic daily sales quantity based on product characteristics
                    $qty = max(1, ($prod->id + $daysAgo) % 4 + 1);
                    $itemSubtotal = $qty * (float) $prod->selling_price;
                    $saleSubtotal += $itemSubtotal;

                    $itemsToCreate[] = [
                        'product_id' => $prod->id,
                        'quantity' => $qty,
                        'price' => $prod->selling_price,
                        'subtotal' => $itemSubtotal,
                    ];

                    $summariesBatch[] = [
                        'product_id' => $prod->id,
                        'sale_date' => $date->toDateString(),
                        'quantity_sold' => $qty,
                        'created_at' => $soldAt,
                        'updated_at' => $soldAt,
                    ];
                }

                $saleId = DB::table('sales')->insertGetId([
                    'invoice_no' => $invoiceNo,
                    'user_id' => $admin->id,
                    'customer_id' => $customerId,
                    'payment_method' => 'cash',
                    'subtotal' => $saleSubtotal,
                    'total' => $saleSubtotal,
                    'status' => 'completed',
                    'sold_at' => $soldAt,
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ]);

                foreach ($itemsToCreate as $item) {
                    $saleItemsBatch[] = [
                        'sale_id' => $saleId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                        'created_at' => $soldAt,
                        'updated_at' => $soldAt,
                    ];
                }
            }
        }

        // Insert batch sale items & daily sales summaries
        foreach (array_chunk($saleItemsBatch, 500) as $chunk) {
            DB::table('sale_items')->insert($chunk);
        }

        foreach (array_chunk($summariesBatch, 500) as $chunk) {
            DailySalesSummary::upsert($chunk, ['product_id', 'sale_date'], ['quantity_sold', 'updated_at']);
        }

        // 9. Pre-calculate SMA demand forecast for all 369 products
        app(ForecastService::class)->calculateAll();
    }
}
