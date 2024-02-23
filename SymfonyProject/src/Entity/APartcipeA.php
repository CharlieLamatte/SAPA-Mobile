<?php

namespace App\Entity;

use App\Repository\APartcipeARepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: APartcipeARepository::class)]
class APartcipeA
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_seance = null;

    #[ORM\Column]
    private ?int $id_patient = null;

    #[ORM\Column(nullable: true)]
    private ?bool $presence = null;

    #[ORM\Column(nullable: true)]
    private ?bool $excuse = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $commentaire = null;

    public function getIdSeance(): ?int
    {
        return $this->id_seance;
    }

    public function setIdSeance(int $id_seance): static
    {
        $this->id_seance = $id_seance;

        return $this;
    }

    public function getIdPatient(): ?int
    {
        return $this->id_patient;
    }

    public function setIdPatient(int $id_patient): static
    {
        $this->id_patient = $id_patient;

        return $this;
    }

    public function isPresence(): ?bool
    {
        return $this->presence;
    }

    public function setPresence(?bool $presence): static
    {
        $this->presence = $presence;

        return $this;
    }

    public function isExcuse(): ?bool
    {
        return $this->excuse;
    }

    public function setExcuse(?bool $excuse): static
    {
        $this->excuse = $excuse;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }
}
