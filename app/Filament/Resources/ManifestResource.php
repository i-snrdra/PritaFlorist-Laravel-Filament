<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManifestResource\Pages;
use App\Filament\Resources\ManifestResource\RelationManagers;
use App\Models\Manifest;
use App\Models\Order;
use App\Models\Crew;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;

class ManifestResource extends Resource
{
    protected static ?string $model = Manifest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = 'Manifest';
    protected static ?string $navigationGroup = 'Order';
    protected static ?string $pluralModelLabel = 'Manifests';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'id', fn (Builder $query) => $query->where('type', '!=', 'studio_foto'))
                            ->getOptionLabelFromRecordUsing(fn (Order $record): string => 
                                "#{$record->id} - " . match($record->type) {
                                    'ulang_tahun' => $record->nama ?? '',
                                    'lamaran', 'pernikahan' => ($record->nama_pria ?? '') . ' & ' . ($record->nama_wanita ?? ''),
                                    default => ''
                                } . " ({$record->type})"
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\Select::make('type')
                            ->label('Tipe Manifest')
                            ->options([
                                'pasang' => 'Pasang',
                                'bongkar' => 'Bongkar',
                            ])
                            ->required(),
                            
                        Forms\Components\DatePicker::make('tanggal_manifest')
                            ->label('Tanggal Manifest')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'selesai' => 'Selesai',
                            ])
                            ->default('pending')
                            ->required(),
                            
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Crew Selection
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\CheckboxList::make('crew_ids')
                            ->label('Crew yang Ditugaskan')
                            ->relationship('crews', 'name')
                            ->options(Crew::where('status', 'active')->pluck('name', 'id'))
                            ->columns(2)
                            ->required(),
                    ])
                    ->heading('Crew'),

                // Inventory Items
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Repeater::make('manifestItems')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('inventory_id')
                                    ->label('Barang')
                                    ->relationship('inventory', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                    
                                Forms\Components\TextInput::make('qty')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1),
                                    
                                Forms\Components\TextInput::make('qty_dikembalikan')
                                    ->label('Qty Dikembalikan')
                                    ->numeric()
                                    ->default(0)
                                    ->visible(fn (Get $get) => $get('../../type') === 'bongkar'),
                                    
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'dipakai' => 'Dipakai',
                                        'dikembalikan' => 'Dikembalikan',
                                    ])
                                    ->default('dipakai')
                                    ->required(),
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->label('Barang yang Digunakan')
                            ->addActionLabel('Tambah Barang'),
                    ])
                    ->heading('Barang'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order ID')
                    ->prefix('#')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('order_display_name')
                    ->label('Nama Order')
                    ->getStateUsing(function (Manifest $record) {
                        $order = $record->order;
                        return match($order->type) {
                            'ulang_tahun' => $order->nama ?? '',
                            'lamaran', 'pernikahan' => ($order->nama_pria ?? '') . ' & ' . ($order->nama_wanita ?? ''),
                            default => ''
                        };
                    }),
                    
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pasang' => 'success',
                        'bongkar' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pasang' => 'Pasang',
                        'bongkar' => 'Bongkar',
                    }),
                    
                Tables\Columns\TextColumn::make('tanggal_manifest')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('crews_count')
                    ->label('Jumlah Crew')
                    ->counts('crews')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('manifest_items_count')
                    ->label('Jumlah Barang')
                    ->counts('manifestItems')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'selesai' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
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
                        'pasang' => 'Pasang',
                        'bongkar' => 'Bongkar',
                    ]),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'selesai' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListManifests::route('/'),
            'create' => Pages\CreateManifest::route('/create'),
            'edit' => Pages\EditManifest::route('/{record}/edit'),
        ];
    }
}
