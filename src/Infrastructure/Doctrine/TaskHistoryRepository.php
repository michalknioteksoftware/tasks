<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskHistory>
 */
class TaskHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskHistory::class);
    }

    /**
     * @return TaskHistory[]
     */
    public function findByTaskIdOrderByIdDesc(int $taskId): array
    {
        return $this->createQueryBuilder('h')
            ->join('h.task', 't')
            ->andWhere('t.id = :taskId')
            ->setParameter('taskId', $taskId)
            ->orderBy('h.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
