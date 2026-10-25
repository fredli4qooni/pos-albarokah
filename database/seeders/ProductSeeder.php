<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryMap = [
            'Herbisida' => Category::where('slug', 'herbisida')->value('id'),
            'Fungisida' => Category::where('slug', 'fungisida')->value('id'),
            'Insektisida' => Category::where('slug', 'insektisida')->value('id'),
            'Bakterisida' => Category::where('slug', 'bakterisida')->value('id'),
            'Pupuk' => Category::where('slug', 'pupuk')->value('id'),
            'Benih' => Category::where('slug', 'benih')->value('id'),
            'Alat & Perlengkapan' => Category::where('slug', 'alat-tani')->value('id'),
            'Moluskisida & Rodentisida' => Category::where('slug', 'moluskisida-rodentisida')->value('id'),
            'Perekat & Adjuvan' => Category::where('slug', 'perekat-adjuvan')->value('id'),
            'ZPT & Biostimulan' => Category::where('slug', 'zpt-biostimulan')->value('id'),
        ];

        $jsonFile = database_path('data/products_369.json');
        if (file_exists($jsonFile)) {
            $items = json_decode(file_get_contents($jsonFile), true);
            foreach ($items as $item) {
                $categoryId = $categoryMap[$item['category']] ?? $categoryMap['Pupuk'];
                Product::firstOrCreate(
                    ['code' => $item['code']],
                    [
                        'category_id' => $categoryId,
                        'barcode' => $item['barcode'],
                        'name' => $item['name'],
                        'unit' => $item['unit'],
                        'purchase_price' => $item['purchase_price'],
                        'selling_price' => $item['selling_price'],
                        'stock' => $item['stock'],
                        'min_stock' => $item['min_stock'] ?? 5,
                    ]
                );
            }
        }
    }
}
