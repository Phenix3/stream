@extends('layouts.admin')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-star mr-2"></i>
            {{ __('Gestion des Créateurs') }}
        </h2>
        <a href="{{ route('admin.creators.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Nouveau Créateur
        </a>
    </div>
@endsection

@section('content')

    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <!-- Filtres et recherche -->
        <div class="p-6 border-b border-gray-200">
            <form method="GET" action="{{ route('admin.creators.index') }}" class="space-y-4 md:space-y-0 md:flex md:items-center md:space-x-4">
                <!-- Recherche -->
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Rechercher par nom ou description..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Filtre vérification -->
                <div class="min-w-0">
                    <select name="verified" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les créateurs</option>
                        <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Vérifiés</option>
                        <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Non vérifiés</option>
                    </select>
                </div>

                <!-- Boutons d'action -->
                <div class="flex space-x-2">
                    <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                        <i class="fas fa-filter mr-2"></i>
                        Filtrer
                    </button>
                    
                    <a href="{{ route('admin.creators.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-2 px-4 rounded inline-flex items-center">
                        <i class="fas fa-times mr-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tableau des créateurs -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Créateur
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statistiques
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date de création
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($creators as $creator)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    @if($creator->avatar)
                                        <img class="h-12 w-12 rounded-full object-cover" src="{{ Storage::url($creator->avatar) }}" alt="{{ $creator->name }}">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-600 text-xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $creator->name }}
                                        @if($creator->verified)
                                            <i class="fas fa-check-circle text-blue-500 ml-1" title="Créateur vérifié"></i>
                                        @endif
                                    </div>
                                    @if($creator->description)
                                        <div class="text-sm text-gray-500 truncate max-w-xs">
                                            {{ Str::limit($creator->description, 50) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
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
                                @if($creator->subscriber_count > 0)
                                    <span class="text-xs text-gray-500">
                                        {{ number_format($creator->subscriber_count) }} abonnés
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex flex-col space-y-1">
                                <span class="text-xs">
                                    <i class="fas fa-video text-blue-500 mr-1"></i>
                                    {{ $creator->videos_count }} vidéos
                                </span>
                                <span class="text-xs">
                                    <i class="fas fa-eye text-green-500 mr-1"></i>
                                    {{ number_format($creator->videos_sum_views ?? 0) }} vues
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $creator->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.creators.show', $creator) }}" class="text-blue-600 hover:text-blue-900" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.creators.edit', $creator) }}" class="text-indigo-600 hover:text-indigo-900" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Toggle verification -->
                                <form action="{{ route('admin.creators.toggle-verification', $creator) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900" title="{{ $creator->verified ? 'Retirer la vérification' : 'Vérifier le créateur' }}">
                                        @if($creator->verified)
                                            <i class="fas fa-star-slash"></i>
                                        @else
                                            <i class="fas fa-star"></i>
                                        @endif
                                    </button>
                                </form>
                                
                                @if($creator->videos_count == 0)
                                <form action="{{ route('admin.creators.destroy', $creator) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce créateur ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center py-8">
                                <i class="fas fa-star text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun créateur trouvé</h3>
                                <p class="text-gray-500 mb-4">Aucun créateur ne correspond à vos critères de recherche.</p>
                                <a href="{{ route('admin.creators.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                                    <i class="fas fa-plus mr-2"></i>
                                    Créer le premier créateur
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($creators->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $creators->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

@endsection 