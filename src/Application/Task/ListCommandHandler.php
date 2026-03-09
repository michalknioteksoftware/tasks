<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\DomainFactory;
use App\Domain\Task\Task as DomainTask;
use App\Domain\User\User as DomainUser;
use App\Infrastructure\Doctrine\Task as DoctrineTask;
use App\Infrastructure\Doctrine\TaskRepository;

final class ListCommandHandler
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly DomainFactory $factory,
    ) {
    }

    /**
     * @return DomainTask[]
     */
    public function handle(DomainUser $user): array
    {
        $userId = $user->getId();
        if ($userId === null) {
            return [];
        }

        /** @var DoctrineTask[] $results */
        $results = $this->taskRepository->findByAssignedUserId($userId);

        $tasks = [];
        foreach ($results as $task) {
            $tasks[] = $this->factory->fromDoctrineTask($task);
        }

        return $tasks;
    }
}

