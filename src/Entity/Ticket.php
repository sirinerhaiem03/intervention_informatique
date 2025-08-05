<?php

namespace App\Entity;
use App\Entity\Utilisateur;
use App\Entity\Commentaire;
use App\Enum\PrioriteEnum;
use App\Enum\StatutEnum;
use App\Repository\TicketRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]


    #[ORM\Column]
    private ?int $id_ticket = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(enumType: StatutEnum::class)]
    private ?StatutEnum $statut = null;

    #[ORM\Column(enumType: PrioriteEnum::class)]
    private ?PrioriteEnum $priorite = null;

    #[ORM\Column]
    private ?\DateTime $date_creation = null;


    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur', nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_technicien', referencedColumnName: 'id_utilisateur', nullable: true)]
    private ?Utilisateur $technicien = null;


    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: Commentaire::class)]
    private Collection $commentaires;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
    }

    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function getIdTicket(): ?int
    {
        return $this->id_ticket;
    }

    public function setIdTicket(int $id_ticket): static
    {
        $this->id_ticket = $id_ticket;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatut(): ?StatutEnum
    {
        return $this->statut;
    }

    public function setStatut(StatutEnum $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPriorite(): ?PrioriteEnum
    {
        return $this->priorite;
    }

    public function setPriorite(PrioriteEnum $priorite): static
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }
    public function getUtilisateur(): ?Utilisateur
{
    return $this->utilisateur;
}

public function setUtilisateur(?Utilisateur $utilisateur): static
{
    $this->utilisateur = $utilisateur;

    return $this;
}

public function getTechnicien(): ?Utilisateur
{
    return $this->technicien;
}

public function setTechnicien(?Utilisateur $technicien): static
{
    $this->technicien = $technicien;
    return $this;
}

}
