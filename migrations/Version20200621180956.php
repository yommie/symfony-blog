<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200621180956 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('UPDATE `user` SET `roles` = "[\"ROLE_SUPER_ADMIN\"]" WHERE `username` = "yommie"');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('UPDATE `user` SET `roles` = "[\"ROLE_ADMIN\"]" WHERE `username` = "yommie"');

    }
}
