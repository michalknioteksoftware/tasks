<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\DomainFactory;
use App\Domain\Task\Task as DomainTask;
use App\Domain\User\User as DomainUser;
use App\Infrastructure\Doctrine\Task as DoctrineTask;
use App\Infrastructure\Doctrine\TaskHistoryRepository;
use App\Infrastructure\Doctrine\TaskRepository;

final class ListHistoryCommandHandler
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly TaskHistoryRepository $taskHistoryRepository,
        private readonly DomainFactory $factory,
    ) {
    }

    /**
     * @return DomainTask[]
     */
    public function handle(DomainTask $task, DomainUser $user): array
    {
        $taskId = $task->getId();
        $userId = $user->getId();

        if ($taskId === null || $userId === null) {
            return [];
        }

        /** @var DoctrineTask|null $doctrineTask */
        $doctrineTask = $this->taskRepository->find($taskId);
        if ($doctrineTask === null) {
            return [];
        }

        $assignedUser = $doctrineTask->getAssignedUser();
        if ($assignedUser === null || $assignedUser->getId() !== $userId) {
            return [];
        }

        $historyRecords = $this->taskHistoryRepository->findByTaskIdOrderByIdDesc($taskId);
        $result = [];

        foreach ($historyRecords as $history) {
            $result[] = $this->factory->fromDoctrineTaskHistory($history);
        }

        return $result;
    }
}
