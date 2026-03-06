<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250306300000 extends AbstractMigration
{
    private const USERS_URL = 'https://jsonplaceholder.typicode.com/users';

    public function getDescription(): string
    {
        return 'Import users from JSON Placeholder API into user table';
    }

    public function up(Schema $schema): void
    {
        $salt = $_ENV['PASSWORD_SALT'] ?? getenv('PASSWORD_SALT');
        if ($salt === false || $salt === '') {
            throw new \RuntimeException('PASSWORD_SALT env variable must be set to run this migration.');
        }
        $placeholderPassword = hash('sha256', $salt . 'ChangeMe');

        $json = $this->fetchUsers();
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Failed to decode JSON from ' . self::USERS_URL);
        }

        foreach ($data as $row) {
            $username = isset($row['username']) ? trim((string) $row['username']) : null;
            $email = isset($row['email']) ? trim((string) $row['email']) : null;

            if ($username === '' || $email === '') {
                error_log(sprintf(
                    '[Migration %s] Skipping user: missing username or email (id from API: %s)',
                    self::class,
                    $row['id'] ?? '?'
                ));
                continue;
            }

            if ($this->usernameOrEmailExists($username, $email)) {
                error_log(sprintf(
                    '[Migration %s] Skipping user: username "%s" or email "%s" already exists (id from API: %s)',
                    self::class,
                    $username,
                    $email,
                    $row['id'] ?? '?'
                ));
                continue;
            }

            $this->insertUser($row, $placeholderPassword);
        }
    }

    public function down(Schema $schema): void
    {
        $usernames = [
            'Bret', 'Antonette', 'Samantha', 'Karianne', 'Kamren',
            'Leopoldo_Corkery', 'Elwyn.Skiles', 'Maxime_Nienow', 'Delphine', 'Moriah.Stanton',
        ];
        $placeholders = implode(', ', array_fill(0, count($usernames), '?'));
        $this->connection->executeStatement(
            'DELETE FROM "user" WHERE username IN (' . $placeholders . ')',
            $usernames
        );
    }

    private function fetchUsers(): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Symfony-Migration/1.0',
            ],
        ]);
        $json = file_get_contents(self::USERS_URL, false, $context);
        if ($json === false) {
            throw new \RuntimeException('Failed to fetch ' . self::USERS_URL . '. Check allow_url_fopen or network.');
        }
        return $json;
    }

    private function usernameOrEmailExists(string $username, string $email): bool
    {
        $sql = 'SELECT 1 FROM "user" WHERE username = :username OR email = :email LIMIT 1';
        $result = $this->connection->executeQuery($sql, [
            'username' => $username,
            'email' => $email,
        ]);
        return $result->fetchOne() !== false;
    }

    private function insertUser(array $row, string $passwordHash): void
    {
        $address = $row['address'] ?? [];
        $geo = is_array($address['geo'] ?? null) ? $address['geo'] : [];
        $company = $row['company'] ?? [];

        $this->connection->executeStatement(
            'INSERT INTO "user" (
                name, username, email,
                address_street, address_suite, address_city, address_zipcode, address_geo_lat, address_geo_lng,
                phone, website,
                company_name, company_catch_phrase, company_bs,
                is_admin, password
            ) VALUES (
                :name, :username, :email,
                :address_street, :address_suite, :address_city, :address_zipcode, :address_geo_lat, :address_geo_lng,
                :phone, :website,
                :company_name, :company_catch_phrase, :company_bs,
                false, :password
            )',
            [
                'name' => trim((string) ($row['name'] ?? '')),
                'username' => trim((string) ($row['username'] ?? '')),
                'email' => trim((string) ($row['email'] ?? '')),
                'address_street' => $this->stringOrNull($address['street'] ?? null),
                'address_suite' => $this->stringOrNull($address['suite'] ?? null),
                'address_city' => $this->stringOrNull($address['city'] ?? null),
                'address_zipcode' => $this->stringOrNull($address['zipcode'] ?? null, 20),
                'address_geo_lat' => $this->stringOrNull($geo['lat'] ?? null, 50),
                'address_geo_lng' => $this->stringOrNull($geo['lng'] ?? null, 50),
                'phone' => $this->stringOrNull($row['phone'] ?? null, 50),
                'website' => $this->stringOrNull($row['website'] ?? null),
                'company_name' => $this->stringOrNull($company['name'] ?? null),
                'company_catch_phrase' => $this->stringOrNull($company['catchPhrase'] ?? null),
                'company_bs' => $this->stringOrNull($company['bs'] ?? null),
                'password' => $passwordHash,
            ]
        );
    }

    private function stringOrNull(mixed $value, int $maxLength = 255): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }
        if (strlen($s) > $maxLength) {
            return substr($s, 0, $maxLength);
        }
        return $s;
    }
}
