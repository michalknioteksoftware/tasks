<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Application\Task\ListCommandHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'web_tasks', methods: ['GET'])]
    public function index(Request $request, ListCommandHandler $handler): Response
    {
        $session = $request->getSession();
        $user = $this->getDomainUserFromSession($session);

        if (null === $user) {
            return new RedirectResponse($this->generateUrl('web_home'));
        }

        $tasks = $handler->handle($user);

        return $this->render('web/tasks.html.twig', [
            'user' => $user,
            'tasks' => $tasks,
        ]);
    }

    private function getDomainUserFromSession($session): ?DomainUser
    {
        $storedUser = $session->get('user');
        if (!is_string($storedUser) || $storedUser === '') {
            return null;
        }

        $unserialized = unserialize($storedUser, [
            'allowed_classes' => true,
        ]);

        return $unserialized instanceof DomainUser ? $unserialized : null;
    }
}

