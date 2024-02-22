<?php

namespace App\Entity;

use App\Repository\ARoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('a_role')]
#[ORM\Entity(repositoryClass: ARoleRepository::class)]
class ARole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_user = null;

    #[ORM\Column]
    private ?int $id_role_user = null;

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function getIdRoleUser(): ?int
    {
        return $this->id_role_user;
    }

    public function setIdRoleUser(int $id_role_user): static
    {
        $this->id_role_user = $id_role_user;

        return $this;
    }
}
