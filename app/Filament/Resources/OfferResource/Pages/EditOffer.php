<?php

namespace App\Filament\Resources\OfferResource\Pages;

use App\Events\OfferCreatedForCartItems;
use App\Events\OfferCreatedForFavorites;
use App\Filament\Resources\OfferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditOffer extends EditRecord
{
    protected static string $resource = OfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->after(fn () => Cache::flush()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['discount'] = $data['discount_price'] ?? 0;

        return $data;
    }

    protected function afterSave(): void
    {
        Cache::flush();
        OfferCreatedForCartItems::dispatch($this->record);
        OfferCreatedForFavorites::dispatch($this->record);
    }
}
