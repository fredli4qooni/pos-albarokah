<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Pak Supardi',
                'phone' => '081234567890',
                'address' => 'RT 02 / RW 01, Dusun Karang Anyar, Desa Sukamaju',
            ],
            [
                'name' => 'Pak Joko Sutrisno',
                'phone' => '082198765432',
                'address' => 'Blok Sawah Timur, Kelompok Tani Sumber Rejeki',
            ],
            [
                'name' => 'Haji Mansyur',
                'phone' => '081377889900',
                'address' => 'RT 04 / RW 02, Dusun Krajan (Dekat Masjid Baitul Makmur)',
            ],
            [
                'name' => 'Pak Wayan Sudirga',
                'phone' => '085211223344',
                'address' => 'Desa Tanjung Harapan, RT 01',
            ],
            [
                'name' => 'Pak Mulyono',
                'phone' => '087855667788',
                'address' => 'Gapoktan Makmur Jaya, Jl. Raya Usaha Tani No. 12',
            ],
        ];

        foreach ($customers as $c) {
            Customer::firstOrCreate(['name' => $c['name']], $c);
        }
    }
}
