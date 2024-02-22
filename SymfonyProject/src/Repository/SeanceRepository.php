<?php

namespace App\Repository;

use App\Entity\Seance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seance>
 *
 * @method Seance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Seance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Seance[]    findAll()
 * @method Seance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seance::class);
    }

    public function findByUserId(int $id_user): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT creneaux.nom_creneau           as nom_creneau,
                    structure.nom_structure        as nom_structure,
                    creneaux.id_type_parcours      as id_type_parcours,
                    creneaux.nombre_participants   as nb_participant,
                    coordonnees.nom_coordonnees    as nom_intervenant,
                    coordonnees.prenom_coordonnees as prenom_intervenant,
                    structure.id_structure         as id_structure,
                    adresse.nom_adresse            as adresse,
                    adresse.complement_adresse     as complement_adresse,
                    villes.code_postal             as code_postal,
                    villes.nom_ville               as nom_ville,
                    creneaux.type_seance           as type_seance,
                    type_parcours.type_parcours    as type_parcours,
                    creneaux.id_jour               as jour,
                    jours.nom_jour                 as nom_jour,
                    seance.heure_debut             as id_heure_debut,
                    seance.heure_fin               as id_heure_fin,
                    users.id_user                  as id_user,
                    seance.date_seance             as date_seance,
                    seance.id_seance               as id_seance,
                    seance.id_creneau              as id_creneau,
                    heuresDeb.heure                as heure_debut,
                    heuresFin.heure                as heure_fin,
                    seance.validation_seance       as valider,
                    seance.commentaire_seance      as commentaire_seance
                FROM seance
                    join creneaux on creneaux.id_creneau = seance.id_creneau
                    join structure on creneaux.id_structure = structure.id_structure
                    join users on seance.id_user = users.id_user
                    join coordonnees on users.id_coordonnees = coordonnees.id_coordonnees
                    join se_pratique_a on creneaux.id_creneau = se_pratique_a.id_creneau
                    join adresse on se_pratique_a.id_adresse = adresse.id_adresse
                    join se_localise_a on adresse.id_adresse = se_localise_a.id_adresse
                    join villes on se_localise_a.id_ville = villes.id_ville
                    join type_parcours on type_parcours.id_type_parcours = creneaux.id_type_parcours
                    join heures as heuresDeb on seance.heure_debut = heuresDeb.id_heure
                    join heures as heuresFin on seance.heure_fin = heuresFin.id_heure
                    join jours on creneaux.id_jour = jours.id_jour
                WHERE seance.annulation_seance = 0
                AND seance.id_user = :id_user';

        $resultSet = $conn->executeQuery($sql, ['id_user' => $id_user]);
        
        return $resultSet->fetchAllAssociative();
    }

    /**
     * @param int $id_user
     * @return Seance[]
     */
    public function findSeancesByUserId(int $id_user): array
    {
        $results = $this->findByUserId($id_user);
        $seances = [];

        foreach ($results as $result) {
            $seance = new Seance();
            $seance->setIdSeance($result['id_seance']);
            $seance->setNomCreneau($result['nom_creneau']);
            $seance->setIdTypeParcours($result['id_type_parcours']);
            $seance->setNombreParticipants($result['nb_participant']);
            $seance->setTypeSeance($result['type_seance']);
            $seance->setIdJour($result['jour']);
            $seance->setNomStructure($result['nom_structure']);
            $seance->setIdStructure($result['id_structure']);
            $seance->setNomCoordonnees($result['nom_intervenant']);
            $seance->setPrenomCoordonnees($result['prenom_intervenant']);
            $seance->setNomAdresse($result['adresse']);
            $seance->setComplementAdresse($result['complement_adresse']);
            $seance->setCodePostal($result['code_postal']);
            $seance->setNomVille($result['nom_ville']);
            $seance->setTypeParcours($result['type_parcours']);
            $seance->setNomJour($result['nom_jour']);
            $seance->setHeureDebut($result['id_heure_debut']);
            $seance->setHeureFin($result['id_heure_fin']);
            $seance->setDateSeance(new \DateTime($result['date_seance']));
            $seance->setIdCreneau($result['id_creneau']);
            $seance->setValidationSeance($result['valider']);
            $seance->setCommentaireSeance($result['commentaire_seance']);
            $seance->setIdUser($result['id_user']);

            $seances[] = $seance;
        }

        return $seances;
    }

//    /**
//     * @return Seance[] Returns an array of Seance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Seance
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
