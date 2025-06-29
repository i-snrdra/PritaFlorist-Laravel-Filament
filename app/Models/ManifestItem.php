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

    // Method untuk mengurangi stok inventory
    public function reduceInventoryStock(): void
    {
        if ($this->inventory && $this->status === 'dipakai') {
            $this->inventory->decrement('stock', $this->qty);
        }
    }

    // Method untuk mengembalikan stok inventory
    public function restoreInventoryStock(): void
    {
        if ($this->inventory) {
            $restoreQty = $this->status === 'dikembalikan' ? $this->qty_dikembalikan : $this->qty;
            $this->inventory->increment('stock', $restoreQty);
        }
    }

    // Method untuk adjust stok berdasarkan perubahan qty
    public function adjustInventoryStock(int $oldQty, int $newQty): void
    {
        if ($this->inventory && $this->status === 'dipakai') {
            $difference = $newQty - $oldQty;
            if ($difference > 0) {
                // Qty bertambah, kurangi stok lebih banyak
                $this->inventory->decrement('stock', $difference);
            } elseif ($difference < 0) {
                // Qty berkurang, kembalikan sebagian stok
                $this->inventory->increment('stock', abs($difference));
            }
        }
    }

    // Event listeners untuk auto inventory tracking
    protected static function booted()
    {
        // Saat ManifestItem dibuat (barang digunakan)
        static::created(function ($manifestItem) {
            $manifestItem->reduceInventoryStock();
        });

        // Saat ManifestItem diupdate
        static::updating(function ($manifestItem) {
            $original = $manifestItem->getOriginal();
            $inventory = $manifestItem->inventory;
            
            if (!$inventory) return;
            
            // Jika qty berubah dan status masih "dipakai"
            if ($manifestItem->isDirty('qty') && $manifestItem->status === 'dipakai') {
                $oldQty = $original['qty'] ?? 0;
                $newQty = $manifestItem->qty;
                $difference = $newQty - $oldQty;
                
                if ($difference > 0) {
                    $inventory->decrement('stock', $difference);
                } elseif ($difference < 0) {
                    $inventory->increment('stock', abs($difference));
                }
            }
            
            // Jika status berubah dari "dipakai" ke "dikembalikan"
            if ($manifestItem->isDirty('status')) {
                $oldStatus = $original['status'] ?? '';
                
                if ($oldStatus === 'dipakai' && $manifestItem->status === 'dikembalikan') {
                    // Kembalikan stok sesuai qty_dikembalikan
                    $returnQty = $manifestItem->qty_dikembalikan ?: $manifestItem->qty;
                    $inventory->increment('stock', $returnQty);
                } elseif ($oldStatus === 'dikembalikan' && $manifestItem->status === 'dipakai') {
                    // Kurangi stok lagi jika status dikembalikan ke "dipakai"
                    $inventory->decrement('stock', $manifestItem->qty);
                }
            }
            
            // Jika qty_dikembalikan berubah dan status "dikembalikan"
            if ($manifestItem->isDirty('qty_dikembalikan') && $manifestItem->status === 'dikembalikan') {
                $oldQtyDikembalikan = $original['qty_dikembalikan'] ?? 0;
                $newQtyDikembalikan = $manifestItem->qty_dikembalikan;
                $difference = $newQtyDikembalikan - $oldQtyDikembalikan;
                
                if ($difference > 0) {
                    $inventory->increment('stock', $difference);
                } elseif ($difference < 0) {
                    $inventory->decrement('stock', abs($difference));
                }
            }
        });

        // Saat ManifestItem dihapus
        static::deleting(function ($manifestItem) {
            // Kembalikan stok sesuai status
            if ($manifestItem->status === 'dipakai') {
                // Jika masih status "dipakai", kembalikan semua qty
                $manifestItem->inventory->increment('stock', $manifestItem->qty);
            } elseif ($manifestItem->status === 'dikembalikan') {
                // Jika sudah "dikembalikan", kembalikan sisa yang belum dikembalikan
                $sisaQty = $manifestItem->qty - $manifestItem->qty_dikembalikan;
                if ($sisaQty > 0) {
                    $manifestItem->inventory->increment('stock', $sisaQty);
                }
            }
        });
    }
}
