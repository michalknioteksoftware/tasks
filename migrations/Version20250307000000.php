<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250307000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create task table with status enum-like field and relation to user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE task (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL CHECK (status IN ('To Do', 'In Progress', 'Done')),
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
            assigned_user_id INT DEFAULT NULL,
            CONSTRAINT fk_task_user_assigned FOREIGN KEY (assigned_user_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
    }
}

