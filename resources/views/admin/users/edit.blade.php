@extends('layouts.admin')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.users.show', $user) }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-user-edit mr-2"></i>
                Modifier l'utilisateur : {{ $user->name }}
            </h2>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                <i class="fas fa-edit mr-2 text-blue-600"></i>
                Informations de l'utilisateur
            </h3>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Nom complet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name', $user->name) }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Adresse email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email', $user->email) }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">
                        Numéro de téléphone
                    </label>
                    <input type="tel" 
                           name="phone" 
                           id="phone" 
                           value="{{ old('phone', $user->phone) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nouveau mot de passe -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Nouveau mot de passe
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Laissez vide pour conserver le mot de passe actuel</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmation mot de passe -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirmer le mot de passe
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Options de vérification et privilèges -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">
                    <i class="fas fa-cog mr-2 text-gray-600"></i>
                    Options et privilèges
                </h4>
                
                <div class="space-y-4">
                    <!-- Droits d'administration -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="is_admin" 
                               id="is_admin" 
                               value="1"
                               {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                               @if($user->is_admin && \App\Models\User::where('is_admin', true)->count() <= 1)
                                   disabled
                               @endif
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_admin" class="ml-2 block text-sm text-gray-900">
                            <i class="fas fa-crown mr-1 text-red-500"></i>
                            Droits d'administration
                            @if($user->is_admin && \App\Models\User::where('is_admin', true)->count() <= 1)
                                <span class="text-gray-500">(Dernier administrateur - impossible de retirer)</span>
                            @endif
                        </label>
                    </div>

                    <!-- Email vérifié -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="email_verified" 
                               id="email_verified" 
                               value="1"
                               {{ old('email_verified', $user->email_verified_at ? true : false) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="email_verified" class="ml-2 block text-sm text-gray-900">
                            <i class="fas fa-envelope-check mr-1 text-green-500"></i>
                            Email vérifié
                        </label>
                    </div>

                    <!-- Téléphone vérifié -->
                    @if($user->phone)
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="phone_verified" 
                                   id="phone_verified" 
                                   value="1"
                                   {{ old('phone_verified', $user->phone_verified_at ? true : false) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="phone_verified" class="ml-2 block text-sm text-gray-900">
                                <i class="fas fa-phone-check mr-1 text-blue-500"></i>
                                Téléphone vérifié
                            </label>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.users.show', $user) }}" 
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