<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Recherche
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Filtres
        if ($request->has('status')) {
            switch ($request->get('status')) {
                case 'verified':
                    $query->whereNotNull('email_verified_at');
                    break;
                case 'unverified':
                    $query->whereNull('email_verified_at');
                    break;
                case 'phone_verified':
                    $query->whereNotNull('phone_verified_at');
                    break;
                case 'admin':
                    $query->where('is_admin', true);
                    break;
            }
        }

        $users = $query->withCount(['favorites', 'downloads', 'watchHistories'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['favorites.video', 'downloads.video', 'watchHistories.video', 'activities']);
        
        $stats = [
            'totalFavorites' => $user->favorites->count(),
            'totalDownloads' => $user->downloads->count(),
            'totalWatchTime' => $user->watchHistories->sum('watched_duration'),
            'completedVideos' => $user->watchHistories->where('completed', true)->count(),
            'activitiesCount' => $user->activities->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'is_admin' => ['boolean'],
            'email_verified' => ['boolean'],
            'phone_verified' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_admin' => $request->boolean('is_admin'),
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
            'phone_verified_at' => $request->boolean('phone_verified') ? now() : null,
        ]);

        return redirect()->route('admin.users.show', $user)
                        ->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'is_admin' => ['boolean'],
            'email_verified' => ['boolean'],
            'phone_verified' => ['boolean'],
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_admin' => $request->boolean('is_admin'),
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
            'phone_verified_at' => $request->boolean('phone_verified') ? now() : null,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.show', $user)
                        ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        // Empêcher la suppression du dernier admin
        if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
            return back()->with('error', 'Impossible de supprimer le dernier administrateur.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleAdmin(User $user)
    {
        // Empêcher de retirer les droits admin du dernier admin
        if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
            return back()->with('error', 'Impossible de retirer les droits admin du dernier administrateur.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $status = $user->is_admin ? 'accordés' : 'retirés';
        return back()->with('success', "Droits d'administration {$status} avec succès.");
    }

    public function verifyEmail(User $user)
    {
        $user->update(['email_verified_at' => now()]);
        return back()->with('success', 'Email vérifié avec succès.');
    }

    public function verifyPhone(User $user)
    {
        $user->update(['phone_verified_at' => now()]);
        return back()->with('success', 'Téléphone vérifié avec succès.');
    }
} 