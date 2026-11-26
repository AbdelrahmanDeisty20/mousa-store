<?php

namespace App\Filament\Resources\ProductBannerResource\Pages;

use App\Filament\Resources\ProductBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditProductBanner extends EditRecord
{
    protected static string $resource = ProductBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->after(fn () => Cache::flush()),
        ];
    }

    protected function afterSave(): void
    {
        Cache::flush();
    }
}
