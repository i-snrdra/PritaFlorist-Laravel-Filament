<?php

namespace Database\Seeders;

use App\Models\Crew;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CrewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $crews = [
            [
                'name' => 'Sari Dewi',
                'phone' => '081234567890',
                'status' => 'active'
            ],
            [
                'name' => 'Budi Santoso',
                'phone' => '082345678901',
                'status' => 'active'
            ],
            [
                'name' => 'Rina Kartika',
                'phone' => '083456789012',
                'status' => 'active'
            ],
            [
                'name' => 'Ahmad Fauzi',
                'phone' => '084567890123',
                'status' => 'active'
            ],
            [
                'name' => 'Lestari Putri',
                'phone' => '085678901234',
                'status' => 'inactive'
            ],
            [
                'name' => 'Doni Setiawan',
                'phone' => '086789012345',
                'status' => 'active'
            ],
            [
                'name' => 'Maya Sari',
                'phone' => '087890123456',
                'status' => 'active'
            ],
            [
                'name' => 'Ricky Pratama',
                'phone' => '088901234567',
                'status' => 'active'
            ]
        ];

        foreach ($crews as $crew) {
            Crew::create($crew);
        }
    }
}
