<?php

declare(strict_types=1);

namespace App\Enum;

enum Role: int
{
    case ROOT = 1;

    case USER = 2;
}
