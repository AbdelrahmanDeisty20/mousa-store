<?php

namespace App\Filament\Resources\OfferResource\Pages;

use App\Events\OfferCreatedForCartItems;
use App\Events\OfferCreatedForFavorites;
use App\Filament\Resources\OfferResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateOffer extends CreateRecord
{
    protected static string $resource = OfferResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['discount'] = $data['discount_price'] ?? 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        Cache::flush();
        OfferCreatedForCartItems::dispatch($this->record);
        OfferCreatedForFavorites::dispatch($this->record);
    }
}
