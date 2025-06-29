<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ulang_tahun', 'lamaran', 'pernikahan', 'studio_foto']);
            
            // Field untuk semua jenis order
            $table->string('nama')->nullable(); // Untuk Ulang Tahun & Studio Foto
            $table->string('nama_pria')->nullable(); // Untuk Lamaran & Pernikahan
            $table->string('nama_wanita')->nullable(); // Untuk Lamaran & Pernikahan
            $table->string('inisial')->nullable(); // Untuk Lamaran & Pernikahan
            
            // Paket
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            
            // Tanggal
            $table->date('tanggal_acara')->nullable(); // Semua kecuali Studio Foto
            $table->date('tanggal_pasang')->nullable(); // Semua kecuali Studio Foto
            $table->date('tanggal_bongkar')->nullable(); // Semua kecuali Studio Foto
            $table->date('tanggal_booking')->nullable(); // Khusus Studio Foto
            
            // Harga dan Status
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->enum('status', ['dp', 'proses', 'selesai'])->default('dp');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
