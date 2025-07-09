@extends('layouts.admin')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-users mr-2"></i>
            {{ __('Gestion des Utilisateurs') }}
        </h2>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Nouvel Utilisateur
        </a>
    </div>
@endsection

@section('content')

    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <!-- Filtres et recherche -->
        <div class="p-6 border-b border-gray-200">
            <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4 md:space-y-0 md:flex md:items-center md:space-x-4">
                <!-- Recherche -->
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Rechercher par nom, email ou téléphone..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Filtre statut -->
                <div class="min-w-0">
                    <select name="status" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les statuts</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Email vérifié</option>
                        <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Email non vérifié</option>
                        <option value="phone_verified" {{ request('status') === 'phone_verified' ? 'selected' : '' }}>Téléphone vérifié</option>
                        <option value="admin" {{ request('status') === 'admin' ? 'selected' : '' }}>Administrateurs</option>
                    </select>
                </div>

                <!-- Boutons -->
                <div class="flex space-x-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md">
                        <i class="fas fa-filter mr-1"></i>
                        Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md">
                            <i class="fas fa-times mr-1"></i>
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tableau des utilisateurs -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Activité
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Inscription
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $user->name }}
                                            @if($user->is_admin)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-crown mr-1"></i>
                                                    Admin
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: {{ $user->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                @if($user->phone)
                                    <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-envelope-check mr-1"></i>
                                            Email vérifié
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-envelope mr-1"></i>
                                            Email non vérifié
                                        </span>
                                    @endif
                                    
                                    @if($user->phone_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-phone-check mr-1"></i>
                                            Tél. vérifié
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="space-y-1">
                                    <div>{{ $user->favorites_count }} favoris</div>
                                    <div>{{ $user->downloads_count }} téléchargements</div>
                                    <div>{{ $user->watch_histories_count }} vues</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                <div>{{ $user->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2 justify-end">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="text-blue-600 hover:text-blue-900"
                                       title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="text-indigo-600 hover:text-indigo-900"
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$user->is_admin || \App\Models\User::where('is_admin', true)->count() > 1)
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                              class="inline" 
                                              onsubmit="return confirmDelete('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                <div class="flex flex-col items-center py-8">
                                    <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500 text-lg mb-2">Aucun utilisateur trouvé</p>
                                    @if(request()->hasAny(['search', 'status']))
                                        <p class="text-gray-400 mb-4">Essayez de modifier vos critères de recherche</p>
                                        <a href="{{ route('admin.users.index') }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Voir tous les utilisateurs
                                        </a>
                                    @else
                                        <a href="{{ route('admin.users.create') }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Créer le premier utilisateur
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Statistiques rapides -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-users text-blue-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $users->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Email vérifié</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $users->where('email_verified_at', '!=', null)->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-crown text-red-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Administrateurs</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $users->where('is_admin', true)->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Nouveaux (7j)</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $users->where('created_at', '>=', now()->subDays(7))->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection 