<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Application\DoctrineDomainFactory;
use App\Application\Task\ListCommandHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractWebController
{
    #[Route('/tasks', name: 'web_tasks', methods: ['GET'])]
    public function index(Request $request, ListCommandHandler $handler, DoctrineDomainFactory $factory): Response
    {
        $user = $this->getDomainUser($factory);

        if (null === $user) {
            return new RedirectResponse($this->generateUrl('web_home'));
        }

        $tasks = $handler->handle($user);

        return $this->render('web/tasks.html.twig', [
            'user' => $user,
            'tasks' => $tasks,
        ]);
    }
}

