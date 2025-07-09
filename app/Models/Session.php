<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'token',
        'refresh_token',
        'expires_at',
        'refresh_expires_at',
        'device_name',
        'device_type',
        'ip_address',
        'user_agent',
        'last_activity',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'token',
        'refresh_token',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les sessions actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les sessions expirées
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope pour les sessions non expirées
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope pour les refresh tokens expirés
     */
    public function scopeRefreshExpired($query)
    {
        return $query->where('refresh_expires_at', '<', now());
    }

    /**
     * Scope pour les refresh tokens non expirés
     */
    public function scopeRefreshNotExpired($query)
    {
        return $query->where('refresh_expires_at', '>', now());
    }

    /**
     * Scope pour filtrer par type de device
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Vérifier si la session est expirée
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Vérifier si le refresh token est expiré
     */
    public function isRefreshExpired(): bool
    {
        return $this->refresh_expires_at < now();
    }

    /**
     * Vérifier si la session est active et non expirée
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Vérifier si le refresh token est valide
     */
    public function isRefreshValid(): bool
    {
        return $this->is_active && !$this->isRefreshExpired();
    }

    /**
     * Marquer la session comme inactive
     */
    public function revoke(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Mettre à jour la dernière activité
     */
    public function updateLastActivity(): bool
    {
        return $this->update(['last_activity' => now()]);
    }

    /**
     * Obtenir une représentation formatée du type de device
     */
    public function getFormattedDeviceTypeAttribute(): string
    {
        return match($this->device_type) {
            'mobile' => 'Mobile',
            'tablet' => 'Tablette',
            'desktop' => 'Ordinateur',
            'tv' => 'Télévision',
            default => 'Inconnu'
        };
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
} 