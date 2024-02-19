<?php

namespace App\Entity;

use App\Repository\USERSRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('users')]
#[ORM\Entity(repositoryClass: USERSRepository::class)]
class USERS
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_user = null;

    #[ORM\Column(length: 100)]
    private ?string $identifiant = null;

    #[ORM\Column(length: 150)]
    private ?string $pswd = null;

    #[ORM\Column]
    private ?bool $est_coordinateur_peps = null;

    #[ORM\Column]
    private ?int $compteur = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_deactivated = null;

    public function getId(): ?int
    {
        return $this->id_user;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): static
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    public function getPswd(): ?string
    {
        return $this->pswd;
    }

    public function setPswd(string $pswd): static
    {
        $this->pswd = $pswd;

        return $this;
    }

    public function isEstCoordinateurPeps(): ?bool
    {
        return $this->est_coordinateur_peps;
    }

    public function setEstCoordinateurPeps(bool $est_coordinateur_peps): static
    {
        $this->est_coordinateur_peps = $est_coordinateur_peps;

        return $this;
    }

    public function getCompteur(): ?int
    {
        return $this->compteur;
    }

    public function setCompteur(int $compteur): static
    {
        $this->compteur = $compteur;

        return $this;
    }

    public function isIsDeactivated(): ?bool
    {
        return $this->is_deactivated;
    }

    public function setIsDeactivated(?bool $is_deactivated): static
    {
        $this->is_deactivated = $is_deactivated;

        return $this;
    }
}
