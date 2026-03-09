<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250306200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert first admin user (username: admin, password: Password1)';
    }

    public function up(Schema $schema): void
    {
        $salt = $_ENV['PASSWORD_SALT'] ?? getenv('PASSWORD_SALT');
        if ($salt === false || $salt === '') {
            throw new \RuntimeException('PASSWORD_SALT env variable must be set to run this migration.');
        }

        $algo = $_ENV['PASSWORD_ALGO'] ?? getenv('PASSWORD_ALGO');
        if ($algo === false || $algo === '') {
            throw new \RuntimeException('PASSWORD_ALGO env variable must be set to run this migration.');
        }
        $password = hash($algo, $salt . 'Password1');

        $this->addSql(
            "INSERT INTO \"user\" (name, username, email, is_admin, password) VALUES ('Admin', 'admin', 'admin@example.com', true, '" . $password . "')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM \"user\" WHERE username = 'admin'");
    }
}
