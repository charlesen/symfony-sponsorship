<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return array<array{user: User, referralCount: int, points: int}>
     */
    public function findTopReferrers(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u as user, COUNT(r.id) as referralCount, u.points as points')
            ->leftJoin('u.referrals', 'r')
            ->groupBy('u.id')
            ->orderBy('referralCount', 'DESC')
            ->addOrderBy('u.points', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function getUserRank(User $user): int
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "
            SELECT COUNT(*) + 1 as rank
            FROM (
                SELECT u.id, COUNT(r.id) as ref_count, u.points
                FROM `user` u
                LEFT JOIN `user` r ON r.referrer_id = u.id
                GROUP BY u.id
                HAVING (ref_count > :userRefCount) 
                    OR (ref_count = :userRefCount AND u.points > :userPoints)
            ) as higher_ranked
        ";

        $result = $conn->fetchOne($sql, [
            'userRefCount' => $user->getReferralsCount(),
            'userPoints' => $user->getPoints(),
        ]);

        return (int) ($result ?: 1);
    }
}
