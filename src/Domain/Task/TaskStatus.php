<?php

declare(strict_types=1);

namespace App\Domain\Task;

enum TaskStatus: string
{
    case ToDo = 'To Do';
    case InProgress = 'In Progress';
    case Done = 'Done';
}

