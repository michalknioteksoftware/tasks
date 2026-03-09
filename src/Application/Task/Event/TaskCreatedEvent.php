<?php

declare(strict_types=1);

namespace App\Application\Task\Event;

final class TaskCreatedEvent
{
    public function __construct(
        private readonly int $taskId,
    ) {
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }
}
