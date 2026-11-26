<?php

namespace App\Filament\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminAccess
{
    public static function user(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public static function isFullAdmin(?User $user = null): bool
    {
        $user ??= self::user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->user_type === 'admin' && ! $user->hasRole('subAdmin');
    }

    public static function isSubAdmin(?User $user = null): bool
    {
        $user ??= self::user();

        return $user ? $user->hasRole('subAdmin') : false;
    }

    public static function can(string $permission, bool $allowSubAdminByDefault = true): bool
    {
        $user = self::user();

        if (! $user) {
            return false;
        }

        if (self::isFullAdmin($user)) {
            return true;
        }

        if (! self::isSubAdmin($user)) {
            return false;
        }

        if ($user->hasPermissionTo($permission, 'web')) {
            return true;
        }

        // Preserve legacy subAdmin restrictions unless permission was explicitly granted
        $restrictedForSubAdmin = ['delete_users', 'delete_orders', 'edit_contacts'];
        if (in_array($permission, $restrictedForSubAdmin, true)) {
            return false;
        }

        // If subAdmin has no granular permissions attached at all, allow standard dashboard operations
        return $allowSubAdminByDefault && $user->getAllPermissions()->isEmpty();
    }
}
