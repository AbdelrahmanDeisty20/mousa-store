<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All /admin dashboard routes are served by Filament (AdminPanelProvider).
| Public non-API/non-admin/non-storage paths fall through to the frontend SPA.
|
*/

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '^(?!api|admin|storage|livewire|filament).*$');
