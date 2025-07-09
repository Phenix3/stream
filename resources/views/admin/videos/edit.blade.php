@extends('layouts.admin')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.videos.show', $video) }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-video mr-2"></i>
                Modifier : {{ $video->title }}
            </h2>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                <i class="fas fa-edit mr-2 text-blue-600"></i>
                Informations de la vidéo
            </h3>
        </div>

        <form method="POST" action="{{ route('admin.videos.update', $video) }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Informations de base -->
                <div class="space-y-6">
                    <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informations de base
                    </h4>

                    <!-- Titre -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">
                            Titre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               value="{{ old('title', $video->title) }}"
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="4"
                                  required
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $video->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- URL du contenu -->
                    <div>
                        <label for="content_url" class="block text-sm font-medium text-gray-700">
                            URL du contenu vidéo <span class="text-red-500">*</span>
                        </label>
                        <input type="url" 
                               name="content_url" 
                               id="content_url" 
                               value="{{ old('content_url', $video->content_url) }}"
                               required
                               placeholder="https://example.com/video.mp4"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('content_url') border-red-500 @enderror">
                        @error('content_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Créateur -->
                    <div>
                        <label for="creator_id" class="block text-sm font-medium text-gray-700">
                            Créateur <span class="text-red-500">*</span>
                        </label>
                        <select name="creator_id" 
                                id="creator_id"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('creator_id') border-red-500 @enderror">
                            <option value="">Sélectionner un créateur</option>
                            @foreach($creators as $creator)
                                <option value="{{ $creator->id }}" {{ old('creator_id', $video->creator_id) == $creator->id ? 'selected' : '' }}>
                                    {{ $creator->name }}
                                    @if($creator->verified)
                                        ✓
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('creator_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Durée -->
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700">
                            Durée <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="duration" 
                               id="duration" 
                               value="{{ old('duration', $video->duration) }}"
                               required
                               placeholder="ex: 1h 30min ou 90:00"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('duration') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-gray-500">Format libre: "1h 30min", "90:00", "1 heure 30 minutes"</p>
                        @error('duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Métadonnées et options -->
                <div class="space-y-6">
                    <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">
                        <i class="fas fa-cog mr-2 text-gray-600"></i>
                        Métadonnées et options
                    </h4>

                    <!-- Miniature actuelle -->
                    @if($video->thumbnail)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Miniature actuelle
                            </label>
                            <img src="{{ Storage::url($video->thumbnail) }}" 
                                 alt="Miniature actuelle" 
                                 class="w-32 h-24 object-cover rounded border">
                        </div>
                    @endif

                    <!-- Visibilité -->
                    <div>
                        <label for="visibility" class="block text-sm font-medium text-gray-700">
                            Visibilité <span class="text-red-500">*</span>
                        </label>
                        <select name="visibility" 
                                id="visibility"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('visibility') border-red-500 @enderror">
                            @foreach($visibilityOptions as $option)
                                <option value="{{ $option }}" {{ old('visibility', $video->visibility) == $option ? 'selected' : '' }}>
                                    @switch($option)
                                        @case('public')
                                            🌐 Public - Visible par tous
                                            @break
                                        @case('private')
                                            🔒 Privé - Accessible uniquement aux autorisés
                                            @break
                                        @case('unlisted')
                                            👁️‍🗨️ Non listé - Accessible avec le lien uniquement
                                            @break
                                    @endswitch
                                </option>
                            @endforeach
                        </select>
                        @error('visibility')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Note -->
                    <div>
                        <label for="rating" class="block text-sm font-medium text-gray-700">
                            Note (sur 5)
                        </label>
                        <input type="number" 
                               name="rating" 
                               id="rating" 
                               value="{{ old('rating', $video->rating) }}"
                               min="0"
                               max="5"
                               step="0.1"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('rating') border-red-500 @enderror">
                        @error('rating')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Saison -->
                    <div>
                        <label for="season" class="block text-sm font-medium text-gray-700">
                            Saison
                        </label>
                        <input type="text" 
                               name="season" 
                               id="season" 
                               value="{{ old('season', $video->season) }}"
                               placeholder="ex: Saison 1, S01, 2024"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('season') border-red-500 @enderror">
                        @error('season')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nouvelle miniature -->
                    <div>
                        <label for="thumbnail" class="block text-sm font-medium text-gray-700">
                            {{ $video->thumbnail ? 'Changer la miniature' : 'Miniature' }}
                        </label>
                        <input type="file" 
                               name="thumbnail" 
                               id="thumbnail"
                               accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('thumbnail') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-gray-500">JPG, PNG ou GIF. Taille maximale: 2MB. Laissez vide pour conserver l'actuelle.</p>
                        @error('thumbnail')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Statistiques (lecture seule) -->
                    <div class="bg-gray-50 p-4 rounded-md">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Statistiques</h5>
                        <div class="text-sm text-gray-600 space-y-1">
                            <div>👁️ {{ number_format($video->views) }} vues</div>
                            <div>📅 Créée le {{ $video->created_at->format('d/m/Y à H:i') }}</div>
                            <div>🔄 Mise à jour le {{ $video->updated_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Genres et Acteurs -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">
                    <i class="fas fa-tags mr-2 text-purple-600"></i>
                    Genres et Casting
                </h4>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Genres -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Genres
                        </label>
                        <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
                            @foreach($genres as $genre)
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="genres[]" 
                                           value="{{ $genre->id }}"
                                           id="genre_{{ $genre->id }}"
                                           {{ in_array($genre->id, old('genres', $video->genres->pluck('id')->toArray())) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="genre_{{ $genre->id }}" class="ml-2 text-sm text-gray-900">
                                        {{ $genre->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('genres')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acteurs -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Acteurs
                        </label>
                        <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
                            @foreach($actors as $actor)
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="actors[]" 
                                           value="{{ $actor->id }}"
                                           id="actor_{{ $actor->id }}"
                                           {{ in_array($actor->id, old('actors', $video->actors->pluck('id')->toArray())) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="actor_{{ $actor->id }}" class="ml-2 text-sm text-gray-900">
                                        {{ $actor->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('actors')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.videos.show', $video) }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md">
                    <i class="fas fa-times mr-2"></i>
                    Annuler
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md">
                    <i class="fas fa-save mr-2"></i>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
@endsection 