<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Application\Task\Event\TaskStatusUpdatedEvent;
use App\Domain\Task\TaskStatus;
use App\Infrastructure\Doctrine\TaskHistory;
use App\Infrastructure\Doctrine\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class TaskStatusUpdatedEventHandler
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(TaskStatusUpdatedEvent $event): void
    {
        $task = $this->taskRepository->find($event->getTaskId());
        if ($task === null) {
            return;
        }

        $newStatus = TaskStatus::from($event->getNewStatus());

        $history = new TaskHistory();
        $history->setTask($task);
        $history->setName($task->getName());
        $history->setDescription($task->getDescription());
        $history->setStatus($newStatus);
        $history->setCreatedAt(new \DateTimeImmutable());
        $history->setAssignedUser($task->getAssignedUser());

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
