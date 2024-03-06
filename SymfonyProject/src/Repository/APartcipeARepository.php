<?php

namespace App\Repository;

use App\Entity\APartcipeA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @extends ServiceEntityRepository<APartcipeA>
 *
 * @method APartcipeA|null find($id, $lockMode = null, $lockVersion = null)
 * @method APartcipeA|null findOneBy(array $criteria, array $orderBy = null)
 * @method APartcipeA[]    findAll()
 * @method APartcipeA[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class APartcipeARepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, APartcipeA::class);
    }

    /**
     * Supprime l'association entre une séance et un patient.
     *
     * @param int $id_seance L'identifiant de la séance.
     * @param int $id_patient L'identifiant du patient.
     * @return bool True si la suppression est réussie, sinon false.
     * @throws \Doctrine\DBAL\Exception Si une erreur survient lors de l'exécution de la requête.
     */
    public function deleteBySeanceAndPatientId(int $id_seance, int $id_patient): JsonResponse {
        try {
            $conn = $this->getEntityManager()->getConnection();

            $sql = 'DELETE FROM a_participe_a
                    WHERE id_seance = :id_seance
                    AND id_patient = :id_patient ';
            
            $conn->executeQuery($sql, ['id_seance' => $id_seance, 'id_patient' => $id_patient]);
            return new JsonResponse(['message' => 'Line successfully deleted']);
        }
        catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error while deleting the line: '.$e->getMessage()]);
        }
    }

    public function createEmargement(APartcipeA $newEmargement): JsonResponse {
        try {
            $conn = $this->getEntityManager()->getConnection();
            $id_patient = $newEmargement->getIdPatient();
            $id_seance = $newEmargement->getIdSeance();
            $presence = $newEmargement->isPresence();
            $excuse = $newEmargement->isExcuse() ?? null;
            $commentaire = $newEmargement->getCommentaire() ?? null;

            $sql = 'INSERT INTO a_participe_a (id_patient, id_seance, presence, excuse, commentaire)
                    VALUES(:id_patient, :id_seance, :presence, :excuse, :commentaire)';
            $conn->executeQuery($sql, ['id_seance' => $id_seance, 'id_patient' => $id_patient, 'presence' => $presence, 'excuse'=> $excuse, 'commentaire' => $commentaire]);
            return new JsonResponse(['message' => 'Patient successfully added']);
        }
        catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error while inserting the line: '.$e->getMessage()]);
        }
    }

    // public function 

//    /**
//     * @return APartcipeA[] Returns an array of APartcipeA objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?APartcipeA
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
