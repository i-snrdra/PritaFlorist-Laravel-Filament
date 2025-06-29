<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Keperluan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->required()
                    ->numeric()
                    ->prefix('Rp '),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->displayFormat('d-m-Y')
                    ->format('d-m-Y'),                
                Forms\Components\TextInput::make('responsible_person')
                    ->label('Dikeluarkan Oleh')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Keperluan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),                
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsible_person')
                    ->label('Dikeluarkan Oleh')
                    ->sortable(),   
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('responsible_person')
                    ->options(Expense::all()->pluck('responsible_person', 'responsible_person')),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
