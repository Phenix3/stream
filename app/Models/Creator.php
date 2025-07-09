<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Creator extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'avatar',
        'subscriber_count',
        'description',
        'verified',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subscriber_count' => 'integer',
        'verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les vidéos
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    /**
     * Scope pour les créateurs vérifiés
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope pour rechercher par nom
     */
    public function scopeSearchByName($query, $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%");
    }

    /**
     * Scope pour les créateurs populaires (par nombre d'abonnés)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('subscriber_count', 'desc');
    }

    /**
     * Obtenir l'URL de l'avatar avec fallback
     */
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ?? asset('images/default-creator-avatar.jpg');
    }

    /**
     * Obtenir le nombre total de vues de toutes les vidéos du créateur
     */
    public function getTotalViewsAttribute(): int
    {
        return $this->videos()->sum('views');
    }

    /**
     * Obtenir le nombre de vidéos du créateur
     */
    public function getVideosCountAttribute(): int
    {
        return $this->videos()->count();
    }

    /**
     * Obtenir le nombre de vidéos publiques du créateur
     */
    public function getPublicVideosCountAttribute(): int
    {
        return $this->videos()->public()->count();
    }

    /**
     * Formater le nombre d'abonnés pour l'affichage
     */
    public function getFormattedSubscriberCountAttribute(): string
    {
        $count = $this->subscriber_count;
        
        if ($count >= 1000000) {
            return round($count / 1000000, 1) . 'M';
        } elseif ($count >= 1000) {
            return round($count / 1000, 1) . 'K';
        }
        
        return (string) $count;
    }

    /**
     * Vérifier si le créateur est vérifié
     */
    public function isVerified(): bool
    {
        return $this->verified === true;
    }
}