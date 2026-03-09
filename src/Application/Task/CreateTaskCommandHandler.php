<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Application\DomainFactory;
use App\Application\Task\Event\TaskCreatedEvent;
use App\Domain\Task\Task as DomainTask;
use App\Domain\Task\TaskStatus;
use App\Domain\User\User as DomainUser;
use App\Infrastructure\Doctrine\Task as DoctrineTask;
use App\Infrastructure\Doctrine\TaskRepository;
use App\Infrastructure\Doctrine\UserRepository;
use Symfony\Component\Messenger\MessageBusInterface;

final class CreateTaskCommandHandler
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly UserRepository $userRepository,
        private readonly DomainFactory $factory,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function handle(string $name, string $description, TaskStatus $status, DomainUser $assignedUser): DomainTask
    {
        $userId = $assignedUser->getId();
        if ($userId === null) {
            throw new \InvalidArgumentException('Assigned user must have an id.');
        }

        $doctrineUser = $this->userRepository->find($userId);
        if ($doctrineUser === null) {
            throw new \InvalidArgumentException('Assigned user not found.');
        }

        $now = new \DateTimeImmutable();

        $task = new DoctrineTask();
        $task->setName($name);
        $task->setDescription($description);
        $task->setStatus($status);
        $task->setCreatedAt($now);
        $task->setUpdatedAt($now);
        $task->setAssignedUser($doctrineUser);

        $this->taskRepository->save($task, true);

        $taskId = $task->getId();
        if ($taskId !== null) {
            $this->messageBus->dispatch(new TaskCreatedEvent($taskId));
        }

        return $this->factory->fromDoctrineTask($task);
    }
}
