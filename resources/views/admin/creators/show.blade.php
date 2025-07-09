@extends('layouts.admin')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.creators.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-star mr-2"></i>
                {{ $creator->name }}
                @if($creator->verified)
                    <i class="fas fa-check-circle text-blue-500 ml-2" title="Créateur vérifié"></i>
                @endif
            </h2>
        </div>
        <div class="flex space-x-2">
            <form action="{{ route('admin.creators.toggle-verification', $creator) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                    @if($creator->verified)
                        <i class="fas fa-star-slash mr-2"></i>
                        Retirer la vérification
                    @else
                        <i class="fas fa-star mr-2"></i>
                        Vérifier le créateur
                    @endif
                </button>
            </form>
            <a href="{{ route('admin.creators.edit', $creator) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                <i class="fas fa-edit mr-2"></i>
                Modifier
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Informations du créateur -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-start space-x-6">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        @if($creator->avatar)
                            <img class="h-24 w-24 rounded-full object-cover" src="{{ Storage::url($creator->avatar) }}" alt="{{ $creator->name }}">
                        @else
                            <div class="h-24 w-24 rounded-full bg-gray-300 flex items-center justify-center">
                                <i class="fas fa-user text-gray-600 text-3xl"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Informations principales -->
                    <div class="flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Informations générales</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Nom</dt>
                                        <dd class="text-sm text-gray-900">{{ $creator->name }}</dd>
                                    </div>
                                    @if($creator->description)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="text-sm text-gray-900">{{ $creator->description }}</dd>
                                    </div>
                                    @endif
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                                        <dd class="text-sm">
                                            @if($creator->verified)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Vérifié
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    En attente
                                                </span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Abonnés</dt>
                                        <dd class="text-sm text-gray-900">{{ number_format($creator->subscriber_count) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Date de création</dt>
                                        <dd class="text-sm text-gray-900">{{ $creator->created_at->format('d/m/Y H:i') }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bannière -->
                @if($creator->banner)
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Bannière</h4>
                    <img class="w-full max-w-2xl h-32 object-cover rounded-lg" src="{{ Storage::url($creator->banner) }}" alt="Bannière de {{ $creator->name }}">
                </div>
                @endif
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-video text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Vidéos</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ number_format($stats['totalVideos']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

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
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Vues</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ number_format($stats['totalViews']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

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
                                <dt class="text-sm font-medium text-gray-500 truncate">Note Moyenne</dt>
                                <dd class="text-2xl font-semibold text-gray-900">
                                    {{ $stats['averageRating'] ? number_format($stats['averageRating'], 1) : 'N/A' }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Abonnés</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ number_format($creator->subscriber_count) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Répartition par visibilité -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Répartition des vidéos par visibilité</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $stats['publicVideos'] }}</div>
                        <div class="text-sm text-gray-500">Publiques</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $stats['privateVideos'] }}</div>
                        <div class="text-sm text-gray-500">Privées</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $stats['unlistedVideos'] }}</div>
                        <div class="text-sm text-gray-500">Non listées</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 10 des vidéos les plus populaires -->
        @if($topVideos->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Top 10 des vidéos les plus populaires</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vidéo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visibilité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vues</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topVideos as $video)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-16">
                                        @if($video->thumbnail)
                                            <img class="h-10 w-16 object-cover rounded" src="{{ Storage::url($video->thumbnail) }}" alt="{{ $video->title }}">
                                        @else
                                            <div class="h-10 w-16 bg-gray-300 rounded flex items-center justify-center">
                                                <i class="fas fa-play text-gray-600"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($video->title, 50) }}</div>
                                        <div class="text-sm text-gray-500">{{ $video->duration ? gmdate('H:i:s', (int)$video->duration) : 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($video->visibility)
                                    @case('public')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-globe mr-1"></i>
                                            Public
                                        </span>
                                        @break
                                    @case('private')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-lock mr-1"></i>
                                            Privé
                                        </span>
                                        @break
                                    @case('unlisted')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-eye-slash mr-1"></i>
                                            Non listé
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <i class="fas fa-eye text-green-500 mr-1"></i>
                                {{ number_format($video->views) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($video->rating)
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        {{ number_format($video->rating, 1) }}
                                    </div>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $video->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.videos.show', $video) }}" class="text-blue-600 hover:text-blue-900" title="Voir la vidéo">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
@endsection 