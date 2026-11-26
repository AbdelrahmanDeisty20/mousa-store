<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Offer;
use App\Models\Property;
use App\Service\ProductHelper;
use BackedEnum;
use Carbon\Carbon;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Catalog');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Offers');
    }

    public static function getModelLabel(): string
    {
        return __('Offer');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Offers');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_offers');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_offers');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_offers');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_offers');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['variant.product', 'variant.variantAttributes']);
    }

    public static function formatVariantLabel(Property $variant): string
    {
        $productName = $variant->product?->name ?: $variant->product?->name_ar ?: "#{$variant->product_id}";
        $attrs = ProductHelper::getAttributesString($variant, app()->getLocale());
        $attrSuffix = $attrs ? " ({$attrs})" : '';

        return "{$productName}{$attrSuffix} - " . number_format((float) $variant->price, 2) . ' ' . __('EGP');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Offer Details'))
                ->columns(2)
                ->schema([
                    Select::make('property_id')
                        ->label(__('Product Variant'))
                        ->options(fn (): array => Property::with(['product', 'variantAttributes'])
                            ->get()
                            ->mapWithKeys(fn (Property $variant): array => [
                                $variant->id => self::formatVariantLabel($variant),
                            ])
                            ->all())
                        ->searchable()
                        ->required(),

                    TextInput::make('discount_price')
                        ->label(__('Discount Percentage (%)'))
                        ->helperText(__('Enter discount percentage (e.g. 25 for 25% off)'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(99)
                        ->step('1')
                        ->suffix('%')
                        ->required(),

                    DateTimePicker::make('start')
                        ->label(__('Start Date'))
                        ->required()
                        ->native(false)
                        ->rules([
                            fn (Get $get, ?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                                $propertyId = $get('property_id');
                                $end = $get('end');
                                if (! $propertyId || ! $value || ! $end) {
                                    return;
                                }

                                $startAt = Carbon::parse($value)->format('Y-m-d H:i:s');
                                $endAt = Carbon::parse($end)->format('Y-m-d H:i:s');

                                $overlap = Offer::query()
                                    ->where('property_id', $propertyId)
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->where('start', '<=', $endAt)
                                    ->where('end', '>=', $startAt)
                                    ->first();

                                if ($overlap) {
                                    $fail(__('Schedule overlap: An offer already exists in this period (:start - :end)', [
                                        'start' => $overlap->start,
                                        'end' => $overlap->end,
                                    ]));
                                }
                            },
                        ]),

                    DateTimePicker::make('end')
                        ->label(__('End Date'))
                        ->required()
                        ->afterOrEqual('start')
                        ->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('variant.product.name_ar')
                    ->label(__('Product'))
                    ->formatStateUsing(fn (Offer $record): string => $record->variant ? self::formatVariantLabel($record->variant) : '—')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('variant.product', fn ($q) => $q->where('name_ar', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%"))),

                TextColumn::make('discount_price')
                    ->label(__('Discount Percentage (%)'))
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn (mixed $state): string => '-' . rtrim(rtrim(number_format((float) $state, 2), '0'), '.') . '%')
                    ->sortable(),

                TextColumn::make('start')
                    ->label(__('Start Date'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('end')
                    ->label(__('End Date'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->after(fn () => Cache::flush()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->after(fn () => Cache::flush()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffers::route('/'),
            'create' => Pages\CreateOffer::route('/create'),
            'edit' => Pages\EditOffer::route('/{record}/edit'),
        ];
    }
}
