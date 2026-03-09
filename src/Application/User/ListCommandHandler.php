<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\DomainFactory;
use App\Domain\User\User as DomainUser;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ListCommandHandler
{
    private const USERS_API_URL = 'https://jsonplaceholder.typicode.com/users';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly DomainFactory $domainFactory,
    ) {
    }

    /**
     * @return DomainUser[]
     */
    public function handle(): array
    {
        $response = $this->httpClient->request('GET', self::USERS_API_URL);
        $items = $response->toArray();

        $users = [];
        foreach ($items as $row) {
            $users[] = $this->domainFactory->userFromApiRow($row);
        }

        return $users;
    }
}
