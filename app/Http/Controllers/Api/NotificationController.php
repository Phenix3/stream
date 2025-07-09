<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Obtenir les notifications de l'utilisateur
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unread' => ['sometimes', 'boolean'],
            'type' => ['sometimes', 'string'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $query = $user->notifications();

        // Filtrer par statut lu/non lu
        if ($request->has('unread')) {
            if ($request->boolean('unread')) {
                $query->unread();
            } else {
                $query->read();
            }
        }

        // Filtrer par type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        $totalCount = $query->count();
        $notifications = $query->latest()
                              ->skip(($page - 1) * $limit)
                              ->take($limit)
                              ->get();

        $totalPages = ceil($totalCount / $limit);

        // Obtenir le nombre de notifications non lues
        $unreadCount = $user->notifications()->unread()->count();

        return ApiResponse::success([
            'notifications' => NotificationResource::collection($notifications),
            'totalCount' => $totalCount,
            'unreadCount' => $unreadCount,
            'page' => (int) $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return ApiResponse::notFound('Notification non trouvée');
        }

        $notification->markAsRead();

        return ApiResponse::success([
            'notification' => new NotificationResource($notification),
        ], 'Notification marquée comme lue');
    }

    /**
     * Marquer une notification comme non lue
     */
    public function markAsUnread(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return ApiResponse::notFound('Notification non trouvée');
        }

        $notification->markAsUnread();

        return ApiResponse::success([
            'notification' => new NotificationResource($notification),
        ], 'Notification marquée comme non lue');
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $updatedCount = Notification::markAllAsReadForUser($user->id);

        return ApiResponse::success([
            'updatedCount' => $updatedCount,
        ], 'Toutes les notifications ont été marquées comme lues');
    }

    /**
     * Obtenir une notification spécifique
     */
    public function show(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return ApiResponse::notFound('Notification non trouvée');
        }

        // Marquer automatiquement comme lue si elle ne l'est pas
        if (!$notification->isRead()) {
            $notification->markAsRead();
        }

        return ApiResponse::success([
            'notification' => new NotificationResource($notification),
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return ApiResponse::notFound('Notification non trouvée');
        }

        $notification->delete();

        return ApiResponse::success(null, 'Notification supprimée avec succès');
    }

    /**
     * Supprimer toutes les notifications lues
     */
    public function clearRead(Request $request)
    {
        $user = $request->user();
        $deletedCount = $user->notifications()->read()->delete();

        return ApiResponse::success([
            'deletedCount' => $deletedCount,
        ], 'Toutes les notifications lues ont été supprimées');
    }

    /**
     * Supprimer toutes les notifications
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $deletedCount = $user->notifications()->delete();

        return ApiResponse::success([
            'deletedCount' => $deletedCount,
        ], 'Toutes les notifications ont été supprimées');
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $unreadCount = $user->notifications()->unread()->count();

        return ApiResponse::success([
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Créer une nouvelle notification (généralement appelé par le système)
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string'],
            'data' => ['sometimes', 'array'],
            'priority' => ['sometimes', 'string', 'in:low,normal,high,urgent'],
            'action_url' => ['sometimes', 'string', 'url'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();

        $notification = Notification::createNotification(
            $user->id,
            $request->title,
            $request->message,
            $request->get('type', Notification::TYPE_SYSTEM),
            $request->get('data', []),
            $request->get('priority', Notification::PRIORITY_NORMAL)
        );

        if ($request->has('action_url')) {
            $notification->update(['action_url' => $request->action_url]);
        }

        return ApiResponse::success([
            'notification' => new NotificationResource($notification),
        ], 'Notification créée avec succès', 201);
    }

    /**
     * Obtenir les statistiques des notifications
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->unread()->count(),
            'read' => $user->notifications()->read()->count(),
            'byType' => [],
            'byPriority' => [],
            'thisWeek' => $user->notifications()->recent(7)->count(),
            'thisMonth' => $user->notifications()->recent(30)->count(),
        ];

        // Statistiques par type
        $types = [
            Notification::TYPE_NEW_VIDEO,
            Notification::TYPE_DOWNLOAD_READY,
            Notification::TYPE_SYSTEM,
            Notification::TYPE_PROMOTION,
            Notification::TYPE_UPDATE,
            Notification::TYPE_REMINDER,
            Notification::TYPE_SOCIAL,
            Notification::TYPE_SECURITY,
        ];

        foreach ($types as $type) {
            $stats['byType'][$type] = $user->notifications()->byType($type)->count();
        }

        // Statistiques par priorité
        $priorities = [
            Notification::PRIORITY_LOW,
            Notification::PRIORITY_NORMAL,
            Notification::PRIORITY_HIGH,
            Notification::PRIORITY_URGENT,
        ];

        foreach ($priorities as $priority) {
            $stats['byPriority'][$priority] = $user->notifications()->byPriority($priority)->count();
        }

        return ApiResponse::success($stats);
    }
} 