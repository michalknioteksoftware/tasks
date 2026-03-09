<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\User\User as DomainUser;

final class Task
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $name,
        private readonly string $description,
        private readonly TaskStatus $status,
        private readonly \DateTimeImmutable $createdAt,
        private readonly \DateTimeImmutable $updatedAt,
        private readonly ?DomainUser $assignedUser,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAssignedUser(): ?DomainUser
    {
        return $this->assignedUser;
    }
}

