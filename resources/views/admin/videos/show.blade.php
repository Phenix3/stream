@extends('layouts.admin')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.videos.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-video mr-2"></i>
                {{ $video->title }}
                @switch($video->visibility)
                    @case('public')
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-globe mr-1"></i>
                            Public
                        </span>
                        @break
                    @case('private')
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-lock mr-1"></i>
                            Privé
                        </span>
                        @break
                    @case('unlisted')
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-eye-slash mr-1"></i>
                            Non listé
                        </span>
                        @break
                @endswitch
            </h2>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.videos.edit', $video) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                <i class="fas fa-edit mr-2"></i>
                Modifier
            </a>
            <form method="POST" action="{{ route('admin.videos.destroy', $video) }}" class="inline" onsubmit="return confirmDelete('Êtes-vous sûr de vouloir supprimer cette vidéo ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                    Supprimer
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Informations principales -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Informations Générales
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Détails de la vidéo -->
                    <div class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Titre</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $video->title }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $video->description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Créateur</dt>
                            <dd class="mt-1 text-sm text-gray-900 flex items-center">
                                {{ $video->creator->name }}
                                @if($video->creator->verified)
                                    <i class="fas fa-check-circle text-blue-500 ml-1" title="Créateur vérifié"></i>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Durée</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $video->duration }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Note</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <div class="flex items-center">
                                    {{ number_format($video->rating, 1) }}/5
                                    <div class="flex ml-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $video->rating)
                                                <i class="fas fa-star text-yellow-400"></i>
                                            @else
                                                <i class="far fa-star text-gray-300"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                            </dd>
                        </div>
                        @if($video->season)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Saison</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $video->season }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date de création</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $video->created_at->format('d/m/Y à H:i') }}
                                <span class="text-gray-500">({{ $video->created_at->diffForHumans() }})</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dernière modification</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $video->updated_at->format('d/m/Y à H:i') }}
                                <span class="text-gray-500">({{ $video->updated_at->diffForHumans() }})</span>
                            </dd>
                        </div>
                    </div>

                    <!-- Miniature et URL -->
                    <div class="space-y-4">
                        @if($video->thumbnail)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-2">Miniature</dt>
                                <img src="{{ Storage::url($video->thumbnail) }}" 
                                     alt="Miniature de {{ $video->title }}" 
                                     class="w-full max-w-sm rounded-lg shadow-sm">
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">URL du contenu</dt>
                            <dd class="mt-1 text-sm text-gray-900 break-all">
                                <a href="{{ $video->content_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ $video->content_url }}
                                    <i class="fas fa-external-link-alt ml-1"></i>
                                </a>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques d'activité -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-eye text-blue-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Vues</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['totalViews']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-heart text-red-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Favoris</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['totalFavorites'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-download text-green-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Téléchargements</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['totalDownloads'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Temps total</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ gmdate('H:i:s', $stats['totalWatchTime']) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line text-purple-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Temps moyen</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ gmdate('H:i:s', $stats['averageWatchTime']) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-percentage text-indigo-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Complétude</p>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($stats['completionRate'], 1) }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Genres et Acteurs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Genres -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <i class="fas fa-tags mr-2 text-purple-600"></i>
                        Genres
                    </h3>
                </div>
                <div class="p-6">
                    @if($video->genres->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($video->genres as $genre)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    {{ $genre->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic">Aucun genre associé</p>
                    @endif
                </div>
            </div>

            <!-- Acteurs -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <i class="fas fa-users mr-2 text-orange-600"></i>
                        Acteurs
                    </h3>
                </div>
                <div class="p-6">
                    @if($video->actors->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($video->actors as $actor)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                    {{ $actor->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic">Aucun acteur associé</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    <i class="fas fa-cog mr-2 text-gray-600"></i>
                    Actions Rapides
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <form method="POST" action="{{ route('admin.videos.bulk-visibility') }}" class="inline">
                        @csrf
                        <input type="hidden" name="selected_items[]" value="{{ $video->id }}">
                        <select name="visibility" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Changer la visibilité...</option>
                            <option value="public" {{ $video->visibility === 'public' ? 'disabled' : '' }}>
                                🌐 Public
                            </option>
                            <option value="private" {{ $video->visibility === 'private' ? 'disabled' : '' }}>
                                🔒 Privé
                            </option>
                            <option value="unlisted" {{ $video->visibility === 'unlisted' ? 'disabled' : '' }}>
                                👁️‍🗨️ Non listé
                            </option>
                        </select>
                    </form>

                    <a href="{{ $video->content_url }}" target="_blank" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md text-center inline-flex items-center justify-center">
                        <i class="fas fa-play mr-2"></i>
                        Voir la vidéo
                    </a>

                    <a href="{{ route('admin.videos.edit', $video) }}" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-md text-center inline-flex items-center justify-center">
                        <i class="fas fa-edit mr-2"></i>
                        Modifier
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection 