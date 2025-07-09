@extends('layouts.admin')
@section('header')
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-video mr-2"></i>
                {{ __('Gestion des Vidéos') }}
            </h2>
            <a href="{{ route('admin.videos.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Nouvelle Vidéo
            </a>
        </div>
@endsection

@section('content')

    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <!-- Filtres et recherche -->
        <div class="p-6 border-b border-gray-200">
            <form method="GET" action="{{ route('admin.videos.index') }}" class="space-y-4 md:space-y-0 md:flex md:items-center md:space-x-4">
                <!-- Recherche -->
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Rechercher par titre ou description..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Filtre visibilité -->
                <div class="min-w-0">
                    <select name="visibility" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Toutes les visibilités</option>
                        @foreach($visibilityOptions as $option)
                            <option value="{{ $option }}" {{ request('visibility') === $option ? 'selected' : '' }}>
                                {{ ucfirst($option) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtre créateur -->
                <div class="min-w-0">
                    <select name="creator_id" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les créateurs</option>
                        @foreach($creators as $creator)
                            <option value="{{ $creator->id }}" {{ request('creator_id') == $creator->id ? 'selected' : '' }}>
                                {{ $creator->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Boutons -->
                <div class="flex space-x-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md">
                        <i class="fas fa-filter mr-1"></i>
                        Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'visibility', 'creator_id']))
                        <a href="{{ route('admin.videos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md">
                            <i class="fas fa-times mr-1"></i>
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tableau des vidéos -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" onchange="toggleAllCheckboxes(this)">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vidéo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Créateur
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Visibilité
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statistiques
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($videos as $video)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_items[]" value="{{ $video->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($video->thumbnail)
                                            <img class="w-16 h-12 object-cover rounded" src="{{ Storage::url($video->thumbnail) }}" alt="{{ $video->title }}">
                                        @else
                                            <div class="w-16 h-12 bg-gray-300 rounded flex items-center justify-center">
                                                <i class="fas fa-video text-gray-600"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                                            {{ $video->title }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Durée: {{ $video->duration }} • Note: {{ number_format($video->rating, 1) }}/5
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $video->creator->name }}</div>
                                    @if($video->creator->verified)
                                        <i class="fas fa-check-circle text-blue-500 ml-1" title="Créateur vérifié"></i>
                                    @endif
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="space-y-1">
                                    <div>{{ number_format($video->views) }} vues</div>
                                    <div>{{ $video->favorites_count }} favoris</div>
                                    <div>{{ $video->downloads_count }} téléchargements</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $video->created_at->format('d/m/Y') }}</div>
                                <div>{{ $video->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2 justify-end">
                                    <a href="{{ route('admin.videos.show', $video) }}" 
                                       class="text-blue-600 hover:text-blue-900"
                                       title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.videos.edit', $video) }}" 
                                       class="text-indigo-600 hover:text-indigo-900"
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.videos.destroy', $video) }}" 
                                          class="inline" 
                                          onsubmit="return confirmDelete('Êtes-vous sûr de vouloir supprimer cette vidéo ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                <div class="flex flex-col items-center py-8">
                                    <i class="fas fa-video text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500 text-lg mb-2">Aucune vidéo trouvée</p>
                                    @if(request()->hasAny(['search', 'visibility', 'creator_id']))
                                        <p class="text-gray-400 mb-4">Essayez de modifier vos critères de recherche</p>
                                        <a href="{{ route('admin.videos.index') }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Voir toutes les vidéos
                                        </a>
                                    @else
                                        <a href="{{ route('admin.videos.create') }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Créer la première vidéo
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Actions en lot -->
        <div id="bulk-actions" class="px-6 py-4 border-t border-gray-200 bg-gray-50" style="display: none;">
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-700">Actions sélectionnées :</span>
                
                <form method="POST" action="{{ route('admin.videos.bulk-visibility') }}" class="inline flex items-center space-x-2">
                    @csrf
                    <input type="hidden" name="video_ids" id="bulk-video-ids">
                    <select name="visibility" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                        <option value="">Changer la visibilité</option>
                        @foreach($visibilityOptions as $option)
                            <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-1 px-3 rounded-md">
                        Appliquer
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.videos.bulk-delete') }}" 
                      class="inline" 
                      onsubmit="return confirmDelete('Êtes-vous sûr de vouloir supprimer les vidéos sélectionnées ?')">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="video_ids" id="bulk-delete-video-ids">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-1 px-3 rounded-md">
                        <i class="fas fa-trash mr-1"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <!-- Pagination -->
        @if($videos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $videos->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Statistiques rapides -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-video text-purple-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $videos->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-globe text-green-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Publiques</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $videos->where('visibility', 'public')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-lock text-red-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Privées</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $videos->where('visibility', 'private')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-eye text-blue-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total vues</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ number_format($videos->sum('views')) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Update bulk action forms with selected IDs
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('input[name="selected_items[]"]:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);
            
            document.getElementById('bulk-video-ids').value = ids.join(',');
            document.getElementById('bulk-delete-video-ids').value = ids.join(',');
            
            const bulkActions = document.getElementById('bulk-actions');
            if (bulkActions) {
                bulkActions.style.display = ids.length > 0 ? 'block' : 'none';
            }
        }

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkActions);
            });
            updateBulkActions();
        });
    </script>
    @endpush
@endsection 