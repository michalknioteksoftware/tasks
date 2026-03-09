<?php

declare(strict_types=1);

namespace App\Application\Task\Event;

final class TaskStatusUpdatedEvent
{
    public function __construct(
        private readonly int $taskId,
        private readonly string $previousStatus,
        private readonly string $newStatus,
    ) {
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getPreviousStatus(): string
    {
        return $this->previousStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }
}
