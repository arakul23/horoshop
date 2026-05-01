<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{

    public function __construct(readonly private UserRepository $userRepository)
    {}

    /**
     * @inheritDoc
     */
    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        if (!$user = $this->userRepository->findOneBy(['token' => $accessToken])) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        return new UserBadge((string) $user->getId());
    }
}
