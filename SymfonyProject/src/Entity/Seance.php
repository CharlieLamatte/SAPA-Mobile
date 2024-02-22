<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('seance')]
#[ORM\Entity(repositoryClass: SeanceRepository::class)]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $nom_creneau = null;

    #[ORM\Column(nullable: true)]
    private ?int $id_type_parcours = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nombre_participants = null;

    #[ORM\Column(length: 50)]
    private ?string $type_seance = null;

    #[ORM\Column(nullable: true)]
    private ?int $id_jour = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nom_structure = null;

    #[ORM\Column]
    private ?int $id_structure = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom_coordonnees = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $prenom_coordonnees = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom_adresse = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $complement_adresse = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $code_postal = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nom_ville = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $type_parcours = null;

    #[ORM\Column(length: 250)]
    private ?string $nom_jour = null;

    #[ORM\Column]
    private ?int $heure_debut = null;

    #[ORM\Column]
    private ?int $heure_fin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_seance = null;

    #[ORM\Column(nullable: true)]
    private ?int $id_creneau = null;

    #[ORM\Column(nullable: true)]
    private ?bool $validation_seance = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $commentaire_seance = null;

    #[ORM\Column(nullable: true)]
    private ?int $id_user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setIdSeance(?int $id_seance): static
    {
        $this->id = $id_seance;

        return $this;
    }

    public function getNomCreneau(): ?string
    {
        return $this->nom_creneau;
    }

    public function setNomCreneau(?string $nom_creneau): static
    {
        $this->nom_creneau = $nom_creneau;

        return $this;
    }

    public function getIdTypeParcours(): ?int
    {
        return $this->id_type_parcours;
    }

    public function setIdTypeParcours(?int $id_type_parcours): static
    {
        $this->id_type_parcours = $id_type_parcours;

        return $this;
    }

    public function getNombreParticipants(): ?string
    {
        return $this->nombre_participants;
    }

    public function setNombreParticipants(?string $nombre_participants): static
    {
        $this->nombre_participants = $nombre_participants;

        return $this;
    }

    public function getTypeSeance(): ?string
    {
        return $this->type_seance;
    }

    public function setTypeSeance(string $type_seance): static
    {
        $this->type_seance = $type_seance;

        return $this;
    }

    public function getIdJour(): ?int
    {
        return $this->id_jour;
    }

    public function setIdJour(?int $id_jour): static
    {
        $this->id_jour = $id_jour;

        return $this;
    }

    public function getNomStructure(): ?string
    {
        return $this->nom_structure;
    }

    public function setNomStructure(?string $nom_structure): static
    {
        $this->nom_structure = $nom_structure;

        return $this;
    }

    public function getIdStructure(): ?int
    {
        return $this->id_structure;
    }

    public function setIdStructure(int $id_structure): static
    {
        $this->id_structure = $id_structure;

        return $this;
    }

    public function getNomCoordonnees(): ?string
    {
        return $this->nom_coordonnees;
    }

    public function setNomCoordonnees(?string $nom_coordonnees): static
    {
        $this->nom_coordonnees = $nom_coordonnees;

        return $this;
    }

    public function getPrenomCoordonnees(): ?string
    {
        return $this->prenom_coordonnees;
    }

    public function setPrenomCoordonnees(?string $prenom_coordonnees): static
    {
        $this->prenom_coordonnees = $prenom_coordonnees;

        return $this;
    }

    public function getNomAdresse(): ?string
    {
        return $this->nom_adresse;
    }

    public function setNomAdresse(?string $nom_adresse): static
    {
        $this->nom_adresse = $nom_adresse;

        return $this;
    }

    public function getComplementAdresse(): ?string
    {
        return $this->complement_adresse;
    }

    public function setComplementAdresse(?string $complement_adresse): static
    {
        $this->complement_adresse = $complement_adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(?string $code_postal): static
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    public function getNomVille(): ?string
    {
        return $this->nom_ville;
    }

    public function setNomVille(?string $nom_ville): static
    {
        $this->nom_ville = $nom_ville;

        return $this;
    }

    public function getTypeParcours(): ?string
    {
        return $this->type_parcours;
    }

    public function setTypeParcours(?string $type_parcours): static
    {
        $this->type_parcours = $type_parcours;

        return $this;
    }

    public function getNomJour(): ?string
    {
        return $this->nom_jour;
    }

    public function setNomJour(string $nom_jour): static
    {
        $this->nom_jour = $nom_jour;

        return $this;
    }

    public function getHeureDebut(): ?int
    {
        return $this->heure_debut;
    }

    public function setHeureDebut(int $heure_debut): static
    {
        $this->heure_debut = $heure_debut;

        return $this;
    }

    public function getHeureFin(): ?int
    {
        return $this->heure_fin;
    }

    public function setHeureFin(int $heure_fin): static
    {
        $this->heure_fin = $heure_fin;

        return $this;
    }

    public function getDateSeance(): ?\DateTimeInterface
    {
        return $this->date_seance;
    }

    public function setDateSeance(?\DateTimeInterface $date_seance): static
    {
        $this->date_seance = $date_seance;

        return $this;
    }

    public function getIdCreneau(): ?int
    {
        return $this->id_creneau;
    }

    public function setIdCreneau(?int $id_creneau): static
    {
        $this->id_creneau = $id_creneau;

        return $this;
    }

    public function isValidationSeance(): ?bool
    {
        return $this->validation_seance;
    }

    public function setValidationSeance(?bool $validation_seance): static
    {
        $this->validation_seance = $validation_seance;

        return $this;
    }

    public function getCommentaireSeance(): ?string
    {
        return $this->commentaire_seance;
    }

    public function setCommentaireSeance(?string $commentaire_seance): static
    {
        $this->commentaire_seance = $commentaire_seance;

        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(?int $id_user): static
    {
        $this->id_user = $id_user;

        return $this;
    }
}
