<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\UserDto;
use App\Entity\User;
use App\Enum\Role;
use App\Security\Voter\UserVoter;
use App\Service\TokenService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/api/users', name: 'api_users_')]
final class UserController extends AbstractController
{
    public function __construct(
        readonly private TokenService                $tokenService,
        readonly private EntityManagerInterface      $entityManager,
        readonly private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    #[Route('/{user}', name: 'get', methods: ['GET'])]
    #[IsGranted(UserVoter::VIEW, subject: 'user')]
    public function get(User $user): JsonResponse
    {
        return new JsonResponse(
            [
                'login' => $user->getLogin(),
                'phone' => $user->getPhone(),
                'password' => $user->getPassword(),
            ],
            Response::HTTP_OK);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] UserDto $userDto): JsonResponse
    {
        try {
            $id = Uuid::v7()->toRfc4122();
            $token = $this->tokenService->generateToken($id);

            $user = new User();
            $user->setId($id);
            $user->setLogin($userDto->login);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $userDto->password));
            $user->setPhone($userDto->phone);
            $user->setToken($token);
            $user->setRole(Role::USER->value);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new ConflictHttpException('Login already exists', $e);
        }

        return new JsonResponse(
            [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'phone' => $user->getPhone(),
                'password' => $user->getPassword(),
                'token' => $token,
            ],
            Response::HTTP_CREATED);
    }

    #[Route('/{user}', name: 'update', methods: ['PUT'])]
    #[IsGranted(UserVoter::EDIT, subject: 'user')]
    public function update(User $user, #[MapRequestPayload] UserDto $userDto): JsonResponse
    {
        try {
            $user->setLogin($userDto->login);
            $user->setPhone($userDto->phone);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $userDto->password));

            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new ConflictHttpException('Login already exists', $e);
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('Unable to update user', $e);
        }

        return $this->json(['id' => $user->getId()]);
    }

    #[Route('/{user}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(UserVoter::DELETE, subject: 'user')]
    public function delete(User $user): JsonResponse
    {
        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('Unable to delete user', $e);
        }

        return new JsonResponse('',Response::HTTP_NO_CONTENT);
    }
}
