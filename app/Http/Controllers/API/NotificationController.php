<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->paginatedNotifications(
            $request,
            function (Builder $query): void {
                $query->where(function (Builder $q): void {
                    $q->where('user_id', auth()->id())
                        ->orWhereNull('user_id');
                });
            },
            'there is no notifications right now',
            'received notifications successfully.'
        );
    }

    public function OrderNoti(Request $request): JsonResponse
    {
        return $this->paginatedNotifications(
            $request,
            function (Builder $query): void {
                $query->where('user_id', auth()->id())
                    ->whereIn('type', ['order', 'order_status']);
            },
            'there is no order notifications right now',
            'received order notifications successfully.'
        );
    }

    /**
     * Paginate notifications using the exact `{ current_page, data, last_page, ... }`
     * contract expected by the storefront notification store.
     */
    private function paginatedNotifications(
        Request $request,
        callable $scope,
        string $emptyMessage,
        string $successMessage
    ): JsonResponse {
        $limit = max(1, min(100, (int) ($request->input('limit', $request->input('per_page', 10)))));

        $query = Notification::query()
            ->select(['id', 'title', 'body', 'type', 'is_read', 'created_at', 'data']);

        $scope($query);

        $notifications = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => $notifications->isEmpty() ? $emptyMessage : $successMessage,
            'data' => $notifications,
        ], 200);
    }
}
