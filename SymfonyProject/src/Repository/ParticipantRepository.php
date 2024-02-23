<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 *
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function requestWithSeanceId(int $id_seance): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT a_participe_a.id_patient,
                    a_participe_a.presence,
                    a_participe_a.excuse,
                    COALESCE(a_participe_a.commentaire, '')                                                     as commentaire,
                    coordonnees.id_coordonnees,
                    IF(nom_utilise IS NOT NULL AND nom_utilise != '', nom_utilise, nom_naissance)               as nom_patient,
                    IF(prenom_utilise IS NOT NULL AND prenom_utilise != '', prenom_utilise,
                    premier_prenom_naissance)                                                                as prenom_patient,
                    coordonnees.mail_coordonnees,
                    coordonnees.tel_portable_coordonnees                                                        as tel_portable_patient,
                    coordonnees.tel_fixe_coordonnees                                                            as tel_fixe_patient,
                    patients.date_admission,
                    s.validation_seance                                                                         as valider,
                    coordonnees.prenom_coordonnees                                                              as prenom_medecin,
                    coordonnees.nom_coordonnees                                                                 as nom_medecin,
                    a.nom_antenne
                FROM a_participe_a
                    JOIN patients using (id_patient)
                    JOIN antenne a on patients.id_antenne = a.id_antenne
                    JOIN seance s on a_participe_a.id_seance = s.id_seance
                    JOIN coordonnees ON patients.id_coordonnee = coordonnees.id_coordonnees
                    LEFT JOIN suit s2 on patients.id_patient = s2.id_patient
                    LEFT JOIN coordonnees c2 ON s2.id_medecin = c2.id_medecin
                WHERE a_participe_a.id_seance = :id_seance";

        $resultSet = $conn->executeQuery($sql, ['id_seance' => $id_seance]);

        return $resultSet->fetchAllAssociative();
    }

    /**
     * @param int $id_seance
     * @return Participant[]
     */
    public function findBySeanceId(int $id_seance): array
    {
        $results = $this->requestWithSeanceId($id_seance);
        $participants = [];

        foreach ($results as $result) {
            $participant = new Participant();
            $participant->setIdSeance($id_seance)
                        ->setIdPatient($result['id_patient'])
                        ->setPresence($result['presence'])
                        ->setExcuse($result['excuse'])
                        ->setCommentaire($result['commentaire'])
                        ->setIdCoordonnees($result['id_coordonnees'])
                        ->setNomPatient($result['nom_patient'])
                        ->setPrenomPatient($result['prenom_patient'])
                        ->setMailCoordonnees($result['mail_coordonnees'])
                        ->setTelPortablePatient($result['tel_portable_patient'])
                        ->setTelFixePatient($result['tel_fixe_patient'])
                        ->setDateAdmission(new \DateTime($result['date_admission']))
                        ->setValider($result['valider'])
                        ->setPrenomMedecin($result['prenom_medecin'])
                        ->setNomMedecin($result['nom_medecin'])
                        ->setNomAntenne($result['nom_antenne']);

        $participants[] = $participant;
    }

    return $participants;
}

//    /**
//     * @return Participant[] Returns an array of Participant objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Participant
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
