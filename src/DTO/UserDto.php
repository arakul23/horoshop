<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    #[Assert\NotBlank(message: 'Login is required')]
    #[Assert\Length(
        min: 4,
        max: 8,
        minMessage: 'Login must be more {{ limit }} symbols',
        maxMessage: 'Login must be less {{ limit }} symbols'
    )]
    public string $login;

    #[Assert\NotBlank(message: 'Phone is required')]
    #[Assert\Length(
        min: 4,
        max: 8,
        minMessage: 'Phone must be more {{ limit }} symbols',
        maxMessage: 'Phone must be less {{ limit }} symbols'
    )]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        message: 'Phone must contain only digits'
    )]
    public string $phone;

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(
        min: 4,
        max: 8,
        minMessage: 'Password must be more {{ limit }} symbols',
        maxMessage: 'Password must be less {{ limit }} symbols'
    )]
    public string $password;
}
