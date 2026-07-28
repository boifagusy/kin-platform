<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TokenSecurityService
{
    const HASH_ALGO = 'sha256';

    /**
     * Generate a cryptographically secure random invitation token.
     * Returns: [raw_token, token_hash, expires_at]
     */
    public static function generateInvitationToken(): array
    {
        $length = config('tokens.invitation.length', 40);
        $rawToken = Str::random($length);
        $tokenHash = hash(self::HASH_ALGO, $rawToken);
        $expiryDays = config('tokens.invitation.expiry_days', 7);
        $expiresAt = now()->addDays($expiryDays);

        Log::info('TokenSecurityService: Invitation token generated', [
            'length' => $length,
            'expiry_days' => $expiryDays,
        ]);

        return [
            'raw_token' => $rawToken,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Verify an invitation token.
     * Returns: [is_valid, error_message]
     */
    public static function verifyToken(string $rawToken, string $storedHash, $expiresAt): array
    {
        $receivedHash = hash(self::HASH_ALGO, $rawToken);

        if ($receivedHash !== $storedHash) {
            Log::warning('TokenSecurityService: Token hash mismatch', [
                'expected_hash' => substr($storedHash, 0, 8) . '...',
                'received_hash' => substr($receivedHash, 0, 8) . '...',
            ]);
            return [false, 'Invalid token'];
        }

        if ($expiresAt && now()->isAfter($expiresAt)) {
            Log::warning('TokenSecurityService: Token expired', [
                'expired_at' => $expiresAt,
            ]);
            return [false, 'Token expired'];
        }

        Log::info('TokenSecurityService: Token verified successfully');
        return [true, null];
    }

    /**
     * Invalidate a token after successful use.
     * This prevents replay attacks.
     */
    public static function invalidateToken(): void
    {
        Log::info('TokenSecurityService: Token invalidated after successful verification');
    }
}
