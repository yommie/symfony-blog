<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200621110131 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('INSERT INTO `user` (`username`, `roles`, `password`, `permissions`, `created_date`) VALUES 
            ("yommie", "[\"ROLE_ADMIN\"]", "$2y$13$XQPRywkOj6YlyvxmnJUfA.Yx/AYSvsuCz6JY3GSLLTp79gIQLHzoK", "[]", NOW())
        ');

    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DELETE FROM `user` WHERE `username` = "yommie"');

    }
}
