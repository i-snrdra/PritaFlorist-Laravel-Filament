<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil kategori yang sudah dibuat
        $lamaranCategory = PackageCategory::where('name', 'Lamaran')->first();
        $resepsiCategory = PackageCategory::where('name', 'Resepsi/Pernikahan')->first();
        $fotoStudioCategory = PackageCategory::where('name', 'Foto Studio')->first();

        $packages = [
            // Paket Lamaran
            [
                'name' => 'Paket Lamaran Elegan',
                'price' => 2500000.00,
                'package_category_id' => $lamaranCategory->id
            ],
            [
                'name' => 'Paket Lamaran Tradisional',
                'price' => 3500000.00,
                'package_category_id' => $lamaranCategory->id
            ],
            [
                'name' => 'Paket Lamaran Premium',
                'price' => 5000000.00,
                'package_category_id' => $lamaranCategory->id
            ],
            [
                'name' => 'Paket Lamaran Sederhana',
                'price' => 1500000.00,
                'package_category_id' => $lamaranCategory->id
            ],

            // Paket Resepsi/Pernikahan
            [
                'name' => 'Paket Pernikahan Sakura',
                'price' => 15000000.00,
                'package_category_id' => $resepsiCategory->id
            ],
            [
                'name' => 'Paket Pernikahan Mawar Putih',
                'price' => 12000000.00,
                'package_category_id' => $resepsiCategory->id
            ],
            [
                'name' => 'Paket Pernikahan Garden Party',
                'price' => 18000000.00,
                'package_category_id' => $resepsiCategory->id
            ],
            [
                'name' => 'Paket Pernikahan Klasik',
                'price' => 10000000.00,
                'package_category_id' => $resepsiCategory->id
            ],
            [
                'name' => 'Paket Pernikahan Mewah',
                'price' => 25000000.00,
                'package_category_id' => $resepsiCategory->id
            ],

            // Paket Foto Studio
            [
                'name' => 'Paket Foto Pre-Wedding',
                'price' => 3000000.00,
                'package_category_id' => $fotoStudioCategory->id
            ],
            [
                'name' => 'Paket Foto Keluarga',
                'price' => 1500000.00,
                'package_category_id' => $fotoStudioCategory->id
            ],
            [
                'name' => 'Paket Foto Maternity',
                'price' => 2000000.00,
                'package_category_id' => $fotoStudioCategory->id
            ],
            [
                'name' => 'Paket Foto Birthday',
                'price' => 1800000.00,
                'package_category_id' => $fotoStudioCategory->id
            ],
            [
                'name' => 'Paket Foto Graduation',
                'price' => 1200000.00,
                'package_category_id' => $fotoStudioCategory->id
            ]
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}
