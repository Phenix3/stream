<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
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
        'type',
        'platform',
        'metadata',
        'timestamp',
        'ip_address',
        'user_agent',
        'device_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Les types d'activités possibles
     */
    const TYPE_LIKE = 'like';
    const TYPE_WATCH = 'watch';
    const TYPE_DOWNLOAD = 'download';
    const TYPE_SHARE = 'share';
    const TYPE_FAVORITE = 'favorite';
    const TYPE_SEARCH = 'search';
    const TYPE_COMMENT = 'comment';
    const TYPE_RATING = 'rating';

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
     * Scope pour un type d'activité spécifique
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour une plateforme spécifique
     */
    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope pour les activités récentes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('timestamp', '>=', now()->subDays($days));
    }

    /**
     * Scope pour ordonner par timestamp
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('timestamp', 'desc');
    }

    /**
     * Scope pour filtrer par type de device
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope pour les activités de visionnage
     */
    public function scopeWatchActivities($query)
    {
        return $query->where('type', self::TYPE_WATCH);
    }

    /**
     * Scope pour les activités de partage
     */
    public function scopeShareActivities($query)
    {
        return $query->where('type', self::TYPE_SHARE);
    }

    /**
     * Scope pour les activités de téléchargement
     */
    public function scopeDownloadActivities($query)
    {
        return $query->where('type', self::TYPE_DOWNLOAD);
    }

    /**
     * Enregistrer une nouvelle activité
     */
    public static function recordActivity(
        int $userId,
        string $type,
        ?int $videoId = null,
        ?string $platform = null,
        array $metadata = []
    ): self {
        return self::create([
            'user_id' => $userId,
            'video_id' => $videoId,
            'type' => $type,
            'platform' => $platform,
            'metadata' => $metadata,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_type' => self::detectDeviceType(request()->userAgent()),
        ]);
    }

    /**
     * Enregistrer une activité de visionnage
     */
    public static function recordWatch(int $userId, int $videoId, array $metadata = []): self
    {
        return self::recordActivity($userId, self::TYPE_WATCH, $videoId, null, $metadata);
    }

    /**
     * Enregistrer une activité de partage
     */
    public static function recordShare(int $userId, int $videoId, string $platform, array $metadata = []): self
    {
        return self::recordActivity($userId, self::TYPE_SHARE, $videoId, $platform, $metadata);
    }

    /**
     * Enregistrer une activité de téléchargement
     */
    public static function recordDownload(int $userId, int $videoId, array $metadata = []): self
    {
        return self::recordActivity($userId, self::TYPE_DOWNLOAD, $videoId, null, $metadata);
    }

    /**
     * Enregistrer une activité de mise en favori
     */
    public static function recordFavorite(int $userId, int $videoId, array $metadata = []): self
    {
        return self::recordActivity($userId, self::TYPE_FAVORITE, $videoId, null, $metadata);
    }

    /**
     * Enregistrer une activité de recherche
     */
    public static function recordSearch(int $userId, string $searchTerm, array $metadata = []): self
    {
        $metadata['search_term'] = $searchTerm;
        return self::recordActivity($userId, self::TYPE_SEARCH, null, null, $metadata);
    }

    /**
     * Détecter le type de device à partir du User-Agent
     */
    private static function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false || strpos($userAgent, 'iphone') !== false) {
            return 'mobile';
        } elseif (strpos($userAgent, 'tablet') !== false || strpos($userAgent, 'ipad') !== false) {
            return 'tablet';
        } elseif (strpos($userAgent, 'tv') !== false || strpos($userAgent, 'smart-tv') !== false) {
            return 'tv';
        } else {
            return 'desktop';
        }
    }

    /**
     * Obtenir le type d'activité formaté
     */
    public function getFormattedTypeAttribute(): string
    {
        return match($this->type) {
            self::TYPE_LIKE => 'J\'aime',
            self::TYPE_WATCH => 'Visionnage',
            self::TYPE_DOWNLOAD => 'Téléchargement',
            self::TYPE_SHARE => 'Partage',
            self::TYPE_FAVORITE => 'Favori',
            self::TYPE_SEARCH => 'Recherche',
            self::TYPE_COMMENT => 'Commentaire',
            self::TYPE_RATING => 'Note',
            default => 'Activité'
        };
    }

    /**
     * Obtenir une description de l'activité
     */
    public function getDescriptionAttribute(): string
    {
        $action = $this->formatted_type;
        
        if ($this->video) {
            return "{$action} - {$this->video->title}";
        } elseif ($this->type === self::TYPE_SEARCH && isset($this->metadata['search_term'])) {
            return "{$action} - \"{$this->metadata['search_term']}\"";
        } else {
            return $action;
        }
    }

    /**
     * Obtenir les statistiques d'activité pour un utilisateur
     */
    public static function getUserStats(int $userId, int $days = 30): array
    {
        $activities = self::forUser($userId)->recent($days);
        
        return [
            'total' => $activities->count(),
            'watch' => $activities->byType(self::TYPE_WATCH)->count(),
            'share' => $activities->byType(self::TYPE_SHARE)->count(),
            'download' => $activities->byType(self::TYPE_DOWNLOAD)->count(),
            'favorite' => $activities->byType(self::TYPE_FAVORITE)->count(),
            'search' => $activities->byType(self::TYPE_SEARCH)->count(),
        ];
    }
} 