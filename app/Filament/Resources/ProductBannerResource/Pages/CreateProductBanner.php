<?php

namespace App\Filament\Resources\ProductBannerResource\Pages;

use App\Filament\Resources\ProductBannerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateProductBanner extends CreateRecord
{
    protected static string $resource = ProductBannerResource::class;

    protected function afterCreate(): void
    {
        Cache::flush();
    }
}
