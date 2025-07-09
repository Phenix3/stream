<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\DownloadResource;
use App\Models\Video;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DownloadController extends Controller
{
    /**
     * Obtenir les téléchargements de l'utilisateur
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', 'in:pending,ready,expired,error'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $query = $user->downloads()->with(['video.creator', 'video.genres']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $totalCount = $query->count();
        $downloads = $query->orderBy('created_at', 'desc')
                          ->skip(($page - 1) * $limit)
                          ->take($limit)
                          ->get();

        $totalPages = ceil($totalCount / $limit);

        return ApiResponse::success([
            'downloads' => DownloadResource::collection($downloads),
            'totalCount' => $totalCount,
            'page' => (int) $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Demander un téléchargement
     */
    public function request(Request $request, $videoId)
    {
        $validator = Validator::make($request->all(), [
            'quality' => ['sometimes', 'string', 'in:720p,1080p,480p'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $video = Video::find($videoId);

        if (!$video) {
            return ApiResponse::notFound('Vidéo non trouvée');
        }

        if (!$video->isPublic()) {
            return ApiResponse::forbidden('Cette vidéo n\'est pas disponible en téléchargement');
        }

        $user = $request->user();
        $quality = $request->get('quality', '720p');

        // Vérifier si un téléchargement existe déjà pour cette vidéo
        $existingDownload = $user->downloads()
            ->where('video_id', $videoId)
            ->where('quality', $quality)
            ->where('status', '!=', 'expired')
            ->first();

        if ($existingDownload) {
            if ($existingDownload->status === 'ready') {
                return ApiResponse::success([
                    'download' => new DownloadResource($existingDownload),
                ], 'Téléchargement déjà disponible');
            } else {
                return ApiResponse::error('Un téléchargement est déjà en cours pour cette vidéo', 400, 'DOWNLOAD_IN_PROGRESS');
            }
        }

        // Créer un nouveau téléchargement
        $download = $user->downloads()->create([
            'video_id' => $videoId,
            'quality' => $quality,
            'status' => 'pending',
            'download_url' => null,
            'file_size' => null,
            'expires_at' => now()->addHours(24), // Expire dans 24h
        ]);

        // Simuler la génération du lien de téléchargement
        // Dans une vraie application, cela déclencherait un job en arrière-plan
        $this->generateDownloadLink($download);

        return ApiResponse::success([
            'download' => new DownloadResource($download->fresh()),
        ], 'Téléchargement demandé avec succès');
    }

    /**
     * Obtenir un téléchargement spécifique
     */
    public function show(Request $request, $downloadId)
    {
        $user = $request->user();
        $download = $user->downloads()
            ->with(['video.creator', 'video.genres'])
            ->find($downloadId);

        if (!$download) {
            return ApiResponse::notFound('Téléchargement non trouvé');
        }

        return ApiResponse::success([
            'download' => new DownloadResource($download),
        ]);
    }

    /**
     * Supprimer un téléchargement
     */
    public function destroy(Request $request, $downloadId)
    {
        $user = $request->user();
        $download = $user->downloads()->find($downloadId);

        if (!$download) {
            return ApiResponse::notFound('Téléchargement non trouvé');
        }

        $download->delete();

        return ApiResponse::success(null, 'Téléchargement supprimé avec succès');
    }

    /**
     * Supprimer tous les téléchargements expirés
     */
    public function clearExpired(Request $request)
    {
        $user = $request->user();
        $deletedCount = $user->downloads()
            ->where('expires_at', '<', now())
            ->delete();

        return ApiResponse::success([
            'deletedCount' => $deletedCount,
        ], 'Téléchargements expirés supprimés');
    }

    /**
     * Générer un lien de téléchargement (simulation)
     */
    private function generateDownloadLink(Download $download)
    {
        // Dans une vraie application, cela serait fait par un job en arrière-plan
        // qui génèrerait le fichier vidéo et stockerait le lien
        
        $downloadUrl = route('api.downloads.file', ['token' => Str::random(32)]);
        $fileSize = $this->estimateFileSize($download->quality);

        $download->update([
            'status' => 'ready',
            'download_url' => $downloadUrl,
            'file_size' => $fileSize,
        ]);
    }

    /**
     * Estimer la taille du fichier basé sur la qualité
     */
    private function estimateFileSize(string $quality): string
    {
        $sizes = [
            '480p' => '800MB',
            '720p' => '1.2GB',
            '1080p' => '2.1GB',
        ];

        return $sizes[$quality] ?? '1.2GB';
    }

    /**
     * Télécharger le fichier (endpoint pour les liens de téléchargement)
     */
    public function downloadFile(Request $request, $token)
    {
        // Dans une vraie application, on vérifierait le token et servirait le fichier
        // Pour cette démo, on retourne une erreur
        return ApiResponse::error('Fonctionnalité de téléchargement de fichier non implémentée', 501, 'NOT_IMPLEMENTED');
    }
} 