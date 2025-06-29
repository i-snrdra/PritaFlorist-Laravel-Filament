<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today();
        $lastMonth = Carbon::today()->subMonth();

        // Expense bulan ini
        Expense::create([
            'name' => 'Pembelian Bunga Putih dan Merah',
            'amount' => 250000,
            'date' => $today,
            'responsible_person' => 'Latifa',
        ]);

        Expense::create([
            'name' => 'Pembayaran Listrik Kantor',
            'amount' => 450000,
            'date' => $today,
            'responsible_person' => 'Bima',
        ]);

        Expense::create([
            'name' => 'Pembelian Lampu Kotak 100W dan 150W',
            'amount' => 180000,
            'date' => $today,
            'responsible_person' => 'Latifa',
        ]);

        // Expense bulan lalu
        Expense::create([
            'name' => 'Pembayaran Internet/WiFi',
            'amount' => 350000,
            'date' => $lastMonth,
            'responsible_person' => 'Bima',
        ]);

        Expense::create([
            'name' => 'Pembelian Bunga Kuning dan Hijau',
            'amount' => 320000,
            'date' => $lastMonth,
            'responsible_person' => 'Latifa',
        ]);

        Expense::create([
            'name' => 'Pembayaran Air PDAM',
            'amount' => 125000,
            'date' => $lastMonth,
            'responsible_person' => 'Bima',
        ]);
    }
}
