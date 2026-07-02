<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait SanitizesInput
{
    protected function sanitizeString(string $input): string
    {
        // Remove potential XSS vectors
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($input);
    }

    protected function sanitizePhone(string $phone): string
    {
        // Remove any non-numeric characters except +
        return preg_replace('/[^0-9+]/', '', $phone);
    }

    protected function sanitizeEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    protected function sanitizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        return '0.0.0.0';
    }

    protected function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
