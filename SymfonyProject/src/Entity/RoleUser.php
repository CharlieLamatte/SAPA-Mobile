<?php

namespace App\Entity;

use App\Repository\RoleUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleUserRepository::class)]
class RoleUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $role_user = null;

    #[ORM\ManyToMany(targetEntity: ARole::class, mappedBy: 'id_role_user')]
    private Collection $id_role_user;

    public function __construct()
    {
        $this->id_role_user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleUser(): ?string
    {
        return $this->role_user;
    }

    public function setRoleUser(string $role_user): static
    {
        $this->role_user = $role_user;

        return $this;
    }

    /**
     * @return Collection<int, ARole>
     */
    public function getIdRoleUser(): Collection
    {
        return $this->id_role_user;
    }

    public function addIdRoleUser(ARole $idRoleUser): static
    {
        if (!$this->id_role_user->contains($idRoleUser)) {
            $this->id_role_user->add($idRoleUser);
            $idRoleUser->addIdRoleUser($this);
        }

        return $this;
    }

    public function removeIdRoleUser(ARole $idRoleUser): static
    {
        if ($this->id_role_user->removeElement($idRoleUser)) {
            $idRoleUser->removeIdRoleUser($this);
        }

        return $this;
    }
}
