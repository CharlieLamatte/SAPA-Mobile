<?php

namespace App\Entity;

use App\Repository\USERSRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToMany(targetEntity: ARole::class, mappedBy: 'id_user')]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, ARole>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(ARole $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addIdUser($this);
        }

        return $this;
    }

    public function removeRole(ARole $role): static
    {
        if ($this->roles->removeElement($role)) {
            $role->removeIdUser($this);
        }

        return $this;
    }
}
