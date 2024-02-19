<?php

namespace App\Repository;

use App\Entity\USERS;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<USERS>
 *
 * @method USERS|null find($id, $lockMode = null, $lockVersion = null)
 * @method USERS|null findOneBy(array $criteria, array $orderBy = null) findOneBy(['identifiant' => 'monIdentifiant'])
 * @method USERS[]    findAll() ==> tout récuperer
 * @method USERS[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) ==> (['identifiant' => 'monIdentifiant'])
 */
class USERSRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, USERS::class);
    }


    public function caca()
    {
        return [];
    }

//    /**
//     * @return USERS[] Returns an array of USERS objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;

// $conn = $this->getEntityManager()->getConnection();

// $sql = '
//     SELECT * FROM product p
//     WHERE p.price > :price
//     ORDER BY p.price ASC
//     ';

// $resultSet = $conn->executeQuery($sql, ['price' => $price]);
//    }

//    public function findOneBySomeField($value): ?USERS
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
