<?php

declare(strict_types=1);

namespace App\Service;

class TokenService
{
    const int TOKEN_EXPIRATION_TIME = 3600;

    public function __construct(readonly private string $tokenSecret)
    {
    }

    public function generateToken(string $id): string {
        $payload = [
            'sub' => $id,
            'exp' => time() + self::TOKEN_EXPIRATION_TIME,
        ];
        $jsonPayload = base64_encode(json_encode($payload));

        $signature = hash_hmac('sha256', $jsonPayload, $this->tokenSecret);


       return $jsonPayload . '.' . $signature;
    }

    public function parseToken(string $token): array
    {
        $parts = explode('.', $token, 2);

        if (count($parts) !== 2) {
            throw new \RuntimeException('Invalid token format');
        }

        [$jsonPayload, $signature] = $parts;

        $expectedSignature = hash_hmac('sha256', $jsonPayload, $this->tokenSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('Invalid token signature');
        }

        $payload = json_decode(base64_decode($jsonPayload, true), true);

        if (!is_array($payload) || !isset($payload['sub'], $payload['exp'])) {
            throw new \RuntimeException('Invalid token payload');
        }

        if ($payload['exp'] < time()) {
            throw new \RuntimeException('Token expired');
        }

        return $payload;
    }
}
