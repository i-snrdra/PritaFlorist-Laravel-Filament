<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManifestItem extends Model
{
    protected $fillable = [
        'manifest_id',
        'inventory_id',
        'qty',
        'qty_dikembalikan',
        'status'
    ];

    // Relationships
    public function manifest(): BelongsTo
    {
        return $this->belongsTo(Manifest::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
