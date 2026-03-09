<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Application\DomainFactory;
use App\Infrastructure\Doctrine\TaskRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractWebController
{
    #[Route('/admin/tasks', name: 'web_admin_tasks', methods: ['GET'])]
    public function allTasks(TaskRepository $taskRepository, DomainFactory $domainFactory): Response
    {
        $doctrineTasks = $taskRepository->findAllOrderedByIdDesc();
        $tasks = [];
        foreach ($doctrineTasks as $task) {
            $tasks[] = $domainFactory->fromDoctrineTask($task);
        }

        return $this->render('web/admin_tasks.html.twig', [
            'tasks' => $tasks,
        ]);
    }
}
