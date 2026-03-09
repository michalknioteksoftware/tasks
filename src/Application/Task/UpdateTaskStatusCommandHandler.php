<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\DomainFactory;
use App\Application\Task\Event\TaskStatusUpdatedEvent;
use App\Domain\Task\Task as DomainTask;
use App\Domain\Task\TaskStatus;
use App\Domain\User\User as DomainUser;
use App\Infrastructure\Doctrine\Task as DoctrineTask;
use App\Infrastructure\Doctrine\TaskRepository;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpdateTaskStatusCommandHandler
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly DomainFactory $factory,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function handle(int $taskId, TaskStatus $newStatus, DomainUser $currentUser): DomainTask
    {
        $userId = $currentUser->getId();
        if ($userId === null) {
            throw new \InvalidArgumentException('Current user must have an id.');
        }

        /** @var DoctrineTask|null $task */
        $task = $this->taskRepository->find($taskId);
        if ($task === null) {
            throw new \InvalidArgumentException('Task not found.');
        }

        $assignedUser = $task->getAssignedUser();
        if ($assignedUser === null || $assignedUser->getId() !== $userId) {
            throw new \InvalidArgumentException('You are not allowed to update this task.');
        }

        $previousStatus = $task->getStatus();

        if ($previousStatus === $newStatus) {
            throw new \InvalidArgumentException('New status must be different from the current status.');
        }

        $now = new \DateTimeImmutable();
        $task->setStatus($newStatus);
        $task->setUpdatedAt($now);

        $this->taskRepository->save($task, true);

        $taskId = $task->getId();
        if ($taskId !== null) {
            $this->messageBus->dispatch(new TaskStatusUpdatedEvent(
                $taskId,
                $previousStatus->value,
                $newStatus->value,
            ));
        }

        return $this->factory->fromDoctrineTask($task, $assignedUser);
    }
}

