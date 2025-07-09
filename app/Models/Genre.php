<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les vidéos (many-to-many)
     */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'video_genres')->withTimestamps();
    }

    /**
     * Scope pour rechercher par nom
     */
    public function scopeSearchByName($query, $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%");
    }

    /**
     * Scope pour rechercher par slug
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope pour les genres populaires (par nombre de vidéos)
     */
    public function scopePopular($query)
    {
        return $query->withCount('videos')->orderBy('videos_count', 'desc');
    }

    /**
     * Obtenir le nombre de vidéos dans ce genre
     */
    public function getVideosCountAttribute(): int
    {
        return $this->videos()->count();
    }

    /**
     * Obtenir le nombre de vidéos publiques dans ce genre
     */
    public function getPublicVideosCountAttribute(): int
    {
        return $this->videos()->public()->count();
    }

    /**
     * Générer automatiquement le slug à partir du nom
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = \Str::slug($value);
    }

    /**
     * Obtenir l'URL de l'icône avec fallback
     */
    public function getIconUrlAttribute()
    {
        return $this->icon ?? asset('images/default-genre-icon.svg');
    }

    /**
     * Obtenir le style CSS pour la couleur du genre
     */
    public function getColorStyleAttribute(): string
    {
        return $this->color ? "background-color: {$this->color};" : '';
    }
} 