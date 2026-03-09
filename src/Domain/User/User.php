<?php

declare(strict_types=1);

namespace App\Domain\User;

final class User
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $name,
        private readonly string $username,
        private readonly string $email,
        private readonly ?Address $address,
        private readonly ?Company $company,
        private readonly ?string $phone,
        private readonly ?string $website,
        private readonly bool $isAdmin,
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

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
}

