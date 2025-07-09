<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Creator;
use App\Models\Genre;
use App\Models\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $query = Video::with(['creator', 'genres']);

        // Recherche
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filtres
        if ($request->has('visibility')) {
            $query->where('visibility', $request->get('visibility'));
        }

        if ($request->has('creator_id')) {
            $query->where('creator_id', $request->get('creator_id'));
        }

        $videos = $query->withCount(['favorites', 'downloads', 'watchHistories'])
                       ->orderBy('created_at', 'desc')
                       ->paginate(20);

        $creators = Creator::all();
        $visibilityOptions = ['public', 'private', 'unlisted'];

        return view('admin.videos.index', compact('videos', 'creators', 'visibilityOptions'));
    }

    public function show(Video $video)
    {
        $video->load(['creator', 'genres', 'actors', 'favorites.user', 'downloads.user', 'watchHistories.user']);
        
        $stats = [
            'totalViews' => $video->views,
            'totalFavorites' => $video->favorites->count(),
            'totalDownloads' => $video->downloads->count(),
            'totalWatchTime' => $video->watchHistories->sum('watched_duration'),
            'averageWatchTime' => $video->watchHistories->avg('watched_duration'),
            'completionRate' => $video->watchHistories->where('completed', true)->count() / max($video->watchHistories->count(), 1) * 100,
        ];

        return view('admin.videos.show', compact('video', 'stats'));
    }

    public function create()
    {
        $creators = Creator::all();
        $genres = Genre::all();
        $actors = Actor::all();
        $visibilityOptions = ['public', 'private', 'unlisted'];

        return view('admin.videos.create', compact('creators', 'genres', 'actors', 'visibilityOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'content_url' => ['required', 'string', 'url'],
            'creator_id' => ['required', 'exists:creators,id'],
            'duration' => ['required', 'string'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'visibility' => ['required', 'in:public,private,unlisted'],
            'season' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['exists:genres,id'],
            'actors' => ['nullable', 'array'],
            'actors.*' => ['exists:actors,id'],
        ]);

        $videoData = [
            'title' => $request->title,
            'description' => $request->description,
            'content_url' => $request->content_url,
            'creator_id' => $request->creator_id,
            'duration' => $request->duration,
            'rating' => $request->rating ?? 0,
            'visibility' => $request->visibility,
            'season' => $request->season,
            'views' => 0,
        ];

        // Gérer l'upload de thumbnail
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $videoData['thumbnail'] = $path;
        }

        $video = Video::create($videoData);

        // Associer les genres
        if ($request->has('genres')) {
            $video->genres()->sync($request->genres);
        }

        // Associer les acteurs
        if ($request->has('actors')) {
            $video->actors()->sync($request->actors);
        }

        return redirect()->route('admin.videos.show', $video)
                        ->with('success', 'Vidéo créée avec succès.');
    }

    public function edit(Video $video)
    {
        $creators = Creator::all();
        $genres = Genre::all();
        $actors = Actor::all();
        $visibilityOptions = ['public', 'private', 'unlisted'];

        return view('admin.videos.edit', compact('video', 'creators', 'genres', 'actors', 'visibilityOptions'));
    }

    public function update(Request $request, Video $video)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'content_url' => ['required', 'string', 'url'],
            'creator_id' => ['required', 'exists:creators,id'],
            'duration' => ['required', 'string'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'visibility' => ['required', 'in:public,private,unlisted'],
            'season' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['exists:genres,id'],
            'actors' => ['nullable', 'array'],
            'actors.*' => ['exists:actors,id'],
        ]);

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'content_url' => $request->content_url,
            'creator_id' => $request->creator_id,
            'duration' => $request->duration,
            'rating' => $request->rating ?? 0,
            'visibility' => $request->visibility,
            'season' => $request->season,
        ];

        // Gérer l'upload de thumbnail
        if ($request->hasFile('thumbnail')) {
            // Supprimer l'ancienne thumbnail
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }
            
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $updateData['thumbnail'] = $path;
        }

        $video->update($updateData);

        // Mettre à jour les genres
        if ($request->has('genres')) {
            $video->genres()->sync($request->genres);
        }

        // Mettre à jour les acteurs
        if ($request->has('actors')) {
            $video->actors()->sync($request->actors);
        }

        return redirect()->route('admin.videos.show', $video)
                        ->with('success', 'Vidéo mise à jour avec succès.');
    }

    public function destroy(Video $video)
    {
        // Supprimer la thumbnail
        if ($video->thumbnail) {
            Storage::disk('public')->delete($video->thumbnail);
        }

        $video->delete();

        return redirect()->route('admin.videos.index')
                        ->with('success', 'Vidéo supprimée avec succès.');
    }

    public function bulkUpdateVisibility(Request $request)
    {
        $request->validate([
            'video_ids' => ['required', 'array'],
            'video_ids.*' => ['exists:videos,id'],
            'visibility' => ['required', 'in:public,private,unlisted'],
        ]);

        Video::whereIn('id', $request->video_ids)
             ->update(['visibility' => $request->visibility]);

        $count = count($request->video_ids);
        return back()->with('success', "{$count} vidéo(s) mises à jour avec succès.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'video_ids' => ['required', 'array'],
            'video_ids.*' => ['exists:videos,id'],
        ]);

        $videos = Video::whereIn('id', $request->video_ids)->get();
        
        foreach ($videos as $video) {
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }
        }

        Video::whereIn('id', $request->video_ids)->delete();

        $count = count($request->video_ids);
        return back()->with('success', "{$count} vidéo(s) supprimée(s) avec succès.");
    }
} 