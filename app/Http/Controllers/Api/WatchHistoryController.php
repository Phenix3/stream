<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\WatchHistoryResource;
use App\Models\Video;
use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WatchHistoryController extends Controller
{
    /**
     * Obtenir l'historique de visionnage
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $query = $user->watchHistory()->with(['video.creator', 'video.genres']);
        $totalCount = $query->count();

        $history = $query->orderBy('created_at', 'desc')
                        ->skip(($page - 1) * $limit)
                        ->take($limit)
                        ->get();

        $totalPages = ceil($totalCount / $limit);

        return ApiResponse::success([
            'history' => WatchHistoryResource::collection($history),
            'totalCount' => $totalCount,
            'page' => (int) $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Enregistrer une vue/progression
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'videoId' => ['required', 'integer', 'exists:videos,id'],
            'watchedDuration' => ['required', 'integer', 'min:0'],
            'totalDuration' => ['required', 'integer', 'min:1'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();
        $videoId = $request->videoId;
        $watchedDuration = $request->watchedDuration;
        $totalDuration = $request->totalDuration;
        $completed = $request->get('completed', false);

        // Vérifier si l'entrée existe déjà
        $history = $user->watchHistory()->where('video_id', $videoId)->first();

        if ($history) {
            // Mettre à jour l'entrée existante
            $history->update([
                'watched_duration' => max($history->watched_duration, $watchedDuration),
                'total_duration' => $totalDuration,
                'completed' => $completed || $history->completed,
                'updated_at' => now(),
            ]);
        } else {
            // Créer une nouvelle entrée
            $history = $user->watchHistory()->create([
                'video_id' => $videoId,
                'watched_duration' => $watchedDuration,
                'total_duration' => $totalDuration,
                'completed' => $completed,
            ]);
        }

        // Mettre à jour les statistiques de l'utilisateur
        $user->increment('videos_watched');
        $user->increment('total_watch_time', $watchedDuration);

        return ApiResponse::success([
            'history' => new WatchHistoryResource($history),
        ], 'Progression enregistrée');
    }

    /**
     * Obtenir la progression pour une vidéo spécifique
     */
    public function show(Request $request, $videoId)
    {
        $user = $request->user();
        $history = $user->watchHistory()
            ->with(['video.creator', 'video.genres'])
            ->where('video_id', $videoId)
            ->first();

        if (!$history) {
            return ApiResponse::notFound('Aucun historique trouvé pour cette vidéo');
        }

        return ApiResponse::success([
            'history' => new WatchHistoryResource($history),
        ]);
    }

    /**
     * Marquer une vidéo comme complètement visionnée
     */
    public function markCompleted(Request $request, $videoId)
    {
        $user = $request->user();
        $video = Video::find($videoId);

        if (!$video) {
            return ApiResponse::notFound('Vidéo non trouvée');
        }

        $history = $user->watchHistory()->where('video_id', $videoId)->first();

        if ($history) {
            $history->update([
                'completed' => true,
                'watched_duration' => $history->total_duration,
            ]);
        } else {
            // Créer une nouvelle entrée marquée comme complète
            $history = $user->watchHistory()->create([
                'video_id' => $videoId,
                'watched_duration' => 0, // Sera mis à jour plus tard si nécessaire
                'total_duration' => 0,
                'completed' => true,
            ]);
        }

        return ApiResponse::success([
            'history' => new WatchHistoryResource($history),
        ], 'Vidéo marquée comme visionnée');
    }

    /**
     * Supprimer une entrée de l'historique
     */
    public function destroy(Request $request, $videoId)
    {
        $user = $request->user();
        $history = $user->watchHistory()->where('video_id', $videoId)->first();

        if (!$history) {
            return ApiResponse::notFound('Entrée d\'historique non trouvée');
        }

        $history->delete();

        return ApiResponse::success(null, 'Entrée supprimée de l\'historique');
    }

    /**
     * Vider tout l'historique
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $deletedCount = $user->watchHistory()->delete();

        return ApiResponse::success([
            'deletedCount' => $deletedCount,
        ], 'Historique vidé avec succès');
    }
} 