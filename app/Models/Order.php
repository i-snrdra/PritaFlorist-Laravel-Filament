<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'type',
        'nama',
        'nama_pria',
        'nama_wanita',
        'inisial',
        'package_id',
        'tanggal_acara',
        'tanggal_pasang',
        'tanggal_bongkar',
        'tanggal_booking',
        'total_harga',
        'status'
    ];

    protected $casts = [
        'tanggal_acara' => 'date',
        'tanggal_pasang' => 'date',
        'tanggal_bongkar' => 'date',
        'tanggal_booking' => 'date',
        'total_harga' => 'decimal:2'
    ];

    // Relationships
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function orderExtras(): HasMany
    {
        return $this->hasMany(OrderExtra::class);
    }

    public function orderVendors(): HasMany
    {
        return $this->hasMany(OrderVendor::class);
    }

    public function manifests(): HasMany
    {
        return $this->hasMany(Manifest::class);
    }

    // Mutators untuk kalkulasi total harga
    public function calculateTotalHarga(): void
    {
        $packagePrice = $this->package ? $this->package->price : 0;
        $extrasTotal = $this->orderExtras->sum('subtotal');
        $this->total_harga = $packagePrice + $extrasTotal;
        $this->save();
    }

    // Accessor untuk display nama
    public function getDisplayNameAttribute(): string
    {
        return match($this->type) {
            'ulang_tahun', 'studio_foto' => $this->nama ?? '',
            'lamaran', 'pernikahan' => ($this->nama_pria ?? '') . ' & ' . ($this->nama_wanita ?? ''),
            default => ''
        };
    }
}
