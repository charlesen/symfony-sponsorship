<?php

namespace App\Repository;

use App\Entity\AssignmentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AssignmentType>
 */
class AssignmentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssignmentType::class);
    }
    
    /**
     * Trouve tous les types de mission triés par titre
     *
     * @return AssignmentType[]
     */
    public function findAllOrderedByTitle(): array
    {
        return $this->createQueryBuilder('at')
            ->orderBy('at.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return AssignmentType[] Returns an array of AssignmentType objects
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

    //    public function findOneBySomeField($value): ?AssignmentType
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
