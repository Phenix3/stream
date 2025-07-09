<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Models\Creator;
use App\Models\Genre;
use App\Models\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    /**
     * Obtenir toutes les vidéos (admin)
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visibility' => ['sometimes', 'string', 'in:public,private,unlisted'],
            'creator_id' => ['sometimes', 'integer', 'exists:creators,id'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'search' => ['sometimes', 'string', 'min:2'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $query = Video::with(['creator', 'genres', 'actors']);

        // Filtrer par visibilité
        if ($request->has('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        // Filtrer par créateur
        if ($request->has('creator_id')) {
            $query->where('creator_id', $request->creator_id);
        }

        // Recherche
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        $totalCount = $query->count();
        $videos = $query->orderBy('created_at', 'desc')
                       ->skip(($page - 1) * $limit)
                       ->take($limit)
                       ->get();

        $totalPages = ceil($totalCount / $limit);

        return ApiResponse::success([
            'videos' => VideoResource::collection($videos),
            'totalCount' => $totalCount,
            'page' => (int) $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Créer une nouvelle vidéo
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'content_url' => ['required', 'string', 'url'],
            'creator_id' => ['required', 'integer', 'exists:creators,id'],
            'duration' => ['required', 'string'],
            'rating' => ['sometimes', 'numeric', 'min:0', 'max:5'],
            'visibility' => ['required', 'string', 'in:public,private,unlisted'],
            'season' => ['sometimes', 'string'],
            'thumbnail' => ['sometimes', 'string'], // Base64 ou URL
            'genres' => ['sometimes', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
            'actors' => ['sometimes', 'array'],
            'actors.*' => ['integer', 'exists:actors,id'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $videoData = [
            'title' => $request->title,
            'description' => $request->description,
            'content_url' => $request->content_url,
            'creator_id' => $request->creator_id,
            'duration' => $request->duration,
            'rating' => $request->get('rating', 0),
            'visibility' => $request->visibility,
            'season' => $request->get('season'),
            'views' => 0,
        ];

        // Gérer l'upload de thumbnail
        if ($request->has('thumbnail')) {
            $thumbnailPath = $this->handleThumbnailUpload($request->thumbnail);
            if ($thumbnailPath) {
                $videoData['thumbnail'] = $thumbnailPath;
            }
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

        $video->load(['creator', 'genres', 'actors']);

        return ApiResponse::success([
            'video' => new VideoResource($video),
        ], 'Vidéo créée avec succès', 201);
    }

    /**
     * Afficher une vidéo spécifique
     */
    public function show($videoId)
    {
        $video = Video::with(['creator', 'genres', 'actors'])->find($videoId);

        if (!$video) {
            return ApiResponse::notFound('Vidéo non trouvée');
        }

        return ApiResponse::success([
            'video' => new VideoResource($video),
        ]);
    }

    /**
     * Mettre à jour une vidéo
     */
    public function update(Request $request, $videoId)
    {
        $video = Video::find($videoId);

        if (!$video) {
            return ApiResponse::notFound('Vidéo non trouvée');
        }

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'content_url' => ['sometimes', 'string', 'url'],
            'creator_id' => ['sometimes', 'integer', 'exists:creators,id'],
            'duration' => ['sometimes', 'string'],
            'rating' => ['sometimes', 'numeric', 'min:0', 'max:5'],
            'visibility' => ['sometimes', 'string', 'in:public,private,unlisted'],
            'season' => ['sometimes', 'string'],
            'thumbnail' => ['sometimes', 'string'],
            'genres' => ['sometimes', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
            'actors' => ['sometimes', 'array'],
            'actors.*' => ['integer', 'exists:actors,id'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $updateData = $request->only([
            'title', 'description', 'content_url', 'creator_id',
            'duration', 'rating', 'visibility', 'season'
        ]);

        // Gérer l'upload de thumbnail
        if ($request->has('thumbnail')) {
            $thumbnailPath = $this->handleThumbnailUpload($request->thumbnail);
            if ($thumbnailPath) {
                // Supprimer l'ancienne thumbnail
                if ($video->thumbnail) {
                    Storage::disk('public')->delete($video->thumbnail);
                }
                $updateData['thumbnail'] = $thumbnailPath;
            }
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

        $video->load(['creator', 'genres', 'actors']);

        return ApiResponse::success([
            'video' => new VideoResource($video),
        ], 'Vidéo mise à jour avec succès');
    }

    /**
     * Supprimer une vidéo
     */
    public function destroy($videoId)
    {
        $video = Video::find($videoId);

        if (!$video) {
            return ApiResponse::notFound('Vidéo non trouvée');
        }

        // Supprimer la thumbnail
        if ($video->thumbnail) {
            Storage::disk('public')->delete($video->thumbnail);
        }

        // Supprimer les relations
        $video->genres()->detach();
        $video->actors()->detach();

        // Supprimer la vidéo
        $video->delete();

        return ApiResponse::success(null, 'Vidéo supprimée avec succès');
    }

    /**
     * Changer la visibilité de plusieurs vidéos
     */
    public function bulkUpdateVisibility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_ids' => ['required', 'array'],
            'video_ids.*' => ['integer', 'exists:videos,id'],
            'visibility' => ['required', 'string', 'in:public,private,unlisted'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $updatedCount = Video::whereIn('id', $request->video_ids)
            ->update(['visibility' => $request->visibility]);

        return ApiResponse::success([
            'updatedCount' => $updatedCount,
        ], 'Visibilité mise à jour pour ' . $updatedCount . ' vidéo(s)');
    }

    /**
     * Supprimer plusieurs vidéos
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_ids' => ['required', 'array'],
            'video_ids.*' => ['integer', 'exists:videos,id'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $videos = Video::whereIn('id', $request->video_ids)->get();

        foreach ($videos as $video) {
            // Supprimer la thumbnail
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }

            // Supprimer les relations
            $video->genres()->detach();
            $video->actors()->detach();
        }

        $deletedCount = Video::whereIn('id', $request->video_ids)->delete();

        return ApiResponse::success([
            'deletedCount' => $deletedCount,
        ], 'Suppression de ' . $deletedCount . ' vidéo(s) effectuée');
    }

    /**
     * Gérer l'upload de thumbnail
     */
    private function handleThumbnailUpload(string $thumbnailData): ?string
    {
        try {
            // Si c'est une URL, la retourner telle quelle
            if (filter_var($thumbnailData, FILTER_VALIDATE_URL)) {
                return $thumbnailData;
            }

            // Sinon, traiter comme du base64
            if (!preg_match('/^data:image\/(\w+);base64,/', $thumbnailData, $matches)) {
                return null;
            }

            $imageType = $matches[1];
            $base64Data = substr($thumbnailData, strpos($thumbnailData, ',') + 1);
            $imageData = base64_decode($base64Data);

            if ($imageData === false) {
                return null;
            }

            // Générer un nom de fichier unique
            $fileName = 'thumbnails/' . uniqid() . '.' . $imageType;

            // Sauvegarder le fichier
            Storage::disk('public')->put($fileName, $imageData);

            return $fileName;

        } catch (\Exception $e) {
            return null;
        }
    }
} 