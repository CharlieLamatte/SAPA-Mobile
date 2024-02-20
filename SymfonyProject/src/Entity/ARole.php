<?php

namespace App\Entity;

use App\Repository\ARoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ARoleRepository::class)]
class ARole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: RoleUser::class, inversedBy: 'id_role_user')]
    private Collection $id_role_user;

    #[ORM\ManyToMany(targetEntity: USERS::class, inversedBy: 'roles')]
    private Collection $id_user;

    public function __construct()
    {
        $this->id_role_user = new ArrayCollection();
        $this->id_user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, RoleUser>
     */
    public function getIdRoleUser(): Collection
    {
        return $this->id_role_user;
    }

    public function addIdRoleUser(RoleUser $idRoleUser): static
    {
        if (!$this->id_role_user->contains($idRoleUser)) {
            $this->id_role_user->add($idRoleUser);
        }

        return $this;
    }

    public function removeIdRoleUser(RoleUser $idRoleUser): static
    {
        $this->id_role_user->removeElement($idRoleUser);

        return $this;
    }

    /**
     * @return Collection<int, USERS>
     */
    public function getIdUser(): Collection
    {
        return $this->id_user;
    }

    public function addIdUser(USERS $idUser): static
    {
        if (!$this->id_user->contains($idUser)) {
            $this->id_user->add($idUser);
        }

        return $this;
    }

    public function removeIdUser(USERS $idUser): static
    {
        $this->id_user->removeElement($idUser);

        return $this;
    }
}
