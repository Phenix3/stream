<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'read',
        'read_at',
        'action_url',
        'icon',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Les types de notifications possibles
     */
    const TYPE_NEW_VIDEO = 'new_video';
    const TYPE_DOWNLOAD_READY = 'download_ready';
    const TYPE_SYSTEM = 'system';
    const TYPE_PROMOTION = 'promotion';
    const TYPE_UPDATE = 'update';
    const TYPE_REMINDER = 'reminder';
    const TYPE_SOCIAL = 'social';
    const TYPE_SECURITY = 'security';

    /**
     * Les priorités de notifications
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour les notifications non lues
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope pour les notifications lues
     */
    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    /**
     * Scope pour un type spécifique
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour une priorité spécifique
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope pour les notifications récentes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope pour ordonner par date de création
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope pour les notifications prioritaires
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Marquer la notification comme lue
     */
    public function markAsRead(): bool
    {
        if ($this->read) {
            return true;
        }

        return $this->update([
            'read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Marquer la notification comme non lue
     */
    public function markAsUnread(): bool
    {
        return $this->update([
            'read' => false,
            'read_at' => null
        ]);
    }

    /**
     * Vérifier si la notification est lue
     */
    public function isRead(): bool
    {
        return $this->read === true;
    }

    /**
     * Vérifier si la notification est non lue
     */
    public function isUnread(): bool
    {
        return $this->read === false;
    }

    /**
     * Créer une nouvelle notification
     */
    public static function createNotification(
        int $userId,
        string $title,
        string $message,
        string $type = self::TYPE_SYSTEM,
        array $data = [],
        string $priority = self::PRIORITY_NORMAL
    ): self {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'priority' => $priority,
            'read' => false,
        ]);
    }

    /**
     * Créer une notification de nouvelle vidéo
     */
    public static function createNewVideoNotification(int $userId, int $videoId, string $videoTitle): self
    {
        return self::createNotification(
            $userId,
            'Nouvelle vidéo disponible',
            "Découvrez la nouvelle vidéo : {$videoTitle}",
            self::TYPE_NEW_VIDEO,
            ['video_id' => $videoId],
            self::PRIORITY_NORMAL
        );
    }

    /**
     * Créer une notification de téléchargement prêt
     */
    public static function createDownloadReadyNotification(int $userId, int $downloadId, string $videoTitle): self
    {
        return self::createNotification(
            $userId,
            'Téléchargement prêt',
            "Votre téléchargement de \"{$videoTitle}\" est prêt",
            self::TYPE_DOWNLOAD_READY,
            ['download_id' => $downloadId],
            self::PRIORITY_HIGH
        );
    }

    /**
     * Créer une notification système
     */
    public static function createSystemNotification(int $userId, string $title, string $message, string $priority = self::PRIORITY_NORMAL): self
    {
        return self::createNotification(
            $userId,
            $title,
            $message,
            self::TYPE_SYSTEM,
            [],
            $priority
        );
    }

    /**
     * Obtenir le nombre de notifications non lues pour un utilisateur
     */
    public static function getUnreadCountForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->count();
    }

    /**
     * Marquer toutes les notifications comme lues pour un utilisateur
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->update([
            'read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Supprimer les anciennes notifications
     */
    public static function deleteOldNotifications(int $days = 30): int
    {
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Obtenir le type formaté pour l'affichage
     */
    public function getFormattedTypeAttribute(): string
    {
        return match($this->type) {
            self::TYPE_NEW_VIDEO => 'Nouvelle vidéo',
            self::TYPE_DOWNLOAD_READY => 'Téléchargement',
            self::TYPE_SYSTEM => 'Système',
            self::TYPE_PROMOTION => 'Promotion',
            self::TYPE_UPDATE => 'Mise à jour',
            self::TYPE_REMINDER => 'Rappel',
            self::TYPE_SOCIAL => 'Social',
            self::TYPE_SECURITY => 'Sécurité',
            default => 'Notification'
        };
    }

    /**
     * Obtenir la priorité formatée pour l'affichage
     */
    public function getFormattedPriorityAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Faible',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Élevée',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Normal'
        };
    }

    /**
     * Obtenir la classe CSS pour la priorité
     */
    public function getPriorityCssClassAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'text-gray-500',
            self::PRIORITY_NORMAL => 'text-blue-500',
            self::PRIORITY_HIGH => 'text-orange-500',
            self::PRIORITY_URGENT => 'text-red-500',
            default => 'text-blue-500'
        };
    }

    /**
     * Obtenir l'URL de l'icône avec fallback
     */
    public function getIconUrlAttribute(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        return match($this->type) {
            self::TYPE_NEW_VIDEO => asset('images/icons/video.svg'),
            self::TYPE_DOWNLOAD_READY => asset('images/icons/download.svg'),
            self::TYPE_SYSTEM => asset('images/icons/system.svg'),
            self::TYPE_PROMOTION => asset('images/icons/promotion.svg'),
            self::TYPE_UPDATE => asset('images/icons/update.svg'),
            self::TYPE_REMINDER => asset('images/icons/reminder.svg'),
            self::TYPE_SOCIAL => asset('images/icons/social.svg'),
            self::TYPE_SECURITY => asset('images/icons/security.svg'),
            default => asset('images/icons/notification.svg')
        };
    }
} 