<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Top Selling Products'))
            ->query(
                Product::query()
                    ->select(
                        'products.id',
                        'products.name_ar',
                        'products.name_en',
                        'products.category_id',
                        'products.brand_id',
                        DB::raw('SUM(orders_items.quantity) as total_qty'),
                        DB::raw('SUM(orders_items.quantity * orders_items.price) as revenue')
                    )
                    ->join('properties', 'products.id', '=', 'properties.product_id')
                    ->join('orders_items', 'properties.id', '=', 'orders_items.property_id')
                    ->groupBy(
                        'products.id',
                        'products.name_ar',
                        'products.name_en',
                        'products.category_id',
                        'products.brand_id'
                    )
            )
            ->columns([
                TextColumn::make('name_ar')
                    ->label(__('Product'))
                    ->formatStateUsing(fn (Product $record): string => $record->name ?: $record->name_ar ?: $record->name_en)
                    ->searchable(['name_ar', 'name_en']),

                TextColumn::make('total_qty')
                    ->label(__('Quantity Sold'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('revenue')
                    ->label(__('Revenue'))
                    ->money('EGP')
                    ->sortable(),
            ])
            ->defaultSort('total_qty', 'desc')
            ->paginated([5, 10]);
    }
}
