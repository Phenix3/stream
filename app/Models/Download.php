<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Download extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'video_id',
        'download_url',
        'file_size',
        'quality',
        'format',
        'status',
        'expires_at',
        'downloaded_at',
        'device_type',
        'file_path',
        'progress',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'progress' => 'integer',
        'expires_at' => 'datetime',
        'downloaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Les statuts possibles pour un téléchargement
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_READY = 'ready';
    const STATUS_DOWNLOADING = 'downloading';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la vidéo
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour une vidéo spécifique
     */
    public function scopeForVideo($query, $videoId)
    {
        return $query->where('video_id', $videoId);
    }

    /**
     * Scope pour les téléchargements par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les téléchargements complétés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope pour les téléchargements en cours
     */
    public function scopeInProgress($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_DOWNLOADING
        ]);
    }

    /**
     * Scope pour les téléchargements expirés
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                    ->orWhere('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope pour les téléchargements prêts
     */
    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY)
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope pour filtrer par qualité
     */
    public function scopeByQuality($query, $quality)
    {
        return $query->where('quality', $quality);
    }

    /**
     * Scope pour filtrer par format
     */
    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope pour ordonner par date de téléchargement
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('downloaded_at', 'desc');
    }

    /**
     * Vérifier si le téléchargement est expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now() || $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Vérifier si le téléchargement est prêt
     */
    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY && !$this->isExpired();
    }

    /**
     * Vérifier si le téléchargement est complété
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Vérifier si le téléchargement est en cours
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_DOWNLOADING
        ]);
    }

    /**
     * Marquer le téléchargement comme commencé
     */
    public function markAsStarted(): bool
    {
        return $this->update([
            'status' => self::STATUS_DOWNLOADING,
            'downloaded_at' => now()
        ]);
    }

    /**
     * Marquer le téléchargement comme complété
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'progress' => 100
        ]);
    }

    /**
     * Marquer le téléchargement comme échoué
     */
    public function markAsFailed(): bool
    {
        return $this->update(['status' => self::STATUS_FAILED]);
    }

    /**
     * Marquer le téléchargement comme expiré
     */
    public function markAsExpired(): bool
    {
        return $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Mettre à jour le progrès du téléchargement
     */
    public function updateProgress(int $progress): bool
    {
        return $this->update(['progress' => min(100, max(0, $progress))]);
    }

    /**
     * Obtenir la taille du fichier formatée
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Obtenir le temps restant avant expiration
     */
    public function getTimeUntilExpirationAttribute(): ?int
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return $this->expires_at->diffInSeconds(now());
    }

    /**
     * Obtenir le statut formaté pour l'affichage
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_PROCESSING => 'Traitement en cours',
            self::STATUS_READY => 'Prêt',
            self::STATUS_DOWNLOADING => 'Téléchargement en cours',
            self::STATUS_COMPLETED => 'Terminé',
            self::STATUS_FAILED => 'Échec',
            self::STATUS_EXPIRED => 'Expiré',
            default => 'Inconnu'
        };
    }

    /**
     * Demander un nouveau téléchargement
     */
    public static function requestDownload($userId, $videoId, $quality = '720p', $format = 'mp4'): self
    {
        return self::create([
            'user_id' => $userId,
            'video_id' => $videoId,
            'quality' => $quality,
            'format' => $format,
            'status' => self::STATUS_PENDING,
            'expires_at' => now()->addHours(24), // Expire dans 24h
            'progress' => 0
        ]);
    }
} 