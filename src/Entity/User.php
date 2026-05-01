<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_login', columns: ['login'])]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 8)]
    #[Assert\NotBlank(message: 'Login is required')]
    #[Assert\Length(
        max: 8,
        maxMessage: 'Login must be less {{ limit }} symbols'
    )]
    private string $login;

    #[ORM\Column(type: Types::STRING, length: 8)]
    #[Assert\NotBlank(message: 'Phone is required')]
    #[Assert\Length(
        max: 8,
        maxMessage: 'Phone must be less {{ limit }} symbols'
    )]
    private string $phone;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(
        max: 8,
        maxMessage: 'Password must be less {{ limit }} symbols'
    )]
    private string $password;

    #[ORM\Column(type: Types::INTEGER)]
    private int $role;

    public function getRole(): int
    {
        return $this->role;
    }

    public function setRole(int $role): void
    {
        $this->role = $role;
    }

    #[ORM\Column(type: Types::TEXT)]
    private ?string $token = null;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $pass): static
    {
        $this->password = $pass;

        return $this;
    }
}
