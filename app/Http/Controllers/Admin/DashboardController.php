<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Video;
use App\Models\Creator;
use App\Models\Download;
use App\Models\Favorite;
use App\Models\Activity;
use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [
            'totalUsers' => User::count(),
            'totalVideos' => Video::count(),
            'totalCreators' => Creator::count(),
            'totalViews' => Video::sum('views'),
            'activeUsers' => User::where('updated_at', '>=', now()->subDays(30))->count(),
            'totalDownloads' => Download::count(),
            'totalFavorites' => Favorite::count(),
            'publicVideos' => Video::public()->count(),
            'privateVideos' => Video::private()->count(),
        ];

        // Utilisateurs récents
        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Vidéos récentes
        $recentVideos = Video::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Activités récentes
        $recentActivities = Activity::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Graphique des inscriptions des 30 derniers jours
        $registrations = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Graphique des vues par jour
        $dailyViews = WatchHistory::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentVideos',
            'recentActivities',
            'registrations',
            'dailyViews'
        ));
    }
} 