<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\User;
use App\Models\Video;
use App\Models\Creator;
use App\Models\Download;
use App\Models\WatchHistory;
use App\Models\Favorite;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Obtenir les statistiques générales
     */
    public function index()
    {
        $stats = [
            'totalUsers' => User::count(),
            'totalVideos' => Video::count(),
            'totalViews' => Video::sum('views'),
            'activeUsers' => User::where('updated_at', '>=', now()->subDays(30))->count(),
            'totalCreators' => Creator::count(),
            'totalDownloads' => Download::count(),
            'totalFavorites' => Favorite::count(),
            'publicVideos' => Video::public()->count(),
            'privateVideos' => Video::private()->count(),
            'unlistedVideos' => Video::unlisted()->count(),
        ];

        return ApiResponse::success($stats);
    }

    /**
     * Statistiques des utilisateurs
     */
    public function users(Request $request)
    {
        $period = $request->get('period', '30'); // jours

        $stats = [
            'total' => User::count(),
            'verified' => User::emailVerified()->count(),
            'phoneVerified' => User::phoneVerified()->count(),
            'newUsers' => User::where('created_at', '>=', now()->subDays($period))->count(),
            'activeUsers' => User::where('updated_at', '>=', now()->subDays($period))->count(),
            'usersWithFavorites' => User::has('favorites')->count(),
            'usersWithDownloads' => User::has('downloads')->count(),
        ];

        // Graphique des inscriptions par jour
        $registrations = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays($period))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        $stats['registrationChart'] = $registrations;

        return ApiResponse::success($stats);
    }

    /**
     * Statistiques des vidéos
     */
    public function videos(Request $request)
    {
        $period = $request->get('period', '30');

        $stats = [
            'total' => Video::count(),
            'public' => Video::public()->count(),
            'private' => Video::private()->count(),
            'unlisted' => Video::unlisted()->count(),
            'totalViews' => Video::sum('views'),
            'averageRating' => Video::avg('rating'),
            'newVideos' => Video::where('created_at', '>=', now()->subDays($period))->count(),
        ];

        // Top 10 des vidéos les plus vues
        $topVideos = Video::with('creator')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'creator' => $video->creator->name,
                    'views' => $video->views,
                    'rating' => $video->rating,
                ];
            });

        $stats['topVideos'] = $topVideos;

        // Statistiques par genre
        $genreStats = DB::table('video_genres')
            ->join('genres', 'video_genres.genre_id', '=', 'genres.id')
            ->join('videos', 'video_genres.video_id', '=', 'videos.id')
            ->select('genres.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(videos.views) as total_views'))
            ->groupBy('genres.id', 'genres.name')
            ->orderBy('count', 'desc')
            ->get();

        $stats['byGenre'] = $genreStats;

        return ApiResponse::success($stats);
    }

    /**
     * Statistiques des créateurs
     */
    public function creators()
    {
        $stats = [
            'total' => Creator::count(),
            'verified' => Creator::verified()->count(),
            'withVideos' => Creator::has('videos')->count(),
            'averageSubscribers' => Creator::avg('subscriber_count'),
        ];

        // Top créateurs par nombre d'abonnés
        $topCreators = Creator::orderBy('subscriber_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($creator) {
                return [
                    'id' => $creator->id,
                    'name' => $creator->name,
                    'subscriberCount' => $creator->subscriber_count,
                    'videosCount' => $creator->videos_count,
                    'totalViews' => $creator->total_views,
                    'verified' => $creator->verified,
                ];
            });

        $stats['topCreators'] = $topCreators;

        return ApiResponse::success($stats);
    }

    /**
     * Statistiques d'engagement
     */
    public function engagement(Request $request)
    {
        $period = $request->get('period', '30');

        $stats = [
            'totalFavorites' => Favorite::count(),
            'totalDownloads' => Download::count(),
            'totalWatchTime' => WatchHistory::sum('watched_duration'),
            'averageWatchTime' => WatchHistory::avg('watched_duration'),
            'completedVideos' => WatchHistory::where('completed', true)->count(),
            'totalActivities' => Activity::count(),
        ];

        // Activités par type
        $activityTypes = Activity::select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        $stats['activitiesByType'] = $activityTypes;

        // Évolution de l'engagement
        $engagement = DB::table('activities')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_activities'),
                'type'
            )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        $stats['engagementChart'] = $engagement;

        return ApiResponse::success($stats);
    }

    /**
     * Statistiques de contenu populaire
     */
    public function popular(Request $request)
    {
        $period = $request->get('period', '7'); // derniers 7 jours par défaut

        // Vidéos tendances
        $trendingVideos = Video::with('creator')
            ->where('created_at', '>=', now()->subDays($period))
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'creator' => $video->creator->name,
                    'views' => $video->views,
                    'createdAt' => $video->created_at->toDateString(),
                ];
            });

        // Genres populaires
        $popularGenres = DB::table('video_genres')
            ->join('genres', 'video_genres.genre_id', '=', 'genres.id')
            ->join('videos', 'video_genres.video_id', '=', 'videos.id')
            ->select('genres.name', DB::raw('SUM(videos.views) as total_views'))
            ->where('videos.created_at', '>=', now()->subDays($period))
            ->groupBy('genres.id', 'genres.name')
            ->orderBy('total_views', 'desc')
            ->limit(10)
            ->get();

        // Créateurs tendances
        $trendingCreators = Creator::withCount(['videos as recent_videos' => function ($query) use ($period) {
            $query->where('created_at', '>=', now()->subDays($period));
        }])
        ->having('recent_videos', '>', 0)
        ->orderBy('recent_videos', 'desc')
        ->limit(10)
        ->get()
        ->map(function ($creator) {
            return [
                'id' => $creator->id,
                'name' => $creator->name,
                'recentVideos' => $creator->recent_videos,
                'subscriberCount' => $creator->subscriber_count,
            ];
        });

        return ApiResponse::success([
            'trendingVideos' => $trendingVideos,
            'popularGenres' => $popularGenres,
            'trendingCreators' => $trendingCreators,
        ]);
    }

    /**
     * Rapport de performance
     */
    public function performance(Request $request)
    {
        $period = $request->get('period', '30');

        // Métriques de performance
        $metrics = [
            'totalPageViews' => Video::sum('views'),
            'averageSessionDuration' => WatchHistory::avg('watched_duration'),
            'bounceRate' => 0, // À calculer selon la logique métier
            'conversionRate' => 0, // Taux de conversion des vues en favoris
        ];

        // Calcul du taux de conversion
        $totalViews = Video::sum('views');
        $totalFavorites = Favorite::count();
        $metrics['conversionRate'] = $totalViews > 0 ? round(($totalFavorites / $totalViews) * 100, 2) : 0;

        // Performance par jour
        $dailyPerformance = DB::table('watch_histories')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('AVG(watched_duration) as avg_duration'),
                DB::raw('SUM(watched_duration) as total_duration')
            )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return ApiResponse::success([
            'metrics' => $metrics,
            'dailyPerformance' => $dailyPerformance,
        ]);
    }
} 