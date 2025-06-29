<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $navigationLabel = 'Order';
    
    protected static ?string $pluralModelLabel = 'Orders';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe Order')
                            ->options([
                                'ulang_tahun' => 'Ulang Tahun',
                                'lamaran' => 'Lamaran',
                                'pernikahan' => 'Pernikahan/Resepsi',
                                'studio_foto' => 'Studio Foto',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Reset semua field saat type berubah
                                $set('nama', null);
                                $set('nama_pria', null);
                                $set('nama_wanita', null);
                                $set('inisial', null);
                                $set('package_id', null);
                                $set('tanggal_acara', null);
                                $set('tanggal_pasang', null);
                                $set('tanggal_bongkar', null);
                                $set('tanggal_booking', null);
                            }),

                        // Field nama untuk Ulang Tahun & Studio Foto
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama')
                            ->required()
                            ->visible(fn (Get $get): bool => in_array($get('type'), ['ulang_tahun', 'studio_foto'])),

                        // Field nama untuk Lamaran & Pernikahan
                        Forms\Components\TextInput::make('nama_pria')
                            ->label('Nama Pria')
                            ->required()
                            ->visible(fn (Get $get): bool => in_array($get('type'), ['lamaran', 'pernikahan'])),
                            
                        Forms\Components\TextInput::make('nama_wanita')
                            ->label('Nama Wanita')
                            ->required()
                            ->visible(fn (Get $get): bool => in_array($get('type'), ['lamaran', 'pernikahan'])),
                            
                        Forms\Components\TextInput::make('inisial')
                            ->label('Inisial')
                            ->required()
                            ->visible(fn (Get $get): bool => in_array($get('type'), ['lamaran', 'pernikahan'])),

                        // Package selection berdasarkan type
                        Forms\Components\Select::make('package_id')
                            ->label('Paket')
                            ->relationship('package', 'name')
                            ->options(function (Get $get) {
                                $type = $get('type');
                                $categoryMap = [
                                    'ulang_tahun' => 'Foto Studio', // ID 3 sesuai seeder
                                    'lamaran' => 'Lamaran', // ID 1 sesuai seeder  
                                    'pernikahan' => 'Resepsi/Pernikahan', // ID 2 sesuai seeder
                                    'studio_foto' => 'Foto Studio', // ID 3 sesuai seeder
                                ];
                                
                                if (!$type || !isset($categoryMap[$type])) {
                                    return [];
                                }
                                
                                return Package::whereHas('packageCategory', function ($query) use ($categoryMap, $type) {
                                    $query->where('name', $categoryMap[$type]);
                                })->pluck('name', 'id');
                            })
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                self::calculateTotalHarga($set, $get);
                            })
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Card::make()
                    ->schema([
                        // Tanggal untuk non-Studio Foto
                        Forms\Components\DatePicker::make('tanggal_acara')
                            ->label('Tanggal Acara')
                            ->required()
                            ->visible(fn (Get $get): bool => $get('type') !== 'studio_foto'),
                            
                        Forms\Components\DatePicker::make('tanggal_pasang')
                            ->label('Tanggal Pasang')
                            ->required()
                            ->visible(fn (Get $get): bool => $get('type') !== 'studio_foto'),
                            
                        Forms\Components\DatePicker::make('tanggal_bongkar')
                            ->label('Tanggal Bongkar')
                            ->required()
                            ->visible(fn (Get $get): bool => $get('type') !== 'studio_foto'),

                        // Tanggal untuk Studio Foto
                        Forms\Components\DatePicker::make('tanggal_booking')
                            ->label('Tanggal Booking')
                            ->required()
                            ->visible(fn (Get $get): bool => $get('type') === 'studio_foto'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'dp' => 'DP',
                                'proses' => 'Proses',
                                'selesai' => 'Selesai',
                            ])
                            ->default('dp')
                            ->required(),

                        Forms\Components\TextInput::make('total_harga')
                            ->label('Total Harga')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->reactive()
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '0'),
                    ])
                    ->columns(2),

                // Tambahan (OrderExtras)
                Forms\Components\Repeater::make('orderExtras')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('nama_tambahan')
                            ->label('Nama Tambahan')
                            ->required(),
                        Forms\Components\TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::calculateTotalHarga($set, $get);
                            }),
                        Forms\Components\TextInput::make('harga_satuan')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::calculateTotalHarga($set, $get);
                            }),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->label('Tambahan')
                    ->visible(fn (Get $get): bool => $get('type') !== 'studio_foto')
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        self::calculateTotalHarga($set, $get);
                    }),

                // Vendor
                Forms\Components\Repeater::make('orderVendors')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('nama_vendor')
                            ->label('Nama Vendor')
                            ->required(),
                    ])
                    ->simple(
                        Forms\Components\TextInput::make('nama_vendor')
                            ->label('Nama Vendor')
                            ->required()
                    )
                    ->collapsible()
                    ->label('Vendor')
                    ->visible(fn (Get $get): bool => $get('type') !== 'studio_foto'),
            ]);
    }

    protected static function calculateTotalHarga(Set $set, Get $get): void
    {
        $packageId = $get('package_id');
        $orderExtras = $get('orderExtras') ?? [];
        $type = $get('type');

        // Get package price
        $packagePrice = 0;
        if ($packageId) {
            $package = Package::find($packageId);
            $packagePrice = $package ? $package->price : 0;
        }

        // Calculate extras total (hanya untuk non-studio foto)
        $extrasTotal = 0;
        if ($type !== 'studio_foto' && !empty($orderExtras)) {
            foreach ($orderExtras as $extra) {
                $qty = (float) ($extra['qty'] ?? 0);
                $hargaSatuan = (float) ($extra['harga_satuan'] ?? 0);
                $extrasTotal += $qty * $hargaSatuan;
            }
        }

        // Calculate total
        $totalHarga = $packagePrice + $extrasTotal;
        
        // Set total harga
        $set('total_harga', $totalHarga);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ulang_tahun' => 'warning',
                        'lamaran' => 'info',
                        'pernikahan' => 'success',
                        'studio_foto' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ulang_tahun' => 'Ulang Tahun',
                        'lamaran' => 'Lamaran',
                        'pernikahan' => 'Pernikahan',
                        'studio_foto' => 'Studio Foto',
                    }),
                    
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Nama')
                    ->getStateUsing(function (Order $record) {
                        return match($record->type) {
                            'ulang_tahun', 'studio_foto' => $record->nama ?? '',
                            'lamaran', 'pernikahan' => ($record->nama_pria ?? '') . ' & ' . ($record->nama_wanita ?? ''),
                            default => ''
                        };
                    })
                    ->searchable(['nama', 'nama_pria', 'nama_wanita']),
                    
                Tables\Columns\TextColumn::make('package.name')
                    ->label('Paket')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('tanggal_acara')
                    ->label('Tanggal Acara')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('tanggal_booking')
                    ->label('Tanggal Booking')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dp' => 'warning',
                        'proses' => 'info',
                        'selesai' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dp' => 'DP',
                        'proses' => 'Proses',
                        'selesai' => 'Selesai',
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'ulang_tahun' => 'Ulang Tahun',
                        'lamaran' => 'Lamaran',
                        'pernikahan' => 'Pernikahan',
                        'studio_foto' => 'Studio Foto',
                    ]),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'dp' => 'DP',
                        'proses' => 'Proses',
                        'selesai' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('buat_manifest_pasang')
                    ->label('Manifest Pasang')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->color('success')
                    ->visible(fn (Order $record): bool => 
                        $record->type !== 'studio_foto' && 
                        !$record->manifests()->where('type', 'pasang')->exists()
                    )
                    ->url(fn (Order $record): string => '/admin/manifests/create?order_id=' . $record->id . '&type=pasang'),
                    
                Tables\Actions\Action::make('buat_manifest_bongkar')
                    ->label('Manifest Bongkar')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->color('warning')
                    ->visible(fn (Order $record): bool => 
                        $record->type !== 'studio_foto' && 
                        $record->manifests()->where('type', 'pasang')->exists() &&
                        !$record->manifests()->where('type', 'bongkar')->exists()
                    )
                    ->url(fn (Order $record): string => '/admin/manifests/create?order_id=' . $record->id . '&type=bongkar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
