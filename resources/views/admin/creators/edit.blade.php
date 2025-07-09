@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.creators.show', $creator) }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-edit mr-2"></i>
                {{ __('Modifier') }} - {{ $creator->name }}
            </h2>
        </div>
        <div class="flex space-x-2">
            @if($creator->videos()->count() == 0)
            <form action="{{ route('admin.creators.destroy', $creator) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce créateur ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">
                    <i class="fas fa-trash mr-2"></i>
                    Supprimer
                </button>
            </form>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <form action="{{ route('admin.creators.update', $creator) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Informations de base -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informations de base</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom du créateur <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $creator->name) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nombre d'abonnés -->
                    <div>
                        <label for="subscriber_count" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre d'abonnés
                        </label>
                        <input type="number" 
                               name="subscriber_count" 
                               id="subscriber_count" 
                               value="{{ old('subscriber_count', $creator->subscriber_count) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('subscriber_count') border-red-500 @enderror">
                        @error('subscriber_count')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="4"
                              placeholder="Description du créateur..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $creator->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Images -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Images</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Avatar -->
                    <div>
                        <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
                            Avatar
                        </label>
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div id="avatar-preview" class="h-16 w-16 rounded-full overflow-hidden">
                                    @if($creator->avatar)
                                        <img src="{{ Storage::url($creator->avatar) }}" alt="{{ $creator->name }}" class="h-16 w-16 rounded-full object-cover">
                                    @else
                                        <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-600 text-xl"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1">
                                <input type="file" 
                                       name="avatar" 
                                       id="avatar" 
                                       accept="image/*"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('avatar') border-red-500 @enderror">
                                <p class="mt-1 text-sm text-gray-500">PNG, JPG, JPEG. Max 2MB. Laissez vide pour garder l'image actuelle.</p>
                                @error('avatar')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Bannière -->
                    <div>
                        <label for="banner" class="block text-sm font-medium text-gray-700 mb-2">
                            Bannière
                        </label>
                        <div class="space-y-2">
                            <div id="banner-preview" class="w-full h-24 rounded-lg overflow-hidden">
                                @if($creator->banner)
                                    <img src="{{ Storage::url($creator->banner) }}" alt="Bannière {{ $creator->name }}" class="w-full h-24 rounded-lg object-cover">
                                @else
                                    <div class="w-full h-24 bg-gray-300 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-image text-gray-600 text-xl"></i>
                                    </div>
                                @endif
                            </div>
                            <input type="file" 
                                   name="banner" 
                                   id="banner" 
                                   accept="image/*"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('banner') border-red-500 @enderror">
                            <p class="text-sm text-gray-500">PNG, JPG, JPEG. Max 4MB. Laissez vide pour garder l'image actuelle.</p>
                            @error('banner')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Options</h3>
                <div class="space-y-4">
                    <!-- Vérification -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="verified" 
                               id="verified" 
                               value="1"
                               {{ old('verified', $creator->verified) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="verified" class="ml-2 block text-sm text-gray-900">
                            Créateur vérifié
                            <span class="text-gray-500 block text-xs">Marquer ce créateur comme vérifié</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Informations système -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Informations système</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Créé le :</span>
                        {{ $creator->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Modifié le :</span>
                        {{ $creator->updated_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Nombre de vidéos :</span>
                        {{ $creator->videos()->count() }}
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.creators.show', $creator) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    Annuler
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>
                    Sauvegarder les modifications
                </button>
            </div>
        </form>
    </div>

    <script>
        // Prévisualisation des images
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatar-preview');
                    preview.innerHTML = `<img src="${e.target.result}" class="h-16 w-16 rounded-full object-cover">`;
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('banner').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('banner-preview');
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-24 rounded-lg object-cover">`;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection 