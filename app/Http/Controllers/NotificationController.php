<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $perPage = $request->get('per_page', 20);
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'unread_count' => Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->count(),
        ]);
    }

    /**
     * Mark a notification as unread
     */
    public function markAsUnread(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->update([
            'read' => false,
            'read_at' => null,
        ]);

        return response()->json([
            'message' => 'Notification marked as unread',
            'unread_count' => Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->count(),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'message' => 'All notifications marked as read',
            'unread_count' => 0,
        ]);
    }

    /**
     * Get unread notification count
     */
    public function count(): JsonResponse
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted',
            'unread_count' => Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->count(),
        ]);
    }
}
