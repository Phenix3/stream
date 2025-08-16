<?php

namespace App\Services;

use Exception;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppOtpService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp-otp.base_url');
        $this->timeout = (int) config('services.whatsapp-otp.timeout');
    }

    /**
     * Normalise un numéro de téléphone au format international
     */
    public function normalizePhoneNumber(string $phoneNumber): string
    {
        // Supprime tous les espaces et caractères non numériques sauf le +
        $cleaned = preg_replace('/[^\d+]/', '', $phoneNumber);

        // Si le numéro commence par +, on le garde tel quel
        if (str_starts_with($cleaned, '+')) {
            return $cleaned;
        }

        // Si le numéro commence par 237, on ajoute le +
        if (str_starts_with($cleaned, '237')) {
            return '+' . $cleaned;
        }

        // Sinon, on ajoute le code pays par défaut
        $countryCode = config('whatsapp-otp.phone_number.country_code', '+237');
        return $countryCode . $cleaned;
    }

    /**
     * Valide le format d'un numéro de téléphone
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        $normalized = $this->normalizePhoneNumber($phoneNumber);
        $regex = config('whatsapp-otp.phone_number.format_regex', '/^(\+?237)?[0-9]{9}$/');

        return preg_match($regex, $normalized) === 1;
    }

    /**
     * Récupère le QR code pour la connexion WhatsApp
     */
    public function getQrCode(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                           ->get("{$this->baseUrl}/qr");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'qr_code' => $data['qr'] ?? null,
                    'message' => $data['message'] ?? 'QR code récupéré'
                ];
            }

            return [
                'success' => false,
                'error' => 'Impossible de récupérer le QR code',
                'status_code' => $response->status()
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp QR code retrieval failed', [
                'error' => $e->getMessage(),
                'url' => $this->baseUrl
            ]);

            return [
                'success' => false,
                'error' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifie le statut de connexion de WhatsApp
     */
    public function checkConnectionStatus(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                           ->get("{$this->baseUrl}/status");

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'connected' => false,
                'error' => 'Service unavailable',
                'status_code' => $response->status()
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp OTP Service connection check failed', [
                'error' => $e->getMessage(),
                'url' => $this->baseUrl
            ]);

            return [
                'connected' => false,
                'error' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifie si un numéro existe sur WhatsApp
     */
    public function checkPhoneNumberExists(string $phoneNumber): array
    {
        try {
            // Vérifier d'abord la connexion avant de faire l'appel
            $connectionStatus = $this->checkConnectionStatus();
            if (!($connectionStatus['connected'] ?? false)) {
                return [
                    'exists' => false,
                    'error' => 'WhatsApp service is not connected',
                    'phone_number' => $this->normalizePhoneNumber($phoneNumber)
                ];
            }

            $normalized = $this->normalizePhoneNumber($phoneNumber);

            // Utiliser un timeout plus court pour éviter les blocages
            $response = Http::timeout(10)
                           ->retry(2, 1000) // Réessayer 2 fois avec 1 seconde d'attente
                           ->post("{$this->baseUrl}/check-number", [
                               'phoneNumber' => $normalized
                           ]);

            if ($response->successful()) {
                $data = $response->json();

                // Vérifier la structure de la réponse
                if (isset($data['success']) && $data['success'] === false) {
                    return [
                        'exists' => false,
                        'error' => $data['error'] ?? 'Phone number check failed',
                        'phone_number' => $normalized
                    ];
                }

                return [
                    'exists' => $data['data']['exists'] ?? false,
                    'phone_number' => $normalized,
                    'jid' => $data['data']['jid'] ?? null
                ];
            }

            // Gestion des codes d'erreur HTTP spécifiques
            $errorMessage = match($response->status()) {
                404 => 'Phone number not found on WhatsApp',
                503 => 'WhatsApp service temporarily unavailable',
                500 => 'WhatsApp service internal error',
                default => 'Unable to verify phone number (HTTP ' . $response->status() . ')'
            };

            Log::warning('WhatsApp phone number check failed', [
                'phone_number' => $normalized,
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            return [
                'exists' => false,
                'error' => $errorMessage,
                'status_code' => $response->status(),
                'phone_number' => $normalized
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('WhatsApp service connection failed', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
                'base_url' => $this->baseUrl
            ]);

            return [
                'exists' => false,
                'error' => 'Cannot connect to WhatsApp service. Please check if the service is running.',
                'phone_number' => $this->normalizePhoneNumber($phoneNumber)
            ];

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('WhatsApp API request failed', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
                'response_code' => $e->response?->status()
            ]);

            return [
                'exists' => false,
                'error' => 'WhatsApp API request failed',
                'phone_number' => $this->normalizePhoneNumber($phoneNumber)
            ];

        } catch (Exception $e) {
            Log::error('Unexpected error during phone number check', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'exists' => false,
                'error' => 'Unexpected error occurred',
                'phone_number' => $this->normalizePhoneNumber($phoneNumber)
            ];
        }
    }

    /**
     * Envoie un code OTP via WhatsApp
     */
    public function sendOtp(string $phoneNumber, string $context = 'general', array $metadata = [], ?string $customMessage = null): array
    {
        try {
            // Validation du service
            if (!config('whatsapp-otp.enabled', true)) {
                return [
                    'success' => false,
                    'error' => 'WhatsApp OTP service is disabled'
                ];
            }

            // Normalisation du numéro
            $normalized = $this->normalizePhoneNumber($phoneNumber);

            if (!$this->validatePhoneNumber($normalized)) {
                return [
                    'success' => false,
                    'error' => 'Invalid phone number format'
                ];
            }

            // Vérification du cooldown
            if (!OtpVerification::canSendNewOtp($normalized, $context)) {
                $cooldown = OtpVerification::getResendCooldownSeconds($normalized, $context);
                return [
                    'success' => false,
                    'error' => 'Please wait before requesting a new OTP',
                    'cooldown_seconds' => $cooldown
                ];
            }

            // Vérification de l'existence du numéro sur WhatsApp
            $phoneCheck = $this->checkPhoneNumberExists($normalized);
            if (!$phoneCheck['exists']) {
                return [
                    'success' => false,
                    'error' => 'This phone number is not available on WhatsApp'
                ];
            }

            // Génération du code OTP
            $otpCode = OtpVerification::generateOtpCode();
            $expiryMinutes = (int) config('whatsapp-otp.otp.expiry_minutes', 10);
            $expiresAt = Carbon::now()->addMinutes($expiryMinutes);

            // Préparation du message
            $message = $customMessage ?? config('whatsapp-otp.messages.default_template');
            $message = str_replace(['{otp}', '{expiry}'], [$otpCode, $expiryMinutes], $message);

            // Envoi via l'API WhatsApp
            $response = Http::timeout($this->timeout)
                           ->post("{$this->baseUrl}/send-otp", [
                               'phoneNumber' => $normalized,
                               'message' => $message,
                               'otpLength' => (int) config('whatsapp-otp.otp.length', 6)
                           ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Invalidation des anciens OTP pour ce contexte
                OtpVerification::forPhone($normalized)
                              ->forContext($context)
                              ->active()
                              ->update(['is_used' => true]);

                // Sauvegarde du nouveau OTP
                $otpVerification = OtpVerification::create([
                    'phone_number' => $normalized,
                    'otp_code' => $otpCode,
                    'context' => $context,
                    'metadata' => $metadata,
                    'expires_at' => $expiresAt,
                    'whatsapp_message_id' => $responseData['data']['messageId'] ?? null,
                    'last_sent_at' => now()
                ]);

                Log::info('WhatsApp OTP sent successfully', [
                    'phone_number' => $normalized,
                    'context' => $context,
                    'otp_id' => $otpVerification->id
                ]);

                return [
                    'success' => true,
                    'message' => 'OTP sent successfully',
                    'data' => [
                        'phone_number' => $normalized,
                        'expires_in_minutes' => $expiryMinutes,
                        'can_resend_after_seconds' => (int) config('whatsapp-otp.otp.resend_cooldown_seconds', 60),
                        'otp_id' => $otpVerification->id
                    ]
                ];
            }

            Log::error('WhatsApp OTP API request failed', [
                'phone_number' => $normalized,
                'status_code' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to send OTP via WhatsApp'
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp OTP send operation failed', [
                'phone_number' => $phoneNumber,
                'context' => $context,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'OTP send failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifie un code OTP
     */
    public function verifyOtp(string $phoneNumber, string $otpCode, string $context = 'general'): array
    {
        try {
            $normalized = $this->normalizePhoneNumber($phoneNumber);

            // Recherche de l'OTP valide
            $otpVerification = OtpVerification::findValidOtp($normalized, $context);

            if (!$otpVerification) {
                return [
                    'success' => false,
                    'error' => 'No valid OTP found for this phone number'
                ];
            }

            // Vérification du nombre maximal de tentatives
            if ($otpVerification->hasMaxAttemptsReached()) {
                return [
                    'success' => false,
                    'error' => 'Maximum verification attempts reached. Please request a new OTP.'
                ];
            }

            // Vérification du code
            if ($otpVerification->otp_code !== $otpCode) {
                $otpVerification->incrementAttempts();

                $remainingAttempts = (int) config('whatsapp-otp.otp.max_attempts', 3) - $otpVerification->attempts;

                return [
                    'success' => false,
                    'error' => 'Invalid OTP code',
                    'remaining_attempts' => max(0, $remainingAttempts)
                ];
            }

            // Marquer comme vérifié
            $otpVerification->markAsVerified();

            Log::info('WhatsApp OTP verified successfully', [
                'phone_number' => $normalized,
                'context' => $context,
                'otp_id' => $otpVerification->id
            ]);

            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'data' => [
                    'phone_number' => $normalized,
                    'context' => $context,
                    'metadata' => $otpVerification->metadata,
                    'verified_at' => $otpVerification->updated_at
                ]
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp OTP verification failed', [
                'phone_number' => $phoneNumber,
                'context' => $context,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'OTP verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Renvoie un OTP (resend)
     */
    public function resendOtp(string $phoneNumber, string $context = 'general'): array
    {
        try {
            $normalized = $this->normalizePhoneNumber($phoneNumber);

            // Vérification du cooldown
            if (!OtpVerification::canSendNewOtp($normalized, $context)) {
                $cooldown = OtpVerification::getResendCooldownSeconds($normalized, $context);
                return [
                    'success' => false,
                    'error' => 'Please wait before requesting a new OTP',
                    'cooldown_seconds' => $cooldown
                ];
            }

            // Récupération des métadonnées du dernier OTP
            $lastOtp = OtpVerification::forPhone($normalized)
                                    ->forContext($context)
                                    ->orderBy('created_at', 'desc')
                                    ->first();

            $metadata = $lastOtp ? $lastOtp->metadata : [];
            $customMessage = config('whatsapp-otp.messages.resend_template');

            return $this->sendOtp($normalized, $context, $metadata, $customMessage);
        } catch (Exception $e) {
            Log::error('WhatsApp OTP resend failed', [
                'phone_number' => $phoneNumber,
                'context' => $context,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'OTP resend failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Nettoie les anciens OTP expirés
     */
    public function cleanupExpiredOtp(): int
    {
        try {
            $deleted = OtpVerification::cleanupExpired();

            Log::info('WhatsApp OTP cleanup completed', [
                'deleted_count' => $deleted
            ]);

            return $deleted;
        } catch (Exception $e) {
            Log::error('WhatsApp OTP cleanup failed', [
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }
}
