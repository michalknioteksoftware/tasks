<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Application\DoctrineDomainFactory;
use App\Domain\User\User as DomainUser;
use App\Infrastructure\Doctrine\User as DoctrineUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractWebController extends AbstractController
{
    protected function getDomainUser(DoctrineDomainFactory $factory): ?DomainUser
    {
        $user = $this->getUser();
        if (!$user instanceof DoctrineUser) {
            return null;
        }

        return $factory->fromDoctrineUser($user);
    }
}

