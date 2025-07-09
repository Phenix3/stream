<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchHistory extends Model
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
        'watched_duration',
        'total_duration',
        'completed',
        'watched_at',
        'last_position',
        'device_type',
        'quality',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'watched_duration' => 'integer',
        'total_duration' => 'integer',
        'last_position' => 'integer',
        'completed' => 'boolean',
        'watched_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
     * Scope pour les vidéos complétées
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    /**
     * Scope pour les vidéos non complétées
     */
    public function scopeIncomplete($query)
    {
        return $query->where('completed', false);
    }

    /**
     * Scope pour l'historique récent
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('watched_at', '>=', now()->subDays($days));
    }

    /**
     * Scope pour ordonner par date de visionnage
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('watched_at', 'desc');
    }

    /**
     * Scope pour filtrer par type de device
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope pour filtrer par qualité
     */
    public function scopeByQuality($query, $quality)
    {
        return $query->where('quality', $quality);
    }

    /**
     * Enregistrer ou mettre à jour l'historique de visionnage
     */
    public static function recordWatchTime($userId, $videoId, $watchedDuration, $totalDuration, $data = []): self
    {
        $history = self::forUser($userId)->forVideo($videoId)->first();
        
        $watchData = array_merge([
            'user_id' => $userId,
            'video_id' => $videoId,
            'watched_duration' => $watchedDuration,
            'total_duration' => $totalDuration,
            'completed' => $watchedDuration >= $totalDuration * 0.95, // 95% = complété
            'watched_at' => now(),
            'last_position' => $watchedDuration,
        ], $data);

        if ($history) {
            // Mettre à jour l'historique existant
            $history->update($watchData);
            return $history;
        } else {
            // Créer un nouvel enregistrement
            return self::create($watchData);
        }
    }

    /**
     * Obtenir le pourcentage de progression
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_duration <= 0) {
            return 0;
        }
        
        return round(($this->watched_duration / $this->total_duration) * 100, 2);
    }

    /**
     * Obtenir la durée restante en secondes
     */
    public function getRemainingDurationAttribute(): int
    {
        return max(0, $this->total_duration - $this->watched_duration);
    }

    /**
     * Obtenir la durée visionnée formatée (HH:MM:SS)
     */
    public function getFormattedWatchedDurationAttribute(): string
    {
        return $this->formatDuration($this->watched_duration);
    }

    /**
     * Obtenir la durée totale formatée (HH:MM:SS)
     */
    public function getFormattedTotalDurationAttribute(): string
    {
        return $this->formatDuration($this->total_duration);
    }

    /**
     * Obtenir la durée restante formatée (HH:MM:SS)
     */
    public function getFormattedRemainingDurationAttribute(): string
    {
        return $this->formatDuration($this->remaining_duration);
    }

    /**
     * Formater une durée en secondes vers HH:MM:SS
     */
    private function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            return sprintf('%02d:%02d', $minutes, $seconds);
        }
    }

    /**
     * Vérifier si la vidéo peut être considérée comme "en cours"
     */
    public function isInProgress(): bool
    {
        return $this->watched_duration > 0 && !$this->completed;
    }

    /**
     * Vérifier si l'utilisateur vient de commencer à regarder
     */
    public function isJustStarted(): bool
    {
        return $this->progress_percentage < 5;
    }

    /**
     * Vérifier si l'utilisateur est proche de la fin
     */
    public function isNearEnd(): bool
    {
        return $this->progress_percentage > 85 && !$this->completed;
    }
} 