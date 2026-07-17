<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Send a notification to specific users.
     */
    public function send(array $data, array $userIds): Notification
    {
        return DB::transaction(function () use ($data, $userIds) {
            $notification = Notification::create([
                'title' => $data['title'],
                'message' => $data['message'],
                'module' => $data['module'],
                'type' => $data['type'] ?? 'info',
                'priority' => $data['priority'] ?? 'normal',
                'action_url' => $data['action_url'] ?? null,
                'related_model' => $data['related_model'] ?? null,
                'related_id' => $data['related_id'] ?? null,
                'created_by' => $data['created_by'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            $recipients = array_map(fn($userId) => [
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ], array_unique($userIds));

            DB::table('notification_recipients')->insert($recipients);

            return $notification;
        });
    }

    /**
     * Send a notification to all users with a given role.
     */
    public function sendToRole(array $data, string $roleName): Notification
    {
        $userIds = $this->getUserIdsByRole($roleName);

        if (empty($userIds)) {
            return $this->sendQuietly($data);
        }

        return $this->send($data, $userIds);
    }

    /**
     * Send a notification to all admin users.
     */
    public function sendToAdmins(array $data): Notification
    {
        return $this->sendToRole($data, 'admin');
    }

    /**
     * Send a notification to users who have a specific permission.
     */
    public function sendToPermission(array $data, string $permission): Notification
    {
        $userIds = $this->getUserIdsByPermission($permission);

        if (empty($userIds)) {
            return $this->sendQuietly($data);
        }

        return $this->send($data, $userIds);
    }

    /**
     * Create a notification without recipients (fire-and-forget placeholder).
     */
    public function sendQuietly(array $data): Notification
    {
        return Notification::create([
            'title' => $data['title'],
            'message' => $data['message'],
            'module' => $data['module'],
            'type' => $data['type'] ?? 'info',
            'priority' => $data['priority'] ?? 'normal',
            'action_url' => $data['action_url'] ?? null,
            'related_model' => $data['related_model'] ?? null,
            'related_id' => $data['related_id'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Mark a single notification as read for a user.
     */
    public function markAsRead(int $notificationId, int $userId): void
    {
        NotificationRecipient::where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): void
    {
        NotificationRecipient::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Delete a notification for a user (removes recipient record).
     */
    public function deleteForUser(int $notificationId, int $userId): void
    {
        NotificationRecipient::where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return NotificationRecipient::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get notifications for a user with pagination and filters.
     */
    public function getUserNotifications(int $userId, array $filters = [])
    {
        $query = Notification::select('notifications.*')
            ->join('notification_recipients', 'notifications.id', '=', 'notification_recipients.notification_id')
            ->where('notification_recipients.user_id', $userId)
            ->with('creator:id,name');

        if (!empty($filters['module'])) {
            $query->where('notifications.module', $filters['module']);
        }

        if (!empty($filters['type'])) {
            $query->where('notifications.type', $filters['type']);
        }

        if (!empty($filters['priority'])) {
            $query->where('notifications.priority', $filters['priority']);
        }

        if (isset($filters['unread']) && $filters['unread'] === true) {
            $query->whereNull('notification_recipients.read_at');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('notifications.title', 'LIKE', "%{$search}%")
                  ->orWhere('notifications.message', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderByDesc('notifications.created_at')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get recent notifications for the bell dropdown.
     * Optimized: reads read_at from the JOIN instead of N+1 queries.
     */
    public function getRecentForUser(int $userId, int $limit = 15)
    {
        $notifications = Notification::select('notifications.*', 'notification_recipients.read_at as recipient_read_at')
            ->join('notification_recipients', 'notifications.id', '=', 'notification_recipients.notification_id')
            ->where('notification_recipients.user_id', $userId)
            ->with('creator:id,name')
            ->orderByDesc('notifications.created_at')
            ->limit($limit)
            ->get();

        return $notifications->map(function ($notification) {
            $notification->is_read = $notification->recipient_read_at !== null;
            unset($notification->recipient_read_at);
            return $notification;
        });
    }

    /**
     * Get recent notifications and unread count in a single query.
     */
    public function getRecentWithCount(int $userId, int $limit = 15): array
    {
        $notifications = $this->getRecentForUser($userId, $limit);
        $unreadCount = $this->getUnreadCount($userId);

        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ];
    }

    /**
     * Resolve user IDs for a given role, with short-lived cache.
     */
    private function getUserIdsByRole(string $roleName): array
    {
        $cacheKey = "notif:role:{$roleName}";

        return Cache::remember($cacheKey, 60, function () use ($roleName) {
            return User::whereHas('roles', fn($q) => $q->where('name', $roleName))
                ->pluck('id')
                ->toArray();
        });
    }

    /**
     * Resolve user IDs for a given permission, with short-lived cache.
     */
    private function getUserIdsByPermission(string $permission): array
    {
        $cacheKey = "notif:perm:{$permission}";

        return Cache::remember($cacheKey, 60, function () use ($permission) {
            return User::whereHas('roles', function ($q) use ($permission) {
                $q->whereHas('permissions', fn($pq) => $pq->where('name', $permission));
            })->pluck('id')->toArray();
        });
    }

    /**
     * Clear permission/role caches. Call after role/permission changes.
     */
    public function clearPermissionCaches(): void
    {
        $keys = Cache::get('notif:cache_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget('notif:cache_keys');
    }
}
