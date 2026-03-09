<?php

declare(strict_types=1);

namespace App\Domain\User;

final class Company
{
    public function __construct(
        private readonly ?string $name,
        private readonly ?string $catchPhrase,
        private readonly ?string $bs,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCatchPhrase(): ?string
    {
        return $this->catchPhrase;
    }

    public function getBs(): ?string
    {
        return $this->bs;
    }
}

