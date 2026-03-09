<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Task[]
     */
    public function findByAssignedUserId(int $userId): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.assignedUser', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.id', 'DESC');

        /** @var Task[] $results */
        $results = $qb->getQuery()->getResult();

        return $results;
    }
}

