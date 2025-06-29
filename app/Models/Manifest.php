<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Manifest extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'tanggal_manifest',
        'keterangan',
        'status'
    ];

    protected $casts = [
        'tanggal_manifest' => 'date'
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function manifestItems(): HasMany
    {
        return $this->hasMany(ManifestItem::class);
    }

    public function manifestCrews(): HasMany
    {
        return $this->hasMany(ManifestCrew::class);
    }

    public function crews(): BelongsToMany
    {
        return $this->belongsToMany(Crew::class, 'manifest_crews');
    }

    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'manifest_items')
            ->withPivot(['qty', 'qty_dikembalikan', 'status']);
    }
}
