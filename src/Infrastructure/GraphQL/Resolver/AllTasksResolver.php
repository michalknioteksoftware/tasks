<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQL\Resolver;

use App\Application\DomainFactory;
use App\Domain\Task\Task as DomainTask;
use App\Infrastructure\Doctrine\TaskRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class AllTasksResolver
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly DomainFactory $domainFactory,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Only administrators can list all tasks.');
        }

        $doctrineTasks = $this->taskRepository->findAllOrderedByIdDesc();
        $tasks = [];
        foreach ($doctrineTasks as $task) {
            $domainTask = $this->domainFactory->fromDoctrineTask($task);
            $tasks[] = $this->taskToArray($domainTask);
        }

        return $tasks;
    }

    /**
     * @return array<string, mixed>
     */
    private function taskToArray(DomainTask $task): array
    {
        $user = $task->getAssignedUser();
        return [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus()->value,
            'createdAt' => $task->getCreatedAt()->format('c'),
            'updatedAt' => $task->getUpdatedAt()->format('c'),
            'assignedUser' => $user !== null ? [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ] : null,
        ];
    }
}
