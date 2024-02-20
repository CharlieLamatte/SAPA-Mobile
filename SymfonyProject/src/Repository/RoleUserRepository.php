<?php

namespace App\Repository;

use App\Entity\RoleUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoleUser>
 *
 * @method RoleUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoleUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoleUser[]    findAll()
 * @method RoleUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoleUser::class);
    }

//    /**
//     * @return RoleUser[] Returns an array of RoleUser objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RoleUser
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
