<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
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
        'favorited_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'favorited_at' => 'datetime',
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
     * Scope pour les favoris récents
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('favorited_at', '>=', now()->subDays($days));
    }

    /**
     * Scope pour ordonner par date de favori
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('favorited_at', 'desc');
    }

    /**
     * Scope pour ordonner par date de favori (plus ancien en premier)
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('favorited_at', 'asc');
    }

    /**
     * Vérifier si un utilisateur a mis une vidéo en favori
     */
    public static function isFavorited($userId, $videoId): bool
    {
        return self::forUser($userId)->forVideo($videoId)->exists();
    }

    /**
     * Ajouter ou retirer un favori (toggle)
     */
    public static function toggle($userId, $videoId): array
    {
        $favorite = self::forUser($userId)->forVideo($videoId)->first();
        
        if ($favorite) {
            $favorite->delete();
            return [
                'action' => 'removed',
                'isFavorite' => false,
                'message' => 'Vidéo retirée des favoris'
            ];
        } else {
            self::create([
                'user_id' => $userId,
                'video_id' => $videoId,
                'favorited_at' => now()
            ]);
            return [
                'action' => 'added',
                'isFavorite' => true,
                'message' => 'Vidéo ajoutée aux favoris'
            ];
        }
    }

    /**
     * Obtenir le nombre de favoris pour une vidéo
     */
    public static function countForVideo($videoId): int
    {
        return self::forVideo($videoId)->count();
    }

    /**
     * Obtenir le nombre de favoris pour un utilisateur
     */
    public static function countForUser($userId): int
    {
        return self::forUser($userId)->count();
    }
} 