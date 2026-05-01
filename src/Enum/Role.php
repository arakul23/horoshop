<?php

declare(strict_types=1);

namespace App\Enum;

enum Role: int
{
    case ROOT = 1;

    case USER = 2;

    public function toSecurityRole(): string
    {
        return match ($this) {
            self::USER => 'ROLE_USER',
            self::ROOT => 'ROLE_SUPER_ADMIN'
        };
    }
}
