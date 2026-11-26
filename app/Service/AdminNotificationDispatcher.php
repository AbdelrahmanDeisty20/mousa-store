<?php

namespace App\Service;

use App\Models\Notification as LegacyNotification;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class AdminNotificationDispatcher
{
    /**
     * Resolve all admin and sub-admin users eligible for dashboard alerts.
     *
     * @return Collection<int, User>
     */
    public static function recipients(): Collection
    {
        return User::query()
            ->where(function ($query): void {
                $query->where('user_type', 'admin')
                    ->orWhereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'subAdmin']));
            })
            ->with('fcmTokens')
            ->get();
    }

    /**
     * Dispatch a unified dashboard alert across:
     * 1. Legacy `notifications` table (admin resource + API compatibility)
     * 2. Filament topbar database bell drawer (`filament_notifications`)
     * 3. Firebase Cloud Messaging tokens (if configured on the admin device)
     *
     * @param  array<string, mixed>  $data
     */
    public static function notifyAdmins(
        string $title,
        string $body,
        string $type,
        array $data = [],
        ?string $actionLabel = null,
        ?string $actionUrl = null,
        string $status = 'info',
        string | BackedEnum | null $icon = null,
        bool $sendFcm = true,
    ): void {
        $admins = self::recipients();

        if ($admins->isEmpty()) {
            return;
        }

        $firebase = $sendFcm ? app(FirebaseNotificationService::class) : null;

        foreach ($admins as $admin) {
            LegacyNotification::create([
                'user_id' => $admin->id,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'data' => $data,
                'is_read' => false,
            ]);

            try {
                $filamentNotification = FilamentNotification::make()
                    ->title($title)
                    ->body($body)
                    ->{$status}();

                if ($icon !== null) {
                    $filamentNotification->icon($icon);
                }

                if ($actionLabel && $actionUrl) {
                    $filamentNotification->actions([
                        Action::make('open')
                            ->label($actionLabel)
                            ->url($actionUrl)
                            ->markAsRead(),
                    ]);
                }

                $filamentNotification->sendToDatabase($admin);
            } catch (\Throwable $e) {
                Log::warning("Filament DB notification failed for admin {$admin->id}: " . $e->getMessage());
            }

            if (! $firebase) {
                continue;
            }

            foreach ($admin->fcmTokens as $tokenRecord) {
                try {
                    $firebase->sendToToken($tokenRecord->fcm_token, $title, $body, array_merge($data, [
                        'type' => $type,
                    ]));
                } catch (\Throwable $e) {
                    Log::error("FCM Error (Admin {$admin->id}): " . $e->getMessage());
                }
            }
        }
    }
}
