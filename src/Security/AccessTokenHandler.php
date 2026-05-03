<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Service\TokenService;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

readonly class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private TokenService   $tokenService
    ) {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        try {
            $this->tokenService->parseToken($accessToken);
        } catch (\RuntimeException $e) {
            throw new BadCredentialsException($e->getMessage(), 401, $e);
        }

        if (!$user = $this->userRepository->findOneBy(['token' => $accessToken])) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        return new UserBadge((string) $user->getId());
    }
}
