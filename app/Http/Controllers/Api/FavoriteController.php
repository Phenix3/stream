<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * Obtenir les favoris de l'utilisateur
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

        $query = $user->favorites()->with(['video.creator', 'video.genres']);
        $totalCount = $query->count();

        $favorites = $query->orderBy('created_at', 'desc')
                          ->skip(($page - 1) * $limit)
                          ->take($limit)
                          ->get();

        $videos = $favorites->map(function ($favorite) {
            return $favorite->video;
        })->filter(); // Enlever les null au cas où

        $totalPages = ceil($totalCount / $limit);

        return ApiResponse::success([
            'favorites' => VideoResource::collection($videos),
            'totalCount' => $totalCount,
            'page' => (int) $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Ajouter/Retirer une vidéo des favoris
     */
    public function toggle(Request $request, $videoId)
    {
        $video = Video::find($videoId);

        if (!$video) {
            return ApiResponse::notFound('Vidéo non trouvée');
        }

        $user = $request->user();
        $favorite = $user->favorites()->where('video_id', $videoId)->first();

        if ($favorite) {
            // Retirer des favoris
            $favorite->delete();
            $isFavorite = false;
            $message = 'Vidéo retirée des favoris';
        } else {
            // Ajouter aux favoris
            $user->favorites()->create([
                'video_id' => $videoId,
            ]);
            $isFavorite = true;
            $message = 'Vidéo ajoutée aux favoris';
        }

        return ApiResponse::success([
            'isFavorite' => $isFavorite,
            'message' => $message,
        ]);
    }

    /**
     * Vérifier si une vidéo est en favoris
     */
    public function check(Request $request, $videoId)
    {
        $user = $request->user();
        $isFavorite = $user->favorites()->where('video_id', $videoId)->exists();

        return ApiResponse::success([
            'isFavorite' => $isFavorite,
        ]);
    }

    /**
     * Supprimer un favori spécifique
     */
    public function destroy(Request $request, $videoId)
    {
        $user = $request->user();
        $favorite = $user->favorites()->where('video_id', $videoId)->first();

        if (!$favorite) {
            return ApiResponse::notFound('Favori non trouvé');
        }

        $favorite->delete();

        return ApiResponse::success(null, 'Favori supprimé avec succès');
    }

    /**
     * Supprimer tous les favoris
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $deletedCount = $user->favorites()->delete();

        return ApiResponse::success([
            'deletedCount' => $deletedCount,
        ], 'Tous les favoris ont été supprimés');
    }
} 