<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Task\Task as DomainTask;
use App\Domain\Task\TaskStatus as DomainTaskStatus;
use App\Domain\User\Address;
use App\Domain\User\Company;
use App\Domain\User\User as DomainUser;
use App\Infrastructure\Doctrine\Task as DoctrineTask;
use App\Infrastructure\Doctrine\TaskHistory as DoctrineTaskHistory;
use App\Infrastructure\Doctrine\User as DoctrineUser;

final class DomainFactory
{
    public function fromDoctrineUser(DoctrineUser $user): DomainUser
    {
        $address = null;
        $addressValues = [
            $user->getAddressStreet(),
            $user->getAddressSuite(),
            $user->getAddressCity(),
            $user->getAddressZipcode(),
            $user->getAddressGeoLat(),
            $user->getAddressGeoLng(),
        ];
        if ($this->hasAnyNonEmpty($addressValues)) {
            $address = new Address(
                $user->getAddressStreet(),
                $user->getAddressSuite(),
                $user->getAddressCity(),
                $user->getAddressZipcode(),
                $user->getAddressGeoLat(),
                $user->getAddressGeoLng(),
            );
        }

        $company = null;
        $companyValues = [
            $user->getCompanyName(),
            $user->getCompanyCatchPhrase(),
            $user->getCompanyBs(),
        ];
        if ($this->hasAnyNonEmpty($companyValues)) {
            $company = new Company(
                $user->getCompanyName(),
                $user->getCompanyCatchPhrase(),
                $user->getCompanyBs(),
            );
        }

        return $this->createUser(
            $user->getId(),
            (string) $user->getName(),
            (string) $user->getUsername(),
            (string) $user->getEmail(),
            $address,
            $company,
            $user->getPhone(),
            $user->getWebsite(),
            $user->isAdmin(),
            $user->getCreatedAt() ?? new \DateTimeImmutable(),
        );
    }

    public function fromDoctrineTask(DoctrineTask $task, ?DoctrineUser $assignedUser = null): DomainTask
    {
        $domainAssignedUser = null;
        if (null !== $assignedUser) {
            $domainAssignedUser = $this->fromDoctrineUser($assignedUser);
        } elseif (null !== $task->getAssignedUser()) {
            $domainAssignedUser = $this->fromDoctrineUser($task->getAssignedUser());
        }

        return new DomainTask(
            $task->getId(),
            $task->getName(),
            $task->getDescription(),
            DomainTaskStatus::from($task->getStatus()->value),
            $task->getCreatedAt(),
            $task->getUpdatedAt(),
            $domainAssignedUser,
        );
    }

    public function fromDoctrineTaskHistory(DoctrineTaskHistory $history): DomainTask
    {
        $task = $history->getTask();
        $domainAssignedUser = null;
        if (null !== $history->getAssignedUser()) {
            $domainAssignedUser = $this->fromDoctrineUser($history->getAssignedUser());
        }

        $createdAt = $history->getCreatedAt();

        return new DomainTask(
            $task->getId(),
            $history->getName(),
            $history->getDescription(),
            DomainTaskStatus::from($history->getStatus()->value),
            $createdAt,
            $createdAt,
            $domainAssignedUser,
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    public function userFromApiRow(array $row): DomainUser
    {
        $address = null;
        if (isset($row['address']) && \is_array($row['address'])) {
            $address = $this->addressFromApiRow($row['address']);
        }

        $company = null;
        if (isset($row['company']) && \is_array($row['company'])) {
            $company = $this->companyFromApiRow($row['company']);
        }

        $id = isset($row['id']) ? (int) $row['id'] : null;
        $name = isset($row['name']) ? (string) $row['name'] : '';
        $username = isset($row['username']) ? (string) $row['username'] : '';
        $email = isset($row['email']) ? (string) $row['email'] : '';
        $phone = isset($row['phone']) ? (string) $row['phone'] : null;
        $website = isset($row['website']) ? (string) $row['website'] : null;

        return $this->createUser(
            $id,
            $name,
            $username,
            $email,
            $address,
            $company,
            $phone,
            $website,
            false,
            new \DateTimeImmutable(),
        );
    }

    public function createUser(
        ?int $id,
        string $name,
        string $username,
        string $email,
        ?Address $address,
        ?Company $company,
        ?string $phone,
        ?string $website,
        bool $isAdmin,
        \DateTimeImmutable $createdAt,
    ): DomainUser {
        return new DomainUser(
            $id,
            $name,
            $username,
            $email,
            $address,
            $company,
            $phone,
            $website,
            $isAdmin,
            $createdAt,
        );
    }

    /**
     * @param array<string, mixed> $addr
     */
    private function addressFromApiRow(array $addr): Address
    {
        $geo = $addr['geo'] ?? [];
        $geoLat = \is_array($geo) ? ($geo['lat'] ?? null) : null;
        $geoLng = \is_array($geo) ? ($geo['lng'] ?? null) : null;

        return new Address(
            isset($addr['street']) ? (string) $addr['street'] : null,
            isset($addr['suite']) ? (string) $addr['suite'] : null,
            isset($addr['city']) ? (string) $addr['city'] : null,
            isset($addr['zipcode']) ? (string) $addr['zipcode'] : null,
            $geoLat !== null ? (string) $geoLat : null,
            $geoLng !== null ? (string) $geoLng : null,
        );
    }

    /**
     * @param array<string, mixed> $comp
     */
    private function companyFromApiRow(array $comp): Company
    {
        return new Company(
            isset($comp['name']) ? (string) $comp['name'] : null,
            isset($comp['catchPhrase']) ? (string) $comp['catchPhrase'] : null,
            isset($comp['bs']) ? (string) $comp['bs'] : null,
        );
    }

    /**
     * @param array<int, mixed> $values
     */
    private function hasAnyNonEmpty(array $values): bool
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                if (trim($value) !== '') {
                    return true;
                }

                continue;
            }

            return true;
        }

        return false;
    }
}

