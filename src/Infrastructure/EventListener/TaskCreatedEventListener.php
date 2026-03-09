<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Application\Task\Event\TaskCreatedEvent;
use App\Infrastructure\Doctrine\TaskHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: TaskCreatedEvent::class)]
final class TaskCreatedEventListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(TaskCreatedEvent $event): void
    {
        $task = $event->getTask();

        $history = new TaskHistory();
        $history->setTask($task);
        $history->setName($task->getName());
        $history->setDescription($task->getDescription());
        $history->setStatus($task->getStatus());
        $history->setCreatedAt($task->getCreatedAt());
        $history->setAssignedUser($task->getAssignedUser());

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
