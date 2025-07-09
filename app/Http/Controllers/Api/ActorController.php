<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\ActorResource;
use App\Http\Resources\VideoResource;
use App\Models\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActorController extends Controller
{
    /**
     * Obtenir la liste des acteurs
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

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $query = Actor::with(['videos' => function ($query) {
            $query->public()->limit(3); // Limiter à 3 vidéos par acteur pour les performances
        }]);

        $totalResults = $query->count();
        $actors = $query->orderBy('name')
                       ->skip(($page - 1) * $limit)
                       ->take($limit)
                       ->get();

        $totalPages = ceil($totalResults / $limit);

        return ApiResponse::success([
            'actors' => ActorResource::collection($actors),
            'totalResults' => $totalResults,
            'page' => (int) $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Recherche d'acteurs
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => ['required', 'string', 'min:2'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $searchTerm = $request->q;
        $limit = $request->get('limit', 20);

        $actors = Actor::searchByName($searchTerm)
            ->with(['videos' => function ($query) {
                $query->public()->limit(3);
            }])
            ->limit($limit)
            ->get();

        return ApiResponse::success([
            'actors' => ActorResource::collection($actors),
        ]);
    }

    /**
     * Obtenir un acteur spécifique avec ses vidéos
     */
    public function show(Request $request, $actorId)
    {
        $actor = Actor::with(['videos' => function ($query) {
            $query->public()->with(['creator', 'genres'])->orderBy('views', 'desc');
        }])->find($actorId);

        if (!$actor) {
            return ApiResponse::notFound('Acteur non trouvé');
        }

        return ApiResponse::success([
            'actor' => new ActorResource($actor),
            'videos' => VideoResource::collection($actor->videos),
        ]);
    }

    /**
     * Obtenir les acteurs populaires
     */
    public function popular(Request $request)
    {
        $limit = $request->get('limit', 10);

        // Obtenir les acteurs avec le plus de vidéos populaires
        $actors = Actor::withCount(['videos' => function ($query) {
            $query->public()->where('views', '>', 10000);
        }])
        ->having('videos_count', '>', 0)
        ->orderBy('videos_count', 'desc')
        ->limit($limit)
        ->get();

        return ApiResponse::success([
            'actors' => ActorResource::collection($actors),
        ]);
    }
} 