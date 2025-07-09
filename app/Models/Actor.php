<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Actor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'profile_image',
        'biography',
        'filmography',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filmography' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les vidéos (many-to-many)
     */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'video_actors')->withTimestamps();
    }

    /**
     * Scope pour rechercher par nom
     */
    public function scopeSearchByName($query, $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%");
    }

    /**
     * Obtenir l'URL de l'image de profil avec fallback
     */
    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image ?? asset('images/default-actor-avatar.jpg');
    }

    /**
     * Obtenir le nombre de films/vidéos de l'acteur
     */
    public function getVideosCountAttribute(): int
    {
        return $this->videos()->count();
    }

    /**
     * Obtenir la filmographie sous forme de chaîne
     */
    public function getFilmographyStringAttribute(): string
    {
        if (is_array($this->filmography)) {
            return implode(', ', $this->filmography);
        }
        return $this->filmography ?? '';
    }
} 