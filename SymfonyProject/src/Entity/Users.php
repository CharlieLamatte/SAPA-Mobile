<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('users')]
#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users
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

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $fonction = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $recovery_token = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $recovery_token_created_at = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_deactivated = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $is_deactivated_updated_at = null;

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

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getRecoveryToken(): ?string
    {
        return $this->recovery_token;
    }

    public function setRecoveryToken(?string $recovery_token): static
    {
        $this->recovery_token = $recovery_token;

        return $this;
    }

    public function getRecoveryTokenCreatedAt(): ?\DateTimeInterface
    {
        return $this->recovery_token_created_at;
    }

    public function setRecoveryTokenCreatedAt(?\DateTimeInterface $recovery_token_created_at): static
    {
        $this->recovery_token_created_at = $recovery_token_created_at;

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

    public function getIsDeactivatedUpdatedAt(): ?\DateTimeInterface
    {
        return $this->is_deactivated_updated_at;
    }

    public function setIsDeactivatedUpdatedAt(?\DateTimeInterface $is_deactivated_updated_at): static
    {
        $this->is_deactivated_updated_at = $is_deactivated_updated_at;

        return $this;
    }
}
