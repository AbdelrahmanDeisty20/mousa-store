<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()->getName();

        if ($routeName === 'orders.cancelOrder') {
            return $next($request);
        }

        // Bypass for Profile and Logout (Always allowed for any dashboard user)
        $bypassRoutes = [
            'dashboard.userProfile',
            'dashboard.profileUpdate',
            'dashboard.logout',
            'dashboard.notifications.index',
            'dashboard.notifications.fetch',
            'dashboard.notifications.markAsRead',
            'dashboard.notifications.markAllAsRead'
        ];
        if (in_array($routeName, $bypassRoutes)) {
            return $next($request);
        }

        // Hard-coded restrictions for subAdmin
        $user = $request->user();  // Get the authenticated user
        $permission = Permission::get(['id', 'name', 'routes'])
            ->first(fn ($perm) => in_array($routeName, array_map('trim', explode(',', (string) $perm->routes)), true));

        if ($user->hasRole('subAdmin')) {
            $restrictedPermissions = [
                // 'edit_users',  // Removed to allow subAdmin to edit basic info
                'delete_users',  // Block deleting users
                'delete_orders',  // Block deleting orders
                'edit_contacts',  // Block editing contacts
            ];

            if ($permission && in_array($permission->name, $restrictedPermissions, true)) {
                abort(403, 'Unauthorized action for Sub Admin.');
            }
        }

        // Original permission check for all users (including subAdmin for non-restricted actions)
        if ($permission && ! $user->can($permission->name)) {
            abort(403);
        }

        return $next($request);
    }
}
