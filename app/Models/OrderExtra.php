<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderExtra extends Model
{
    protected $fillable = [
        'order_id',
        'nama_tambahan',
        'qty',
        'harga_satuan',
        'subtotal'
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Mutator untuk menghitung subtotal otomatis
    protected static function booted()
    {
        static::saving(function ($orderExtra) {
            $orderExtra->subtotal = $orderExtra->qty * $orderExtra->harga_satuan;
        });
    }
}
