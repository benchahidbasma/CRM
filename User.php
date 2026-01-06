<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password_hash = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'boolean')]
    private bool $is_active = true;

    // --- Getters & Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function getPassword(): string
    {
        return $this->password_hash ?? '';
    }

    public function setPasswordHash(string $hash): self
    {
        $this->password_hash = $hash;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // garantit que chaque utilisateur a au moins ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $active): self
    {
        $this->is_active = $active;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Nettoyage des données sensibles temporaires si nécessaire
    }

    // --- Optionnel : méthode __toString pour afficher directement l'email ---
    public function __toString(): string
    {
        return $this->email ?? '';
    }
}
