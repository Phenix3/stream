<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\ActivityResource;
use App\Models\Video;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    /**
     * Obtenir l'activité de l'utilisateur
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['sometimes', 'string', 'in:like,watch,download,share,favorite'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $query = $user->activities()->with(['video.creator', 'video.genres']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $totalCount = $query->count();
        $activities = $query->orderBy('created_at', 'desc')
                           ->skip(($page - 1) * $limit)
                           ->take($limit)
                           ->get();

        $totalPages = ceil($totalCount / $limit);

        return ApiResponse::success([
            'activities' => ActivityResource::collection($activities),
            'totalCount' => $totalCount,
            'page' => (int) $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Enregistrer une nouvelle activité
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:like,watch,download,share,favorite'],
            'videoId' => ['required', 'integer', 'exists:videos,id'],
            'platform' => ['sometimes', 'string', 'in:whatsapp,facebook,twitter,instagram'],
            'data' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();
        $video = Video::find($request->videoId);

        if (!$video) {
            return ApiResponse::notFound('Vidéo non trouvée');
        }

        $activityData = [
            'user_id' => $user->id,
            'video_id' => $request->videoId,
            'type' => $request->type,
            'data' => $request->get('data', []),
        ];

        // Ajouter des données spécifiques selon le type d'activité
        switch ($request->type) {
            case 'share':
                $activityData['data']['platform'] = $request->get('platform', 'unknown');
                $activityData['description'] = "Partagé '{$video->title}' sur " . ($request->get('platform', 'une plateforme'));
                break;

            case 'like':
                $activityData['description'] = "A aimé '{$video->title}'";
                break;

            case 'watch':
                $activityData['description'] = "A regardé '{$video->title}'";
                break;

            case 'download':
                $activityData['description'] = "A téléchargé '{$video->title}'";
                break;

            case 'favorite':
                $activityData['description'] = "A ajouté '{$video->title}' aux favoris";
                break;
        }

        // Vérifier si une activité similaire existe déjà (éviter les doublons)
        $existingActivity = $user->activities()
            ->where('video_id', $request->videoId)
            ->where('type', $request->type)
            ->where('created_at', '>', now()->subMinutes(5)) // Dans les 5 dernières minutes
            ->first();

        if ($existingActivity) {
            return ApiResponse::success([
                'activity' => new ActivityResource($existingActivity),
            ], 'Activité déjà enregistrée');
        }

        $activity = Activity::create($activityData);

        return ApiResponse::success([
            'activity' => new ActivityResource($activity->load(['video.creator', 'video.genres'])),
        ], 'Activité enregistrée avec succès');
    }

    /**
     * Obtenir une activité spécifique
     */
    public function show(Request $request, $activityId)
    {
        $user = $request->user();
        $activity = $user->activities()
            ->with(['video.creator', 'video.genres'])
            ->find($activityId);

        if (!$activity) {
            return ApiResponse::notFound('Activité non trouvée');
        }

        return ApiResponse::success([
            'activity' => new ActivityResource($activity),
        ]);
    }

    /**
     * Supprimer une activité
     */
    public function destroy(Request $request, $activityId)
    {
        $user = $request->user();
        $activity = $user->activities()->find($activityId);

        if (!$activity) {
            return ApiResponse::notFound('Activité non trouvée');
        }

        $activity->delete();

        return ApiResponse::success(null, 'Activité supprimée avec succès');
    }

    /**
     * Supprimer toutes les activités d'un type spécifique
     */
    public function clearByType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:like,watch,download,share,favorite'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();
        $deletedCount = $user->activities()
            ->where('type', $request->type)
            ->delete();

        return ApiResponse::success([
            'deletedCount' => $deletedCount,
        ], "Toutes les activités de type '{$request->type}' ont été supprimées");
    }

    /**
     * Vider toute l'activité
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $deletedCount = $user->activities()->delete();

        return ApiResponse::success([
            'deletedCount' => $deletedCount,
        ], 'Toute l\'activité a été supprimée');
    }

    /**
     * Obtenir les statistiques d'activité
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $stats = [
            'totalActivities' => $user->activities()->count(),
            'byType' => [
                'like' => $user->activities()->where('type', 'like')->count(),
                'watch' => $user->activities()->where('type', 'watch')->count(),
                'download' => $user->activities()->where('type', 'download')->count(),
                'share' => $user->activities()->where('type', 'share')->count(),
                'favorite' => $user->activities()->where('type', 'favorite')->count(),
            ],
            'thisWeek' => $user->activities()
                ->where('created_at', '>=', now()->subWeek())
                ->count(),
            'thisMonth' => $user->activities()
                ->where('created_at', '>=', now()->subMonth())
                ->count(),
        ];

        return ApiResponse::success($stats);
    }
} 