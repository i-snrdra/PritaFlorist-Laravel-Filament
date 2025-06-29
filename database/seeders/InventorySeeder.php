<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventory;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inventory::create([
            'name' => 'Bunga Putih ',
            'stock' => 1000,
        ]);

        Inventory::create([
            'name' => 'Bunga Merah',
            'stock' => 1000,
        ]);

        Inventory::create([
            'name' => 'Bunga Kuning',
            'stock' => 600,
        ]);

        Inventory::create([
            'name' => 'Bunga Hijau',
            'stock' => 600,
        ]);
        Inventory::create([
            'name' => 'Lampu Kotak 30W',
            'stock' => 15,
        ]);
        Inventory::create([
            'name' => 'Lampu Kotak 60W',
            'stock' => 15,
        ]);
        Inventory::create([
            'name' => 'Lampu Kotak 100W',
            'stock' => 5,
        ]);
        Inventory::create([
            'name' => 'Lampu Kotak 150W',
            'stock' => 2,
        ]);
        Inventory::create([
            'name' => 'Lampu Kotak 200W',
            'stock' => 1,
        ]);
    }
}
