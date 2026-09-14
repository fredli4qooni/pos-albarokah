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
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
