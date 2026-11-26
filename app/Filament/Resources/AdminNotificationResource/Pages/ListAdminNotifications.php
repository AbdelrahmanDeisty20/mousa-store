<?php

namespace App\Filament\Resources\AdminNotificationResource\Pages;

use App\Filament\Resources\AdminNotificationResource;
use App\Models\FilamentDatabaseNotification;
use App\Models\Notification as UserNotification;
use App\Models\UserFcmToken;
use App\Service\FirebaseNotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ListAdminNotifications extends ListRecords
{
    protected static string $resource = AdminNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('broadcast')
                ->label(__('Send Broadcast Notification'))
                ->icon(Heroicon::OutlinedMegaphone)
                ->color('primary')
                ->schema([
                    TextInput::make('title')
                        ->label(__('Broadcast Title'))
                        ->required()
                        ->maxLength(255),

                    Textarea::make('body')
                        ->label(__('Broadcast Message'))
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    UserNotification::create([
                        'user_id' => null,
                        'title' => $data['title'],
                        'body' => $data['body'],
                        'type' => 'broadcast',
                        'is_read' => false,
                    ]);

                    $tokens = UserFcmToken::query()
                        ->whereNotNull('fcm_token')
                        ->pluck('fcm_token')
                        ->unique();

                    $sentCount = 0;
                    $firebase = app(FirebaseNotificationService::class);
                    if ($tokens->isNotEmpty() && $firebase->isConfigured()) {
                        foreach ($tokens as $token) {
                            try {
                                $firebase->sendToToken($token, $data['title'], $data['body'], ['type' => 'broadcast']);
                                $sentCount++;
                            } catch (\Throwable $e) {
                                Log::error('FCM Broadcast Error: ' . $e->getMessage());
                            }
                        }
                    }

                    FilamentNotification::make()
                        ->title(__('Broadcast notification sent successfully'))
                        ->body(__('Saved to app notifications and pushed to :count device(s).', ['count' => $sentCount]))
                        ->success()
                        ->send();
                }),

            Action::make('markAllAsRead')
                ->label(__('Mark All as Read'))
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->action(function (): void {
                    if ($userId = Auth::id()) {
                        UserNotification::where('user_id', $userId)
                            ->where('is_read', false)
                            ->update(['is_read' => true]);

                        FilamentDatabaseNotification::query()
                            ->where('notifiable_id', $userId)
                            ->whereNull('read_at')
                            ->update(['read_at' => now()]);
                    }

                    FilamentNotification::make()
                        ->title(__('All notifications marked as read'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
