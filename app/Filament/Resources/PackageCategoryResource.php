<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageCategoryResource\Pages;
use App\Filament\Resources\PackageCategoryResource\RelationManagers;
use App\Models\PackageCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PackageCategoryResource extends Resource
{
    protected static ?string $model = PackageCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Kategori Paket';
    protected static ?string $pluralNavigationLabel = 'Kategori Paket';
    protected static ?string $navigationGroup = 'Paket';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPackageCategories::route('/'),
            'create' => Pages\CreatePackageCategory::route('/create'),
            'edit' => Pages\EditPackageCategory::route('/{record}/edit'),
        ];
    }
}
