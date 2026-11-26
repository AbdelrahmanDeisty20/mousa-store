<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Exports\ProductExport;
use App\Filament\Resources\ProductResource;
use App\Imports\ProductImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('importExcel')
                ->label(__('Import Excel'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('info')
                ->schema([
                    FileUpload::make('excel_file')
                        ->label(__('Excel / CSV File'))
                        ->disk('local')
                        ->directory('temp_imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            '.xlsx',
                            '.xls',
                            '.csv',
                        ])
                        ->required(),

                    FileUpload::make('zip_file')
                        ->label(__('Images ZIP Archive (Optional)'))
                        ->disk('local')
                        ->directory('temp_imports')
                        ->acceptedFileTypes(['application/zip', '.zip']),
                ])
                ->action(function (array $data): void {
                    $extractedPath = null;
                    $excelRelative = $data['excel_file'] ?? null;
                    $zipRelative = $data['zip_file'] ?? null;

                    try {
                        if ($zipRelative && Storage::disk('local')->exists($zipRelative)) {
                            $zip = new ZipArchive;
                            if ($zip->open(Storage::disk('local')->path($zipRelative)) === true) {
                                $extractedPath = storage_path('app/temp_imports/extracted_' . uniqid());
                                File::ensureDirectoryExists($extractedPath);
                                $zip->extractTo($extractedPath);
                                $zip->close();
                            }
                        }

                        Excel::import(
                            new ProductImport($extractedPath),
                            Storage::disk('local')->path($excelRelative)
                        );

                        Cache::flush();

                        Notification::make()
                            ->title(__('Products imported successfully!'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title(__('Error importing products'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if ($excelRelative) {
                            Storage::disk('local')->delete($excelRelative);
                        }
                        if ($zipRelative) {
                            Storage::disk('local')->delete($zipRelative);
                        }
                        if ($extractedPath && File::isDirectory($extractedPath)) {
                            File::deleteDirectory($extractedPath);
                        }
                    }
                }),

            Action::make('exportExcel')
                ->label(__('Export Excel'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('success')
                ->action(fn () => Excel::download(
                    new ProductExport,
                    'products_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
                )),
        ];
    }
}
