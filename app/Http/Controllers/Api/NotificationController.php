<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $service
    ) {}

    /**
     * GET /api/notifications
     * Paginated list with filters.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $notifications = $this->service->getUserNotifications($userId, [
            'module' => $request->module,
            'type' => $request->type,
            'priority' => $request->priority,
            'unread' => $request->boolean('unread'),
            'search' => $request->search,
            'per_page' => $request->per_page ?? 20,
        ]);

        return response()->json($notifications);
    }

    /**
     * GET /api/notifications/recent
     * Recent notifications for the bell dropdown.
     */
    public function recent()
    {
        $userId = Auth::id();
        $data = $this->service->getRecentWithCount($userId, 15);

        return response()->json($data);
    }

    /**
     * GET /api/notifications/unread-count
     */
    public function unreadCount()
    {
        $count = $this->service->getUnreadCount(Auth::id());
        return response()->json(['unread_count' => $count]);
    }

    /**
     * PUT /api/notifications/{notification}/read
     * Mark a single notification as read.
     */
    public function markAsRead(int $notificationId)
    {
        $this->service->markAsRead($notificationId, Auth::id());
        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * PUT /api/notifications/read-all
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $this->service->markAllAsRead(Auth::id());
        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * DELETE /api/notifications/{notification}
     * Delete a notification for the current user.
     */
    public function destroy(int $notificationId)
    {
        $this->service->deleteForUser($notificationId, Auth::id());
        return response()->json(['message' => 'Notification deleted']);
    }

    /**
     * GET /api/notifications/modules
     * Get list of modules that have notifications.
     */
    public function modules()
    {
        $userId = Auth::id();
        $modules = \App\Models\Notification::select('module')
            ->join('notification_recipients', 'notifications.id', '=', 'notification_recipients.notification_id')
            ->where('notification_recipients.user_id', $userId)
            ->distinct()
            ->pluck('module');

        return response()->json(['modules' => $modules]);
    }
}
