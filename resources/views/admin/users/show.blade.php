@extends('layouts.admin')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-user mr-2"></i>
                {{ $user->name }}
                @if($user->is_admin)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i class="fas fa-crown mr-1"></i>
                        Administrateur
                    </span>
                @endif
            </h2>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                <i class="fas fa-edit mr-2"></i>
                Modifier
            </a>
            @if(!$user->is_admin || \App\Models\User::where('is_admin', true)->count() > 1)
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirmDelete('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                        <i class="fas fa-trash mr-2"></i>
                        Supprimer
                    </button>
                </form>
            @endif
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nom complet</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 flex items-center">
                            {{ $user->email }}
                            @if($user->email_verified_at)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>
                                    Vérifié
                                </span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation mr-1"></i>
                                    Non vérifié
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                        <dd class="mt-1 text-sm text-gray-900 flex items-center">
                            {{ $user->phone ?? 'Non renseigné' }}
                            @if($user->phone && $user->phone_verified_at)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>
                                    Vérifié
                                </span>
                            @elseif($user->phone)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation mr-1"></i>
                                    Non vérifié
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($user->is_admin)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-crown mr-1"></i>
                                    Administrateur
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-user mr-1"></i>
                                    Utilisateur
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date d'inscription</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $user->created_at->format('d/m/Y à H:i') }}
                            <span class="text-gray-500">({{ $user->created_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dernière activité</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $user->updated_at->format('d/m/Y à H:i') }}
                            <span class="text-gray-500">({{ $user->updated_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques d'activité -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                            <i class="fas fa-download text-blue-500 text-2xl"></i>
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
                            <p class="text-sm font-medium text-gray-500">Temps de visionnage</p>
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
                            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Vidéos complétées</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['completedVideos'] }}</p>
                        </div>
                    </div>
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
                    @if(!$user->email_verified_at)
                        <form method="POST" action="{{ route('admin.users.verify-email', $user) }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-md inline-flex items-center justify-center">
                                <i class="fas fa-envelope-check mr-2"></i>
                                Vérifier l'email
                            </button>
                        </form>
                    @endif

                    @if($user->phone && !$user->phone_verified_at)
                        <form method="POST" action="{{ route('admin.users.verify-phone', $user) }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md inline-flex items-center justify-center">
                                <i class="fas fa-phone-check mr-2"></i>
                                Vérifier le téléphone
                            </button>
                        </form>
                    @endif

                    @if(!$user->is_admin || \App\Models\User::where('is_admin', true)->count() > 1)
                        <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full {{ $user->is_admin ? 'bg-orange-500 hover:bg-orange-600' : 'bg-purple-500 hover:bg-purple-600' }} text-white font-medium py-2 px-4 rounded-md inline-flex items-center justify-center">
                                @if($user->is_admin)
                                    <i class="fas fa-user-minus mr-2"></i>
                                    Retirer admin
                                @else
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Promouvoir admin
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Favoris récents -->
        @if($user->favorites->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <i class="fas fa-heart mr-2 text-red-600"></i>
                        Favoris Récents
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($user->favorites->take(5) as $favorite)
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @if($favorite->video->thumbnail)
                                        <img class="w-16 h-12 object-cover rounded" src="{{ Storage::url($favorite->video->thumbnail) }}" alt="{{ $favorite->video->title }}">
                                    @else
                                        <div class="w-16 h-12 bg-gray-300 rounded flex items-center justify-center">
                                            <i class="fas fa-video text-gray-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $favorite->video->title }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Ajouté {{ $favorite->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="text-sm text-gray-500">{{ number_format($favorite->video->views) }} vues</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($user->favorites->count() > 5)
                        <div class="mt-4 text-center">
                            <span class="text-sm text-gray-500">Et {{ $user->favorites->count() - 5 }} favoris de plus...</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Activités récentes -->
        @if($user->activities->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <i class="fas fa-history mr-2 text-green-600"></i>
                        Activités Récentes
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($user->activities->take(10) as $activity)
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        @switch($activity->type)
                                            @case('video_view')
                                                <i class="fas fa-eye text-blue-600 text-sm"></i>
                                                @break
                                            @case('favorite')
                                                <i class="fas fa-heart text-red-600 text-sm"></i>
                                                @break
                                            @case('download')
                                                <i class="fas fa-download text-green-600 text-sm"></i>
                                                @break
                                            @default
                                                <i class="fas fa-circle text-gray-600 text-sm"></i>
                                        @endswitch
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $activity->type)) }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $activity->created_at->format('d/m/Y à H:i') }}
                                        <span class="ml-2">({{ $activity->created_at->diffForHumans() }})</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($user->activities->count() > 10)
                        <div class="mt-4 text-center">
                            <span class="text-sm text-gray-500">Et {{ $user->activities->count() - 10 }} activités de plus...</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection 