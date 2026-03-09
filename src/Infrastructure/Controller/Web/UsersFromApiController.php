<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Application\User\ListCommandHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UsersFromApiController extends AbstractWebController
{
    #[Route('/users-from-api', name: 'web_users_from_api', methods: ['GET'])]
    public function list(ListCommandHandler $listCommandHandler): Response
    {
        $users = $listCommandHandler->handle();

        return $this->render('web/users_from_api.html.twig', [
            'users' => $users,
        ]);
    }
}
