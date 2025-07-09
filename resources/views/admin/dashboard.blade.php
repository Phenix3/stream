@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        <i class="fas fa-chart-bar mr-2"></i>
        {{ __('Tableau de bord') }}
    </h2>
@endsection

@section('content')

    <div class="space-y-6">
        <!-- Statistiques principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Utilisateurs -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Utilisateurs
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        {{ number_format($stats['totalUsers']) }}
                                    </div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                        <span class="sr-only">{{ $stats['activeUsers'] }} actifs ce mois</span>
                                        <small class="text-gray-500">
                                            ({{ number_format($stats['activeUsers']) }} actifs)
                                        </small>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3">
                    <div class="text-sm">
                        <a href="{{ route('admin.users.index') }}" class="font-medium text-blue-600 hover:text-blue-500">
                            Voir tous les utilisateurs
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total Vidéos -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-video text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Vidéos
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        {{ number_format($stats['totalVideos']) }}
                                    </div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold text-blue-600">
                                        <small class="text-gray-500">
                                            ({{ number_format($stats['publicVideos']) }} publiques)
                                        </small>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3">
                    <div class="text-sm">
                        <a href="{{ route('admin.videos.index') }}" class="font-medium text-purple-600 hover:text-purple-500">
                            Voir toutes les vidéos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total Créateurs -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-star text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Créateurs
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        {{ number_format($stats['totalCreators']) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3">
                    <div class="text-sm">
                        <a href="{{ route('admin.creators.index') }}" class="font-medium text-yellow-600 hover:text-yellow-500">
                            Voir tous les créateurs
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total Vues -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-eye text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Vues
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        {{ number_format($stats['totalViews']) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3">
                    <div class="text-sm">
                        <span class="text-gray-500">
                            {{ number_format($stats['totalFavorites']) }} favoris, 
                            {{ number_format($stats['totalDownloads']) }} téléchargements
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Graphique des inscriptions -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                        Inscriptions des 30 derniers jours
                    </h3>
                    <div class="h-64">
                        <canvas id="registrationsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Graphique des vues -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                        Activité de visionnage (30 jours)
                    </h3>
                    <div class="h-64">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listes récentes -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Utilisateurs récents -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <i class="fas fa-user-plus mr-2 text-blue-600"></i>
                        Nouveaux Utilisateurs
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($recentUsers as $user)
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $user->name }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $user->email }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-sm text-gray-500">
                                    {{ $user->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">Aucun nouvel utilisateur</p>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            Voir tous les utilisateurs →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Vidéos récentes -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <i class="fas fa-video mr-2 text-purple-600"></i>
                        Vidéos Récentes
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($recentVideos as $video)
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($video->thumbnail)
                                        <img class="w-12 h-8 object-cover rounded" src="{{ Storage::url($video->thumbnail) }}" alt="{{ $video->title }}">
                                    @else
                                        <div class="w-12 h-8 bg-gray-300 rounded flex items-center justify-center">
                                            <i class="fas fa-video text-gray-600 text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $video->title }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $video->creator->name }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-sm text-gray-500">
                                    {{ $video->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">Aucune vidéo récente</p>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.videos.index') }}" class="text-sm font-medium text-purple-600 hover:text-purple-500">
                            Voir toutes les vidéos →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Activités récentes -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <i class="fas fa-history mr-2 text-green-600"></i>
                        Activités Récentes
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($recentActivities as $activity)
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        @switch($activity->type)
                                            @case('video_view')
                                                <i class="fas fa-eye text-green-600 text-sm"></i>
                                                @break
                                            @case('favorite')
                                                <i class="fas fa-heart text-red-600 text-sm"></i>
                                                @break
                                            @case('download')
                                                <i class="fas fa-download text-blue-600 text-sm"></i>
                                                @break
                                            @default
                                                <i class="fas fa-circle text-gray-600 text-sm"></i>
                                        @endswitch
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $activity->user->name ?? 'Utilisateur supprimé' }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ ucfirst(str_replace('_', ' ', $activity->type)) }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-sm text-gray-500">
                                    {{ $activity->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">Aucune activité récente</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Données pour les graphiques
        const registrationsData = @json($registrations);
        const dailyViewsData = @json($dailyViews);

        // Graphique des inscriptions
        const registrationsCtx = document.getElementById('registrationsChart').getContext('2d');
        new Chart(registrationsCtx, {
            type: 'line',
            data: {
                labels: registrationsData.map(item => new Date(item.date).toLocaleDateString('fr-FR')),
                datasets: [{
                    label: 'Inscriptions',
                    data: registrationsData.map(item => item.count),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Graphique des vues
        const viewsCtx = document.getElementById('viewsChart').getContext('2d');
        new Chart(viewsCtx, {
            type: 'bar',
            data: {
                labels: dailyViewsData.map(item => new Date(item.date).toLocaleDateString('fr-FR')),
                datasets: [{
                    label: 'Vues',
                    data: dailyViewsData.map(item => item.count),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
                 });
     </script>
     @endpush
@endsection 