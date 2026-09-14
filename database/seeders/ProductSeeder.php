<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $herbisida = Category::where('slug', 'herbisida')->first()->id;
        $fungisida = Category::where('slug', 'fungisida')->first()->id;
        $insektisida = Category::where('slug', 'insektisida')->first()->id;
        $pupuk = Category::where('slug', 'pupuk')->first()->id;
        $benih = Category::where('slug', 'benih')->first()->id;
        $alat = Category::where('slug', 'alat-tani')->first()->id;

        $products = [
            // Herbisida (Fokus proposal penelitian)
            [
                'category_id' => $herbisida,
                'code' => 'HRB-GRM-01',
                'barcode' => '8991234001011',
                'name' => 'Gramoxone 276 SL 1 Liter',
                'unit' => 'Botol',
                'purchase_price' => 82000,
                'selling_price' => 95000,
                'stock' => 12,
                'min_stock' => 10,
            ],
            [
                'category_id' => $herbisida,
                'code' => 'HRB-RND-01',
                'barcode' => '8991234001028',
                'name' => 'Roundup 486 SL 1 Liter',
                'unit' => 'Botol',
                'purchase_price' => 95000,
                'selling_price' => 110000,
                'stock' => 4, // Stockout risk: below min_stock
                'min_stock' => 8,
            ],
            [
                'category_id' => $herbisida,
                'code' => 'HRB-RMB-01',
                'barcode' => '8991234001035',
                'name' => 'Rambo 480 SL 1 Liter',
                'unit' => 'Botol',
                'purchase_price' => 70000,
                'selling_price' => 85000,
                'stock' => 15,
                'min_stock' => 8,
            ],
            [
                'category_id' => $herbisida,
                'code' => 'HRB-CBA-01',
                'barcode' => '8991234001042',
                'name' => 'CBA 6 1 Liter',
                'unit' => 'Botol',
                'purchase_price' => 60000,
                'selling_price' => 72000,
                'stock' => 20,
                'min_stock' => 10,
            ],
            [
                'category_id' => $herbisida,
                'code' => 'HRB-ALY-01',
                'barcode' => '8991234001059',
                'name' => 'Ally 20 WG 5 Gram',
                'unit' => 'Bungkus',
                'purchase_price' => 18000,
                'selling_price' => 25000,
                'stock' => 50,
                'min_stock' => 15,
            ],

            // Fungisida
            [
                'category_id' => $fungisida,
                'code' => 'FNG-ANT-01',
                'barcode' => '8991234002018',
                'name' => 'Antracol 70 WP 250 Gram',
                'unit' => 'Bungkus',
                'purchase_price' => 45000,
                'selling_price' => 55000,
                'stock' => 18,
                'min_stock' => 8,
            ],
            [
                'category_id' => $fungisida,
                'code' => 'FNG-DTH-01',
                'barcode' => '8991234002025',
                'name' => 'Dithane M-45 80 WP 500 Gram',
                'unit' => 'Bungkus',
                'purchase_price' => 75000,
                'selling_price' => 88000,
                'stock' => 7,
                'min_stock' => 5,
            ],

            // Insektisida
            [
                'category_id' => $insektisida,
                'code' => 'INS-PRV-01',
                'barcode' => '8991234003015',
                'name' => 'Prevathon 50 SC 100 ml',
                'unit' => 'Botol',
                'purchase_price' => 80000,
                'selling_price' => 95000,
                'stock' => 10,
                'min_stock' => 5,
            ],
            [
                'category_id' => $insektisida,
                'code' => 'INS-RGT-01',
                'barcode' => '8991234003022',
                'name' => 'Regent 50 SC 100 ml',
                'unit' => 'Botol',
                'purchase_price' => 52000,
                'selling_price' => 62000,
                'stock' => 14,
                'min_stock' => 6,
            ],

            // Pupuk
            [
                'category_id' => $pupuk,
                'code' => 'PPK-NPK-01',
                'barcode' => '8991234004012',
                'name' => 'Pupuk NPK Mutiara 16-16-16 1 Kg',
                'unit' => 'Kg',
                'purchase_price' => 16000,
                'selling_price' => 20000,
                'stock' => 45,
                'min_stock' => 20,
            ],
            [
                'category_id' => $pupuk,
                'code' => 'PPK-URA-01',
                'barcode' => '8991234004029',
                'name' => 'Pupuk Urea Petro 50 Kg',
                'unit' => 'Sak',
                'purchase_price' => 280000,
                'selling_price' => 310000,
                'stock' => 8,
                'min_stock' => 5,
            ],

            // Benih
            [
                'category_id' => $benih,
                'code' => 'BNH-INP-01',
                'barcode' => '8991234005019',
                'name' => 'Benih Padi Inpari 32 HDB 5 Kg',
                'unit' => 'Bungkus',
                'purchase_price' => 70000,
                'selling_price' => 85000,
                'stock' => 25,
                'min_stock' => 10,
            ],

            // Alat
            [
                'category_id' => $alat,
                'code' => 'ALT-SPR-01',
                'barcode' => '8991234006016',
                'name' => 'Tangki Sprayer Elektrik CBA 16 Liter',
                'unit' => 'Pcs',
                'purchase_price' => 420000,
                'selling_price' => 490000,
                'stock' => 3,
                'min_stock' => 3,
            ],
        ];

        foreach ($products as $item) {
            Product::firstOrCreate(['code' => $item['code']], $item);
        }
    }
}
