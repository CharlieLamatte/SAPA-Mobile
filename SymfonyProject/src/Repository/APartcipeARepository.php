<?php

namespace App\Repository;

use App\Entity\APartcipeA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
