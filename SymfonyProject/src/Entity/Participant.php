<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_patient = null;

    #[ORM\Column(nullable: true)]
    private ?bool $presence = null;

    #[ORM\Column(nullable: true)]
    private ?bool $excuse = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column]
    private ?int $id_coordonnees = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom_patient = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $prenom_patient = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $mail_coordonnees = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $tel_portable_patient = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $tel_fixe_patient = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_admission = null;

    #[ORM\Column(nullable: true)]
    private ?bool $valider = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $prenom_medecin = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom_medecin = null;

    #[ORM\Column(length: 200)]
    private ?string $nom_antenne = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getIdCoordonnees(): ?int
    {
        return $this->id_coordonnees;
    }

    public function setIdCoordonnees(int $id_coordonnees): static
    {
        $this->id_coordonnees = $id_coordonnees;

        return $this;
    }

    public function getNomPatient(): ?string
    {
        return $this->nom_patient;
    }

    public function setNomPatient(?string $nom_patient): static
    {
        $this->nom_patient = $nom_patient;

        return $this;
    }

    public function getPrenomPatient(): ?string
    {
        return $this->prenom_patient;
    }

    public function setPrenomPatient(?string $prenom_patient): static
    {
        $this->prenom_patient = $prenom_patient;

        return $this;
    }

    public function getMailCoordonnees(): ?string
    {
        return $this->mail_coordonnees;
    }

    public function setMailCoordonnees(?string $mail_coordonnees): static
    {
        $this->mail_coordonnees = $mail_coordonnees;

        return $this;
    }

    public function getTelPortablePatient(): ?string
    {
        return $this->tel_portable_patient;
    }

    public function setTelPortablePatient(?string $tel_portable_patient): static
    {
        $this->tel_portable_patient = $tel_portable_patient;

        return $this;
    }

    public function getTelFixePatient(): ?string
    {
        return $this->tel_fixe_patient;
    }

    public function setTelFixePatient(?string $tel_fixe_patient): static
    {
        $this->tel_fixe_patient = $tel_fixe_patient;

        return $this;
    }

    public function getDateAdmission(): ?\DateTimeInterface
    {
        return $this->date_admission;
    }

    public function setDateAdmission(?\DateTimeInterface $date_admission): static
    {
        $this->date_admission = $date_admission;

        return $this;
    }

    public function isValider(): ?bool
    {
        return $this->valider;
    }

    public function setValider(?bool $valider): static
    {
        $this->valider = $valider;

        return $this;
    }

    public function getPrenomMedecin(): ?string
    {
        return $this->prenom_medecin;
    }

    public function setPrenomMedecin(?string $prenom_medecin): static
    {
        $this->prenom_medecin = $prenom_medecin;

        return $this;
    }

    public function getNomMedecin(): ?string
    {
        return $this->nom_medecin;
    }

    public function setNomMedecin(?string $nom_medecin): static
    {
        $this->nom_medecin = $nom_medecin;

        return $this;
    }

    public function getNomAntenne(): ?string
    {
        return $this->nom_antenne;
    }

    public function setNomAntenne(string $nom_antenne): static
    {
        $this->nom_antenne = $nom_antenne;

        return $this;
    }
}
