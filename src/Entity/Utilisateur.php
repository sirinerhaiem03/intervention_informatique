<?php

namespace App\Entity;

use App\Enum\RoleEnum;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]


    #[ORM\Column]
    private ?int $id_utilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(enumType: RoleEnum::class)]
    private ?RoleEnum $role = null;

    // --- Getters & Setters ---



    public function getIdUtilisateur(): ?int
    {
        return $this->id_utilisateur;
    }

    public function setIdUtilisateur(int $id_utilisateur): static
    {
        $this->id_utilisateur = $id_utilisateur;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?RoleEnum
    {
        return $this->role;
    }

    public function setRole(RoleEnum $role): static
    {
        $this->role = $role;
        return $this;
    }

    // --- Méthodes obligatoires UserInterface & PasswordAuthenticatedUserInterface ---

    public function getUserIdentifier(): string
    {
        // La méthode utilisée par Symfony pour identifier l'utilisateur (typiquement l'email)
        return (string) $this->email;
    }

    /**
     * Retourne un tableau de rôles pour Symfony.
     * On convertit ton RoleEnum en string et on ajoute 'ROLE_USER' par défaut.
     */
    public function getRoles(): array
    {
        $roles = [];

        if ($this->role !== null) {
            $roles[] = 'ROLE_' . strtoupper($this->role->value);
        }

        // Toujours ajouter ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Pas nécessaire ici mais à implémenter
     */
    public function eraseCredentials(): void
    {
        // Ici tu peux effacer des données sensibles si tu en stockes temporairement
    }
    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Ticket::class)]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getTickets(): Collection
    {
        return $this->tickets;
    }

}
