<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpVerification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'phone',
        'otp_code',
        'temp_user_id',
        'expires_at',
        'verified_at',
        'attempts',
        'max_attempts',
        'status',
        'method',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'otp_code',
    ];

    /**
     * Les statuts possibles pour une vérification OTP
     */
    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';
    const STATUS_EXPIRED = 'expired';
    const STATUS_FAILED = 'failed';
    const STATUS_BLOCKED = 'blocked';

    /**
     * Les méthodes de vérification possibles
     */
    const METHOD_WHATSAPP = 'whatsapp';
    const METHOD_SMS = 'sms';
    const METHOD_CALL = 'call';

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
     * Scope pour un numéro de téléphone spécifique
     */
    public function scopeForPhone($query, $phone)
    {
        return $query->where('phone', $phone);
    }

    /**
     * Scope pour un temp_user_id spécifique
     */
    public function scopeForTempUser($query, $tempUserId)
    {
        return $query->where('temp_user_id', $tempUserId);
    }

    /**
     * Scope pour les vérifications par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les vérifications en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope pour les vérifications expirées
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                    ->orWhere('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope pour les vérifications non expirées
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now())
                    ->where('status', '!=', self::STATUS_EXPIRED);
    }

    /**
     * Scope pour les vérifications récentes
     */
    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope pour filtrer par méthode
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Vérifier si l'OTP est expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now() || $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Vérifier si l'OTP est vérifié
     */
    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    /**
     * Vérifier si l'OTP est en attente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }

    /**
     * Vérifier si le maximum de tentatives est atteint
     */
    public function isMaxAttemptsReached(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    /**
     * Générer un nouveau code OTP
     */
    public static function generateOtpCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Créer une nouvelle vérification OTP
     */
    public static function createVerification(
        string $phone,
        ?int $userId = null,
        string $method = self::METHOD_WHATSAPP,
        int $expiresInMinutes = 5
    ): self {
        // Invalider les vérifications précédentes pour ce numéro
        self::forPhone($phone)->pending()->update(['status' => self::STATUS_EXPIRED]);

        return self::create([
            'user_id' => $userId,
            'phone' => $phone,
            'otp_code' => self::generateOtpCode(),
            'temp_user_id' => $userId ? null : 'temp_' . uniqid(),
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'attempts' => 0,
            'max_attempts' => 3,
            'status' => self::STATUS_PENDING,
            'method' => $method,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Vérifier un code OTP
     */
    public function verifyCode(string $inputCode): array
    {
        // Vérifier si déjà vérifié
        if ($this->isVerified()) {
            return [
                'success' => false,
                'message' => 'Code déjà vérifié',
                'error_code' => 'already_verified'
            ];
        }

        // Vérifier si expiré
        if ($this->isExpired()) {
            return [
                'success' => false,
                'message' => 'Code expiré',
                'error_code' => 'expired'
            ];
        }

        // Vérifier si le maximum de tentatives est atteint
        if ($this->isMaxAttemptsReached()) {
            $this->update(['status' => self::STATUS_BLOCKED]);
            return [
                'success' => false,
                'message' => 'Trop de tentatives. Demandez un nouveau code.',
                'error_code' => 'max_attempts_reached'
            ];
        }

        // Incrémenter le nombre de tentatives
        $this->increment('attempts');

        // Vérifier le code
        if ($inputCode === $this->otp_code) {
            $this->update([
                'status' => self::STATUS_VERIFIED,
                'verified_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Code vérifié avec succès',
                'data' => [
                    'user_id' => $this->user_id,
                    'temp_user_id' => $this->temp_user_id,
                    'phone' => $this->phone
                ]
            ];
        } else {
            $remainingAttempts = $this->max_attempts - $this->attempts;
            
            if ($remainingAttempts <= 0) {
                $this->update(['status' => self::STATUS_BLOCKED]);
                return [
                    'success' => false,
                    'message' => 'Code incorrect. Trop de tentatives.',
                    'error_code' => 'invalid_code_blocked'
                ];
            }

            return [
                'success' => false,
                'message' => "Code incorrect. {$remainingAttempts} tentative(s) restante(s)",
                'error_code' => 'invalid_code',
                'remaining_attempts' => $remainingAttempts
            ];
        }
    }

    /**
     * Obtenir le temps restant avant expiration (en secondes)
     */
    public function getTimeUntilExpirationAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return $this->expires_at->diffInSeconds(now());
    }

    /**
     * Obtenir le statut formaté pour l'affichage
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_VERIFIED => 'Vérifié',
            self::STATUS_EXPIRED => 'Expiré',
            self::STATUS_FAILED => 'Échec',
            self::STATUS_BLOCKED => 'Bloqué',
            default => 'Inconnu'
        };
    }

    /**
     * Obtenir la méthode formatée pour l'affichage
     */
    public function getFormattedMethodAttribute(): string
    {
        return match($this->method) {
            self::METHOD_WHATSAPP => 'WhatsApp',
            self::METHOD_SMS => 'SMS',
            self::METHOD_CALL => 'Appel téléphonique',
            default => 'Inconnu'
        };
    }

    /**
     * Nettoyer les vérifications expirées
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now()->subHours(1))->delete();
    }

    /**
     * Vérifier si un numéro peut demander un nouveau code
     */
    public static function canRequestNewCode(string $phone, int $cooldownMinutes = 1): bool
    {
        $lastVerification = self::forPhone($phone)
            ->where('created_at', '>=', now()->subMinutes($cooldownMinutes))
            ->latest()
            ->first();

        return !$lastVerification;
    }

    /**
     * Obtenir les statistiques de vérification pour un numéro
     */
    public static function getPhoneStats(string $phone, int $hours = 24): array
    {
        $verifications = self::forPhone($phone)
            ->where('created_at', '>=', now()->subHours($hours));

        return [
            'total_attempts' => $verifications->count(),
            'successful' => $verifications->byStatus(self::STATUS_VERIFIED)->count(),
            'failed' => $verifications->byStatus(self::STATUS_FAILED)->count(),
            'blocked' => $verifications->byStatus(self::STATUS_BLOCKED)->count(),
            'pending' => $verifications->pending()->count(),
        ];
    }
} 