<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'content_url',
        'creator_id',
        'views',
        'duration',
        'rating',
        'visibility',
        'season',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'views' => 'integer',
        'rating' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Les valeurs possibles pour le champ visibility
     */
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_UNLISTED = 'unlisted';

    /**
     * Relation avec le créateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    /**
     * Relation avec les genres (many-to-many)
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'video_genres')->withTimestamps();
    }

    /**
     * Relation avec les acteurs (many-to-many)
     */
    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'video_actors')->withTimestamps();
    }

    /**
     * Relation avec les utilisateurs qui ont mis en favoris
     */
    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Relation avec les favoris
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Relation avec l'historique de visionnage
     */
    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    /**
     * Relation avec les téléchargements
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    /**
     * Relation avec les activités
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Scope pour les vidéos publiques
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    /**
     * Scope pour les vidéos privées
     */
    public function scopePrivate($query)
    {
        return $query->where('visibility', self::VISIBILITY_PRIVATE);
    }

    /**
     * Scope pour les vidéos non listées
     */
    public function scopeUnlisted($query)
    {
        return $query->where('visibility', self::VISIBILITY_UNLISTED);
    }

    /**
     * Scope pour rechercher par titre
     */
    public function scopeSearchByTitle($query, $term)
    {
        return $query->where('title', 'LIKE', "%{$term}%");
    }

    /**
     * Scope pour filtrer par genre
     */
    public function scopeByGenre($query, $genreId)
    {
        return $query->whereHas('genres', function ($q) use ($genreId) {
            $q->where('genres.id', $genreId);
        });
    }

    /**
     * Scope pour les vidéos populaires (par vues)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('views', 'desc');
    }

    /**
     * Scope pour les vidéos récentes
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope pour les vidéos tendances (par vues et date récente)
     */
    public function scopeTrending($query)
    {
        return $query->where('created_at', '>=', now()->subDays(7))
                    ->orderBy('views', 'desc');
    }

    /**
     * Incrémenter le nombre de vues
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Obtenir l'URL de la miniature avec fallback
     */
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail ?? asset('images/default-video-thumbnail.jpg');
    }

    /**
     * Vérifier si la vidéo est publique
     */
    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    /**
     * Vérifier si la vidéo est privée
     */
    public function isPrivate(): bool
    {
        return $this->visibility === self::VISIBILITY_PRIVATE;
    }

    /**
     * Vérifier si la vidéo est non listée
     */
    public function isUnlisted(): bool
    {
        return $this->visibility === self::VISIBILITY_UNLISTED;
    }

    /**
     * Obtenir les noms des genres sous forme de tableau
     */
    public function getGenreNamesAttribute(): array
    {
        return $this->genres->pluck('name')->toArray();
    }
} 