<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Crew;
use App\Models\Manifest;
use App\Models\ManifestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create sample data
        $category = PackageCategory::create(['name' => 'Test Category']);
        $this->package = Package::create([
            'name' => 'Test Package',
            'price' => 1000000,
            'package_category_id' => $category->id
        ]);
        
        $this->inventory = Inventory::create([
            'name' => 'Test Bunga',
            'stock' => 100
        ]);
        
        $this->order = Order::create([
            'type' => 'lamaran',
            'nama_pria' => 'John',
            'nama_wanita' => 'Jane',
            'inisial' => 'JJ',
            'package_id' => $this->package->id,
            'tanggal_acara' => now()->addDays(7),
            'tanggal_pasang' => now()->addDays(6),
            'tanggal_bongkar' => now()->addDays(8),
            'total_harga' => 1000000,
            'status' => 'dp'
        ]);
        
        $this->manifest = Manifest::create([
            'order_id' => $this->order->id,
            'type' => 'pasang',
            'tanggal_manifest' => now(),
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_reduces_inventory_stock_when_manifest_item_is_created()
    {
        $initialStock = $this->inventory->stock;
        $qtyUsed = 10;

        // Create manifest item (barang digunakan)
        ManifestItem::create([
            'manifest_id' => $this->manifest->id,
            'inventory_id' => $this->inventory->id,
            'qty' => $qtyUsed,
            'status' => 'dipakai'
        ]);

        // Refresh inventory
        $this->inventory->refresh();

        // Assert stock is reduced
        $this->assertEquals($initialStock - $qtyUsed, $this->inventory->stock);
    }

    /** @test */
    public function it_restores_inventory_stock_when_status_changed_to_dikembalikan()
    {
        $initialStock = $this->inventory->stock;
        $qtyUsed = 15;
        $qtyReturned = 12;

        // Create manifest item
        $manifestItem = ManifestItem::create([
            'manifest_id' => $this->manifest->id,
            'inventory_id' => $this->inventory->id,
            'qty' => $qtyUsed,
            'status' => 'dipakai'
        ]);

        // Check stock is reduced
        $this->inventory->refresh();
        $this->assertEquals($initialStock - $qtyUsed, $this->inventory->stock);

        // Change status to dikembalikan
        $manifestItem->update([
            'status' => 'dikembalikan',
            'qty_dikembalikan' => $qtyReturned
        ]);

        // Check stock is restored partially
        $this->inventory->refresh();
        $expectedStock = $initialStock - $qtyUsed + $qtyReturned;
        $this->assertEquals($expectedStock, $this->inventory->stock);
    }

    /** @test */
    public function it_adjusts_inventory_stock_when_qty_is_changed()
    {
        $initialStock = $this->inventory->stock;
        $initialQty = 10;
        $newQty = 15;

        // Create manifest item
        $manifestItem = ManifestItem::create([
            'manifest_id' => $this->manifest->id,
            'inventory_id' => $this->inventory->id,
            'qty' => $initialQty,
            'status' => 'dipakai'
        ]);

        // Check initial stock reduction
        $this->inventory->refresh();
        $this->assertEquals($initialStock - $initialQty, $this->inventory->stock);

        // Change quantity
        $manifestItem->update(['qty' => $newQty]);

        // Check stock is further reduced
        $this->inventory->refresh();
        $this->assertEquals($initialStock - $newQty, $this->inventory->stock);
    }

    /** @test */
    public function it_restores_inventory_stock_when_manifest_item_is_deleted()
    {
        $initialStock = $this->inventory->stock;
        $qtyUsed = 20;

        // Create manifest item
        $manifestItem = ManifestItem::create([
            'manifest_id' => $this->manifest->id,
            'inventory_id' => $this->inventory->id,
            'qty' => $qtyUsed,
            'status' => 'dipakai'
        ]);

        // Check stock is reduced
        $this->inventory->refresh();
        $this->assertEquals($initialStock - $qtyUsed, $this->inventory->stock);

        // Delete manifest item
        $manifestItem->delete();

        // Check stock is restored
        $this->inventory->refresh();
        $this->assertEquals($initialStock, $this->inventory->stock);
    }

    /** @test */
    public function it_handles_qty_dikembalikan_changes_correctly()
    {
        $initialStock = $this->inventory->stock;
        $qtyUsed = 20;
        $initialQtyReturned = 5;
        $newQtyReturned = 15;

        // Create manifest item and set as dikembalikan
        $manifestItem = ManifestItem::create([
            'manifest_id' => $this->manifest->id,
            'inventory_id' => $this->inventory->id,
            'qty' => $qtyUsed,
            'status' => 'dipakai'
        ]);

        // Change to dikembalikan
        $manifestItem->update([
            'status' => 'dikembalikan',
            'qty_dikembalikan' => $initialQtyReturned
        ]);

        // Check stock after partial return
        $this->inventory->refresh();
        $expectedStock = $initialStock - $qtyUsed + $initialQtyReturned;
        $this->assertEquals($expectedStock, $this->inventory->stock);

        // Increase qty_dikembalikan
        $manifestItem->update(['qty_dikembalikan' => $newQtyReturned]);

        // Check stock is further restored
        $this->inventory->refresh();
        $finalExpectedStock = $initialStock - $qtyUsed + $newQtyReturned;
        $this->assertEquals($finalExpectedStock, $this->inventory->stock);
    }

    /** @test */
    public function it_handles_multiple_manifest_items_correctly()
    {
        $initialStock = $this->inventory->stock;

        // Create multiple manifest items
        $manifestItem1 = ManifestItem::create([
            'manifest_id' => $this->manifest->id,
            'inventory_id' => $this->inventory->id,
            'qty' => 10,
            'status' => 'dipakai'
        ]);

        $manifestItem2 = ManifestItem::create([
            'manifest_id' => $this->manifest->id,
            'inventory_id' => $this->inventory->id,
            'qty' => 15,
            'status' => 'dipakai'
        ]);

        // Check total stock reduction
        $this->inventory->refresh();
        $this->assertEquals($initialStock - 25, $this->inventory->stock);

        // Return one item partially
        $manifestItem1->update([
            'status' => 'dikembalikan',
            'qty_dikembalikan' => 8
        ]);

        // Check stock
        $this->inventory->refresh();
        $this->assertEquals($initialStock - 25 + 8, $this->inventory->stock);

        // Delete second item (should restore its qty)
        $manifestItem2->delete();

        // Check final stock
        $this->inventory->refresh();
        $this->assertEquals($initialStock - 10 + 8, $this->inventory->stock);
    }
}
