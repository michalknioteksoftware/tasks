<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\DomainFactory;
use App\Domain\Task\Task as DomainTask;
use App\Domain\Task\TaskStatus;
use App\Domain\User\Address;
use App\Domain\User\Company;
use App\Domain\User\User as DomainUser;
use App\Infrastructure\Doctrine\Task as DoctrineTask;
use App\Infrastructure\Doctrine\TaskHistory as DoctrineTaskHistory;
use App\Infrastructure\Doctrine\User as DoctrineUser;
use PHPUnit\Framework\TestCase;

final class DomainFactoryTest extends TestCase
{
    private DomainFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new DomainFactory();
    }

    public function testFromDoctrineUserBuildsFullUserWithAddressAndCompany(): void
    {
        $createdAt = new \DateTimeImmutable('-1 day');

        $doctrineUser = (new DoctrineUser())
            ->setName('John Doe')
            ->setUsername('jdoe')
            ->setEmail('john@example.com')
            ->setAddressStreet('Main St 1')
            ->setAddressSuite('Apt. 2')
            ->setAddressCity('Prague')
            ->setAddressZipcode('11000')
            ->setAddressGeoLat('50.0878')
            ->setAddressGeoLng('14.4205')
            ->setPhone('+420123456789')
            ->setWebsite('example.com')
            ->setCompanyName('Acme Inc')
            ->setCompanyCatchPhrase('We build stuff')
            ->setCompanyBs('bs value')
            ->setIsAdmin(true)
            ->setCreatedAt($createdAt);

        $ref = new \ReflectionProperty(DoctrineUser::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($doctrineUser, 42);

        $user = $this->factory->fromDoctrineUser($doctrineUser);

        self::assertInstanceOf(DomainUser::class, $user);
        self::assertSame(42, $user->getId());
        self::assertSame('John Doe', $user->getName());
        self::assertSame('jdoe', $user->getUsername());
        self::assertSame('john@example.com', $user->getEmail());
        self::assertTrue($user->isAdmin());
        self::assertEquals($createdAt, $user->getCreatedAt());

        $address = $user->getAddress();
        self::assertInstanceOf(Address::class, $address);
        self::assertSame('Main St 1', $address->getStreet());
        self::assertSame('Apt. 2', $address->getSuite());
        self::assertSame('Prague', $address->getCity());
        self::assertSame('11000', $address->getZipcode());
        self::assertSame('50.0878', $address->getGeoLat());
        self::assertSame('14.4205', $address->getGeoLng());

        $company = $user->getCompany();
        self::assertInstanceOf(Company::class, $company);
        self::assertSame('Acme Inc', $company->getName());
        self::assertSame('We build stuff', $company->getCatchPhrase());
        self::assertSame('bs value', $company->getBs());
    }

    public function testFromDoctrineUserOmitsEmptyAddressAndCompany(): void
    {
        $doctrineUser = (new DoctrineUser())
            ->setName('No Address')
            ->setUsername('noaddr')
            ->setEmail('noaddr@example.com');

        $user = $this->factory->fromDoctrineUser($doctrineUser);

        self::assertNull($user->getAddress());
        self::assertNull($user->getCompany());
    }

    public function testFromDoctrineUserUsesNowWhenCreatedAtIsNull(): void
    {
        $doctrineUser = (new DoctrineUser())
            ->setName('No CreatedAt')
            ->setUsername('nocreated')
            ->setEmail('nocreated@example.com');

        $before = new \DateTimeImmutable('-1 minute');

        $user = $this->factory->fromDoctrineUser($doctrineUser);

        $after = new \DateTimeImmutable('+1 minute');

        self::assertInstanceOf(DomainUser::class, $user);
        self::assertGreaterThanOrEqual($before, $user->getCreatedAt());
        self::assertLessThanOrEqual($after, $user->getCreatedAt());
    }

    public function testFromDoctrineTaskUsesExplicitAssignedUserWhenProvided(): void
    {
        $task = $this->createDoctrineTask(1, 'Task A');
        $taskAssignedDoctrineUser = null; // simulate different task->assignedUser

        $explicitDoctrineUser = (new DoctrineUser())
            ->setName('Explicit User')
            ->setUsername('explicit')
            ->setEmail('explicit@example.com');

        $domainTask = $this->factory->fromDoctrineTask($task, $explicitDoctrineUser);

        self::assertInstanceOf(DomainTask::class, $domainTask);
        $assignedUser = $domainTask->getAssignedUser();
        self::assertInstanceOf(DomainUser::class, $assignedUser);
        self::assertSame('Explicit User', $assignedUser->getName());
    }

    public function testFromDoctrineTaskUsesTaskAssignedUserWhenExplicitIsNull(): void
    {
        $task = $this->createDoctrineTask(2, 'Task B');

        $taskDoctrineUser = (new DoctrineUser())
            ->setName('Task User')
            ->setUsername('taskuser')
            ->setEmail('taskuser@example.com');

        $task->setAssignedUser($taskDoctrineUser);

        $domainTask = $this->factory->fromDoctrineTask($task, null);

        $assignedUser = $domainTask->getAssignedUser();
        self::assertInstanceOf(DomainUser::class, $assignedUser);
        self::assertSame('Task User', $assignedUser->getName());
    }

    public function testFromDoctrineTaskLeavesAssignedUserNullWhenNoUser(): void
    {
        $task = $this->createDoctrineTask(3, 'Task C');

        $domainTask = $this->factory->fromDoctrineTask($task, null);

        self::assertNull($domainTask->getAssignedUser());
    }

    public function testFromDoctrineTaskMapsCoreFieldsAndStatusEnum(): void
    {
        $task = $this->createDoctrineTask(4, 'Status Task');

        $domainTask = $this->factory->fromDoctrineTask($task);

        self::assertInstanceOf(DomainTask::class, $domainTask);
        self::assertSame(4, $domainTask->getId());
        self::assertSame('Status Task', $domainTask->getName());
        self::assertSame('Description', $domainTask->getDescription());
        self::assertInstanceOf(TaskStatus::class, $domainTask->getStatus());
        self::assertSame(TaskStatus::ToDo, $domainTask->getStatus());
    }

    public function testFromDoctrineTaskHistoryCopiesHistoryValuesAndAssignedUser(): void
    {
        $task = $this->createDoctrineTask(10, 'Original Name');

        $history = new DoctrineTaskHistory();
        $history
            ->setTask($task)
            ->setName('History Name')
            ->setDescription('History Description')
            ->setStatus(TaskStatus::Done)
            ->setCreatedAt(new \DateTimeImmutable('2024-01-01T12:00:00Z'));

        $historyUser = (new DoctrineUser())
            ->setName('History User')
            ->setUsername('historyuser')
            ->setEmail('history@example.com');

        $history->setAssignedUser($historyUser);

        $domainTask = $this->factory->fromDoctrineTaskHistory($history);

        self::assertInstanceOf(DomainTask::class, $domainTask);
        self::assertSame(10, $domainTask->getId());
        self::assertSame('History Name', $domainTask->getName());
        self::assertSame('History Description', $domainTask->getDescription());
        self::assertSame(TaskStatus::Done, $domainTask->getStatus());
        self::assertEquals($history->getCreatedAt(), $domainTask->getCreatedAt());
        self::assertEquals($history->getCreatedAt(), $domainTask->getUpdatedAt());

        $assignedUser = $domainTask->getAssignedUser();
        self::assertInstanceOf(DomainUser::class, $assignedUser);
        self::assertSame('History User', $assignedUser->getName());
    }

    public function testFromDoctrineTaskHistoryWithNoAssignedUser(): void
    {
        $task = $this->createDoctrineTask(11, 'Original Name');

        $history = new DoctrineTaskHistory();
        $history
            ->setTask($task)
            ->setName('History Name')
            ->setDescription('History Description')
            ->setStatus(TaskStatus::InProgress)
            ->setCreatedAt(new \DateTimeImmutable('2024-01-02T15:00:00Z'));

        $domainTask = $this->factory->fromDoctrineTaskHistory($history);

        self::assertNull($domainTask->getAssignedUser());
    }

    public function testUserFromApiRowBuildsUserWithNestedObjects(): void
    {
        $row = [
            'id' => 99,
            'name' => 'API User',
            'username' => 'apiuser',
            'email' => 'api@example.com',
            'phone' => '+123',
            'website' => 'api.example.com',
            'address' => [
                'street' => 'API Street',
                'suite' => 'Suite 100',
                'city' => 'API City',
                'zipcode' => '99999',
                'geo' => [
                    'lat' => '1.23',
                    'lng' => '4.56',
                ],
            ],
            'company' => [
                'name' => 'API Co',
                'catchPhrase' => 'API all the things',
                'bs' => 'api-bs',
            ],
        ];

        $user = $this->factory->userFromApiRow($row);

        self::assertInstanceOf(DomainUser::class, $user);
        self::assertSame(99, $user->getId());
        self::assertFalse($user->isAdmin(), 'API users should not be admin');

        $address = $user->getAddress();
        self::assertInstanceOf(Address::class, $address);
        self::assertSame('API Street', $address->getStreet());
        self::assertSame('1.23', $address->getGeoLat());
        self::assertSame('4.56', $address->getGeoLng());

        $company = $user->getCompany();
        self::assertInstanceOf(Company::class, $company);
        self::assertSame('API Co', $company->getName());
    }

    public function testUserFromApiRowWithMissingOptionalFields(): void
    {
        $row = [
            // no id
            'name' => 'Minimal API User',
            'username' => 'minapi',
            'email' => 'minapi@example.com',
            // no address, no company, no phone, no website
        ];

        $before = new \DateTimeImmutable('-1 minute');

        $user = $this->factory->userFromApiRow($row);

        $after = new \DateTimeImmutable('+1 minute');

        self::assertNull($user->getId());
        self::assertSame('Minimal API User', $user->getName());
        self::assertNull($user->getAddress());
        self::assertNull($user->getCompany());
        self::assertNull($user->getPhone());
        self::assertNull($user->getWebsite());
        self::assertFalse($user->isAdmin());

        self::assertGreaterThanOrEqual($before, $user->getCreatedAt());
        self::assertLessThanOrEqual($after, $user->getCreatedAt());
    }

    public function testCreateUserReturnsDomainUserWithExactValues(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-03T10:00:00Z');
        $address = new Address('Street', 'Suite', 'City', 'Zip', '1', '2');
        $company = new Company('Name', 'Catch', 'Bs');

        $user = $this->factory->createUser(
            7,
            'Name',
            'username',
            'email@example.com',
            $address,
            $company,
            '123',
            'example.com',
            true,
            $createdAt,
        );

        self::assertInstanceOf(DomainUser::class, $user);
        self::assertSame(7, $user->getId());
        self::assertSame($address, $user->getAddress());
        self::assertSame($company, $user->getCompany());
        self::assertTrue($user->isAdmin());
        self::assertSame($createdAt, $user->getCreatedAt());
    }

    /**
     * Helper to create a DoctrineTask with basic valid data.
     */
    private function createDoctrineTask(int $id, string $name): DoctrineTask
    {
        $task = new DoctrineTask();
        $task
            ->setName($name)
            ->setDescription('Description')
            ->setStatus(TaskStatus::ToDo)
            ->setCreatedAt(new \DateTimeImmutable('2024-01-01T00:00:00Z'))
            ->setUpdatedAt(new \DateTimeImmutable('2024-01-02T00:00:00Z'));

        $ref = new \ReflectionProperty(DoctrineTask::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($task, $id);

        return $task;
    }
}
