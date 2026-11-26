<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

    protected function afterCreate(): void
    {
        Cache::flush();
    }
}
