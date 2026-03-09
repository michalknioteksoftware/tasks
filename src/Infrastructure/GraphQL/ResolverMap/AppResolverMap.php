<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQL\ResolverMap;

use App\Infrastructure\GraphQL\Resolver\AllTasksResolver;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

final class AppResolverMap extends ResolverMap
{
    public function __construct(
        private readonly AllTasksResolver $allTasksResolver,
    ) {
    }

    protected function map(): array
    {
        return [
            'Query' => [
                'allTasks' => $this->allTasksResolver->__invoke(...),
            ],
        ];
    }
}
