<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'account_id',
        'email_verified_at',
        'phone_verified_at',
        'videos_watched',
        'total_watch_time',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'videos_watched' => 'integer',
            'total_watch_time' => 'integer',
        ];
    }

    /**
     * Relation avec les sessions utilisateur
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Relation avec les vidéos favorites
     */
    public function favoriteVideos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'favorites')->withTimestamps();
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
     * Relation avec les notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Relation avec les vérifications OTP
     */
    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }

    /**
     * Obtenir les statistiques de l'utilisateur
     */
    public function getStatisticsAttribute(): array
    {
        return [
            'videosWatched' => $this->videos_watched ?? 0,
            'favorites' => $this->favorites()->count(),
            'downloads' => $this->downloads()->count(),
            'totalWatchTime' => $this->total_watch_time ?? 0,
        ];
    }

    /**
     * Scope pour les utilisateurs vérifiés par email
     */
    public function scopeEmailVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope pour les utilisateurs vérifiés par téléphone
     */
    public function scopePhoneVerified($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }

    /**
     * Vérifier si l'utilisateur a vérifié son email
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Vérifier si l'utilisateur a vérifié son téléphone
     */
    public function hasVerifiedPhone(): bool
    {
        return !is_null($this->phone_verified_at);
    }
}
