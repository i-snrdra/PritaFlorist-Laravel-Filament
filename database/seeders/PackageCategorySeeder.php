<?php

namespace Database\Seeders;

use App\Models\PackageCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Lamaran'],
            ['name' => 'Resepsi/Pernikahan'],
            ['name' => 'Foto Studio'],
        ];

        foreach ($categories as $category) {
            PackageCategory::create($category);
        }
    }
}
