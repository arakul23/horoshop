<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\UserDto;
use App\Entity\User;
use App\Enum\Role;
use App\Service\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/users', name: 'api_users_')]
final class UserController extends AbstractController
{
    private const string ADMIN = 'admin';

    public function __construct(
        readonly private TokenService                $tokenService,
        readonly private EntityManagerInterface      $entityManager,
        readonly private UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] UserDto $userDto): JsonResponse
    {
        try {
            $id = Uuid::v7()->toRfc4122();
            $token = $this->tokenService->generateToken($id);
            $role = $userDto->login == self::ADMIN ? Role::ROOT->value : Role::USER->value;

            $user = new User();
            $user->setId($id);
            $user->setLogin($userDto->login);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $userDto->password));
            $user->setPhone($userDto->phone);
            $user->setToken($token);
            $user->setRole($role);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ]);
        }

        return new JsonResponse(['token' => $token], Response::HTTP_CREATED);
    }

    #[Route('/get/{user}', name: 'get', methods: ['GET'])]
    public function get(User $user): JsonResponse
    {
        return new JsonResponse(
            [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'phone' => $user->getPhone(),
                'role' => $user->getRole(),
            ],
            Response::HTTP_OK);
    }

    #[Route('/update/{user}', name: 'update', methods: ['PUT'])]
    public function update(User $user): JsonResponse
    {
        return $this->json([
            'login' => $user->getLogin(),
            'password' => $user->getLogin(),
        ]);
    }

    #[Route('/delete/{user}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'User deleted'], Response::HTTP_NO_CONTENT);
    }


    /*    #[Route('/user', name: 'app_user')]
        public function index(): JsonResponse
        {
            return $this->json([
                'message' => 'Welcome to your new controller!',
                'path' => 'src/Controller/UserController.php',
            ]);
        }*/
}
