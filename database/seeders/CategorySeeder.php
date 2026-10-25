<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Herbisida',
                'slug' => 'herbisida',
                'description' => 'Pestisida pengendali gulma atau rumput liar pada lahan tanaman pertanian.',
            ],
            [
                'name' => 'Fungisida',
                'slug' => 'fungisida',
                'description' => 'Senyawa kimia untuk mengendalikan atau mencegah infeksi jamur pada tanaman.',
            ],
            [
                'name' => 'Insektisida',
                'slug' => 'insektisida',
                'description' => 'Obat pembasmi hama serangga seperti wereng, ulat, dan penggerek batang.',
            ],
            [
                'name' => 'Pupuk Kimia & Organik',
                'slug' => 'pupuk',
                'description' => 'Nutrisi tanaman makro dan mikro, baik berbentuk butiran, cairan, maupun pupuk organik.',
            ],
            [
                'name' => 'Benih Unggul',
                'slug' => 'benih',
                'description' => 'Benih padi, jagung, cabai, sayur mayur bersertifikasi dengan daya tumbuh tinggi.',
            ],
            [
                'name' => 'Alat & Perlengkapan Tani',
                'slug' => 'alat-tani',
                'description' => 'Alat semprot sprayer, nozzle, selang, dan perlengkapan pelindung petani.',
            ],
            [
                'name' => 'Bakterisida',
                'slug' => 'bakterisida',
                'description' => 'Bakterisida pertanian pengendali layu bakteri dan busuk basah tanaman.',
            ],
            [
                'name' => 'Moluskisida & Rodentisida',
                'slug' => 'moluskisida-rodentisida',
                'description' => 'Obat pengendali hama keong mas, siput, dan tikus sawah.',
            ],
            [
                'name' => 'Perekat & Adjuvan',
                'slug' => 'perekat-adjuvan',
                'description' => 'Bahan perekat, perata, dan penembus untuk efikasi semprot pestisida.',
            ],
            [
                'name' => 'ZPT & Biostimulan',
                'slug' => 'zpt-biostimulan',
                'description' => 'Zat pengatur tumbuh (ZPT) pemacu akar, tunas, bunga, dan buah.',
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
