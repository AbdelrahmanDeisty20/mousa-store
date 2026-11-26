<?php

namespace App\Filament\Resources\AdminNotificationResource\Pages;

use App\Filament\Resources\AdminNotificationResource;
use App\Models\Notification as UserNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ListAdminNotifications extends ListRecords
{
    protected static string $resource = AdminNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAllAsRead')
                ->label(__('Mark All as Read'))
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->action(function (): void {
                    if ($userId = Auth::id()) {
                        UserNotification::where('user_id', $userId)
                            ->where('is_read', false)
                            ->update(['is_read' => true]);
                    }

                    FilamentNotification::make()
                        ->title(__('All notifications marked as read'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
