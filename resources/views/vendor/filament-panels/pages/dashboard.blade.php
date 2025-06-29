<x-filament-panels::page class="fi-dashboard-page">
    @if (method_exists($this, 'filtersForm'))
        {{ $this->filtersForm }}
    @endif

    
    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pengeluaran</h2>
        @livewire(\App\Filament\Resources\ExpenseResource\Widgets\ExpenseSummary::class)
    </div>
    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Inventaris</h2>
        @livewire(\App\Filament\Resources\InventoryResource\Widgets\InventorySummary::class)
    </div>
</x-filament-panels::page>
