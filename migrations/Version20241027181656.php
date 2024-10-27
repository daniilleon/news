<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241027181656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE module_employees (EmployeeID INT AUTO_INCREMENT NOT NULL, LanguageID INT NOT NULL, EmployeeLink VARCHAR(255) NOT NULL, EmployeeName VARCHAR(255) NOT NULL, EmployeeJobTitle VARCHAR(255) NOT NULL, EmployeeDescription LONGTEXT DEFAULT NULL, LinkedIn VARCHAR(255) DEFAULT NULL, Instagram VARCHAR(255) DEFAULT NULL, Facebook VARCHAR(255) DEFAULT NULL, Twitter VARCHAR(255) DEFAULT NULL, CategoryID INT NOT NULL, PRIMARY KEY(EmployeeID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_languages (LanguageID INT AUTO_INCREMENT NOT NULL, LanguageCode VARCHAR(10) NOT NULL, LanguageName VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_FAB04CB153FBF288 (LanguageCode), PRIMARY KEY(LanguageID)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE module_employees');
        $this->addSql('DROP TABLE module_languages');
    }
}
