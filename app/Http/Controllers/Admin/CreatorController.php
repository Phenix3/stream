<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CreatorController extends Controller
{
    public function index(Request $request)
    {
        $query = Creator::query();

        // Recherche
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filtres
        if ($request->has('verified') && $request->get('verified') !== '') {
            $query->where('verified', $request->boolean('verified'));
        }

        $creators = $query->withCount(['videos'])
                         ->withSum('videos', 'views')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        return view('admin.creators.index', compact('creators'));
    }

    public function show(Creator $creator)
    {
        $creator->load(['videos.genres']);
        
        $stats = [
            'totalVideos' => $creator->videos->count(),
            'totalViews' => $creator->videos->sum('views'),
            'averageRating' => $creator->videos->avg('rating'),
            'publicVideos' => $creator->videos->where('visibility', 'public')->count(),
            'privateVideos' => $creator->videos->where('visibility', 'private')->count(),
            'unlistedVideos' => $creator->videos->where('visibility', 'unlisted')->count(),
        ];

        // Vidéos les plus populaires
        $topVideos = $creator->videos()
                            ->orderBy('views', 'desc')
                            ->limit(10)
                            ->get();

        return view('admin.creators.show', compact('creator', 'stats', 'topVideos'));
    }

    public function create()
    {
        return view('admin.creators.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:4096'],
            'verified' => ['boolean'],
            'subscriber_count' => ['nullable', 'integer', 'min:0'],
        ]);

        $creatorData = [
            'name' => $request->name,
            'description' => $request->description,
            'verified' => $request->boolean('verified'),
            'subscriber_count' => $request->subscriber_count ?? 0,
        ];

        // Gérer l'upload d'avatar
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('creators/avatars', 'public');
            $creatorData['avatar'] = $path;
        }

        // Gérer l'upload de banner
        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('creators/banners', 'public');
            $creatorData['banner'] = $path;
        }

        $creator = Creator::create($creatorData);

        return redirect()->route('admin.creators.show', $creator)
                        ->with('success', 'Créateur créé avec succès.');
    }

    public function edit(Creator $creator)
    {
        return view('admin.creators.edit', compact('creator'));
    }

    public function update(Request $request, Creator $creator)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:4096'],
            'verified' => ['boolean'],
            'subscriber_count' => ['nullable', 'integer', 'min:0'],
        ]);

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'verified' => $request->boolean('verified'),
            'subscriber_count' => $request->subscriber_count ?? 0,
        ];

        // Gérer l'upload d'avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar
            if ($creator->avatar) {
                Storage::disk('public')->delete($creator->avatar);
            }
            
            $path = $request->file('avatar')->store('creators/avatars', 'public');
            $updateData['avatar'] = $path;
        }

        // Gérer l'upload de banner
        if ($request->hasFile('banner')) {
            // Supprimer l'ancien banner
            if ($creator->banner) {
                Storage::disk('public')->delete($creator->banner);
            }
            
            $path = $request->file('banner')->store('creators/banners', 'public');
            $updateData['banner'] = $path;
        }

        $creator->update($updateData);

        return redirect()->route('admin.creators.show', $creator)
                        ->with('success', 'Créateur mis à jour avec succès.');
    }

    public function destroy(Creator $creator)
    {
        // Vérifier s'il y a des vidéos associées
        if ($creator->videos()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer ce créateur car il a des vidéos associées.');
        }

        // Supprimer les fichiers
        if ($creator->avatar) {
            Storage::disk('public')->delete($creator->avatar);
        }
        
        if ($creator->banner) {
            Storage::disk('public')->delete($creator->banner);
        }

        $creator->delete();

        return redirect()->route('admin.creators.index')
                        ->with('success', 'Créateur supprimé avec succès.');
    }

    public function toggleVerification(Creator $creator)
    {
        $creator->update(['verified' => !$creator->verified]);

        $status = $creator->verified ? 'vérifié' : 'non vérifié';
        return back()->with('success', "Créateur marqué comme {$status} avec succès.");
    }
} 