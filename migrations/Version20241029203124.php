<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241029203124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE module_employees (EmployeeID INT AUTO_INCREMENT NOT NULL, EmployeeLink VARCHAR(255) NOT NULL, EmployeeName VARCHAR(255) NOT NULL, EmployeeJobTitle VARCHAR(255) NOT NULL, EmployeeDescription LONGTEXT DEFAULT NULL, EmployeeLinkedIn VARCHAR(255) DEFAULT NULL, EmployeeInstagram VARCHAR(255) DEFAULT NULL, EmployeeFacebook VARCHAR(255) DEFAULT NULL, EmployeeTwitter VARCHAR(255) DEFAULT NULL, CategoryID INT NOT NULL, LanguageID INT NOT NULL, INDEX IDX_E0E3DCC8E03EAF66 (LanguageID), PRIMARY KEY(EmployeeID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_languages (LanguageID INT AUTO_INCREMENT NOT NULL, LanguageCode VARCHAR(10) NOT NULL, LanguageName VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_FAB04CB153FBF288 (LanguageCode), PRIMARY KEY(LanguageID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE module_employees ADD CONSTRAINT FK_E0E3DCC8E03EAF66 FOREIGN KEY (LanguageID) REFERENCES module_languages (LanguageID)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module_employees DROP FOREIGN KEY FK_E0E3DCC8E03EAF66');
        $this->addSql('DROP TABLE module_employees');
        $this->addSql('DROP TABLE module_languages');
    }
}
