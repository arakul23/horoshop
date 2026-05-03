<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\User;
use App\Enum\Role;
use App\Security\Voter\UserVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class UserVoterTest extends TestCase
{
    #[DataProvider('rootAllowedAttributesProvider')]
    public function testRootCanManageAnyUser(string $attribute): void
    {
        $voter = new UserVoter();
        $authenticatedUser = $this->createUser('root-id', Role::ROOT);
        $targetUser = $this->createUser('other-id', Role::USER);
        $token = new UsernamePasswordToken($authenticatedUser, 'main', $authenticatedUser->getRoles());

        $result = $voter->vote($token, $targetUser, [$attribute]);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    #[DataProvider('userOwnedAttributesProvider')]
    public function testUserCanAccessOwnDataForViewAndEdit(string $attribute): void
    {
        $voter = new UserVoter();
        $authenticatedUser = $this->createUser('user-id', Role::USER);
        $token = new UsernamePasswordToken($authenticatedUser, 'main', $authenticatedUser->getRoles());

        $result = $voter->vote($token, $authenticatedUser, [$attribute]);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    #[DataProvider('userDeniedAttributesProvider')]
    public function testUserIsDeniedForForeignUserAndDelete(string $attribute, string $targetId): void
    {
        $voter = new UserVoter();
        $authenticatedUser = $this->createUser('user-id', Role::USER);
        $targetUser = $this->createUser($targetId, Role::USER);
        $token = new UsernamePasswordToken($authenticatedUser, 'main', $authenticatedUser->getRoles());

        $result = $voter->vote($token, $targetUser, [$attribute]);

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public static function rootAllowedAttributesProvider(): array
    {
        return [
            [UserVoter::VIEW],
            [UserVoter::EDIT],
            [UserVoter::DELETE],
        ];
    }

    public static function userOwnedAttributesProvider(): array
    {
        return [
            [UserVoter::VIEW],
            [UserVoter::EDIT],
        ];
    }

    public static function userDeniedAttributesProvider(): array
    {
        return [
            [UserVoter::VIEW, 'another-user-id'],
            [UserVoter::EDIT, 'another-user-id'],
            [UserVoter::DELETE, 'user-id'],
        ];
    }

    private function createUser(string $id, Role $role): User
    {
        $user = new User();
        $user->setId($id);
        $user->setRole($role->value);

        return $user;
    }
}
