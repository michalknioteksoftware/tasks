<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250306100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table based on JSON Placeholder structure with is_admin and password';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "user" (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            address_street VARCHAR(255) DEFAULT NULL,
            address_suite VARCHAR(255) DEFAULT NULL,
            address_city VARCHAR(255) DEFAULT NULL,
            address_zipcode VARCHAR(20) DEFAULT NULL,
            address_geo_lat VARCHAR(50) DEFAULT NULL,
            address_geo_lng VARCHAR(50) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            company_name VARCHAR(255) DEFAULT NULL,
            company_catch_phrase VARCHAR(255) DEFAULT NULL,
            company_bs VARCHAR(255) DEFAULT NULL,
            is_admin BOOLEAN NOT NULL DEFAULT false,
            password VARCHAR(255) NOT NULL
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "user"');
    }
}
