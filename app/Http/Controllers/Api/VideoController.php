<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\VideoResource;
use App\Http\Resources\VideoSectionResource;
use App\Models\Video;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    /**
     * Obtenir les vidéos de la page d'accueil
     */
    public function home(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            // Vidéos en vedette (les plus vues des 30 derniers jours)
            $featuredVideos = Video::public()
                ->with(['creator:id,name,avatar,subscriber_count'])
                ->where('created_at', '>=', now()->subDays(30))
                ->orderBy('views', 'desc')
                ->limit(5)
                ->get();

            // Sections de vidéos
            $sections = [];

            // Section Latest
            $latestVideos = Video::public()
                ->with(['creator:id,name,avatar,subscriber_count'])
                ->latest()
                ->limit($limit)
                ->get();

            $sections[] = (object) [
                'title' => 'Latest',
                'type' => 'latest',
                'videos' => $latestVideos
            ];

            // Section Trending
            $trendingVideos = Video::public()
                ->with(['creator:id,name,avatar,subscriber_count'])
                ->trending()
                ->limit($limit)
                ->get();

            $sections[] = (object) [
                'title' => 'Trending',
                'type' => 'trending', 
                'videos' => $trendingVideos
            ];

            // Section Popular
            $popularVideos = Video::public()
                ->with(['creator:id,name,avatar,subscriber_count'])
                ->popular()
                ->limit($limit)
                ->get();

            $sections[] = (object) [
                'title' => 'Popular',
                'type' => 'popular',
                'videos' => $popularVideos
            ];

            return ApiResponse::success([
                'featuredVideos' => VideoResource::collection($featuredVideos),
                'sections' => VideoSectionResource::collection(collect($sections)),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in VideoController@home: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors du chargement des vidéos', 500);
        }
    }

    /**
     * Recherche de vidéos
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => ['required', 'string', 'min:2'],
            'genre' => ['sometimes', 'string'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $query = Video::public()->with(['creator', 'genres', 'actors']);
        $searchTerm = $request->q;
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        // Recherche par titre et description
        $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%");
        });

        // Filtrer par genre si spécifié
        if ($request->has('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->genre}%")
                  ->orWhere('slug', $request->genre);
            });
        }

        $totalResults = $query->count();
        $videos = $query->orderBy('views', 'desc')
                       ->skip(($page - 1) * $limit)
                       ->take($limit)
                       ->get();

        $totalPages = ceil($totalResults / $limit);

        return ApiResponse::success([
            'videos' => VideoResource::collection($videos),
            'totalResults' => $totalResults,
            'page' => (int) $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Obtenir une vidéo spécifique
     */
    public function show(Request $request, $videoId)
    {
        $video = Video::public()
            ->with(['creator', 'genres', 'actors'])
            ->find($videoId);

        if (!$video) {
            return ApiResponse::notFound('Vidéo non trouvée');
        }

        // Incrémenter le nombre de vues
        $video->incrementViews();

        // Obtenir des vidéos similaires (même genre)
        $relatedVideos = Video::public()
            ->with(['creator', 'genres'])
            ->whereHas('genres', function ($query) use ($video) {
                $query->whereIn('genres.id', $video->genres->pluck('id'));
            })
            ->where('id', '!=', $video->id)
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        return ApiResponse::success([
            'video' => new VideoResource($video),
            'relatedVideos' => VideoResource::collection($relatedVideos),
        ]);
    }

    /**
     * Obtenir les sections de vidéos
     */
    public function sections(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['sometimes', 'string', 'in:latest,trending,popular,recommended'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $type = $request->get('type', 'latest');
        $limit = $request->get('limit', 20);
        $sections = [];

        switch ($type) {
            case 'latest':
                $videos = Video::public()
                    ->with(['creator', 'genres'])
                    ->latest()
                    ->limit($limit)
                    ->get();
                
                $sections[] = (object) [
                    'title' => 'Latest',
                    'type' => 'latest',
                    'videos' => $videos
                ];
                break;

            case 'trending':
                $videos = Video::public()
                    ->with(['creator', 'genres'])
                    ->trending()
                    ->limit($limit)
                    ->get();
                
                $sections[] = (object) [
                    'title' => 'Trending',
                    'type' => 'trending',
                    'videos' => $videos
                ];
                break;

            case 'popular':
                $videos = Video::public()
                    ->with(['creator', 'genres'])
                    ->popular()
                    ->limit($limit)
                    ->get();
                
                $sections[] = (object) [
                    'title' => 'Popular',
                    'type' => 'popular',
                    'videos' => $videos
                ];
                break;

            case 'recommended':
                // Pour les recommandations, on peut utiliser les vidéos populaires
                // Dans une vraie application, on implémenterait un algorithme de recommandation
                $videos = Video::public()
                    ->with(['creator', 'genres'])
                    ->where('rating', '>=', 4.0)
                    ->orderBy('views', 'desc')
                    ->limit($limit)
                    ->get();
                
                $sections[] = (object) [
                    'title' => 'Recommended',
                    'type' => 'recommended',
                    'videos' => $videos
                ];
                break;
        }

        return ApiResponse::success([
            'sections' => VideoSectionResource::collection(collect($sections)),
        ]);
    }

    /**
     * Obtenir les vidéos par genre
     */
    public function byGenre(Request $request, $genreSlug)
    {
        $genre = Genre::where('slug', $genreSlug)->first();

        if (!$genre) {
            return ApiResponse::notFound('Genre non trouvé');
        }

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $query = Video::public()
            ->with(['creator', 'genres'])
            ->whereHas('genres', function ($q) use ($genre) {
                $q->where('genres.id', $genre->id);
            });

        $totalResults = $query->count();
        $videos = $query->orderBy('views', 'desc')
                       ->skip(($page - 1) * $limit)
                       ->take($limit)
                       ->get();

        $totalPages = ceil($totalResults / $limit);

        return ApiResponse::success([
            'genre' => $genre->name,
            'videos' => VideoResource::collection($videos),
            'totalResults' => $totalResults,
            'page' => (int) $page,
            'totalPages' => $totalPages,
        ]);
    }
} 