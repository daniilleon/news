<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241110123930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE module_categories (CategoryID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, CategoryLink VARCHAR(255) NOT NULL, og_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(CategoryID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_category_translations (CategoryTranslationID INT AUTO_INCREMENT NOT NULL, CategoryName VARCHAR(100) NOT NULL, CategoryDescription LONGTEXT DEFAULT NULL, CategoryID INT NOT NULL, LanguageID INT NOT NULL, INDEX IDX_AE9FC350E8042869 (CategoryID), INDEX IDX_AE9FC350E03EAF66 (LanguageID), PRIMARY KEY(CategoryTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_countries (CountryID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, CountryLink VARCHAR(255) NOT NULL, og_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(CountryID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_country_translations (CountryTranslationID INT AUTO_INCREMENT NOT NULL, CountryName VARCHAR(100) NOT NULL, CountryDescription LONGTEXT DEFAULT NULL, CountryID INT NOT NULL, LanguageID INT NOT NULL, INDEX IDX_88253B2C423D04DF (CountryID), INDEX IDX_88253B2CE03EAF66 (LanguageID), PRIMARY KEY(CountryTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_employees (EmployeeID INT AUTO_INCREMENT NOT NULL, EmployeeActive TINYINT(1) DEFAULT 1 NOT NULL, EmployeeLink VARCHAR(255) NOT NULL, EmployeeName VARCHAR(255) NOT NULL, EmployeeDescription LONGTEXT DEFAULT NULL, EmployeeLinkedIn VARCHAR(255) DEFAULT NULL, EmployeeInstagram VARCHAR(255) DEFAULT NULL, EmployeeFacebook VARCHAR(255) DEFAULT NULL, EmployeeTwitter VARCHAR(255) DEFAULT NULL, LanguageID INT NOT NULL, CategoryID INT NOT NULL, EmployeeJobTitleID INT NOT NULL, INDEX IDX_E0E3DCC8E03EAF66 (LanguageID), INDEX IDX_E0E3DCC8E8042869 (CategoryID), INDEX IDX_E0E3DCC8F599824F (EmployeeJobTitleID), PRIMARY KEY(EmployeeID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_employees_job_title (EmployeeJobTitleID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, EmployeeJobTitleCode VARCHAR(255) NOT NULL, PRIMARY KEY(EmployeeJobTitleID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_employees_job_title_translations (EmployeeJobTitleTranslationID INT AUTO_INCREMENT NOT NULL, EmployeeJobTitleName VARCHAR(100) NOT NULL, EmployeeJobTitleID INT NOT NULL, LanguageID INT NOT NULL, INDEX IDX_EC30B0C5F599824F (EmployeeJobTitleID), INDEX IDX_EC30B0C5E03EAF66 (LanguageID), PRIMARY KEY(EmployeeJobTitleTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_languages (LanguageID INT AUTO_INCREMENT NOT NULL, LanguageCode VARCHAR(10) NOT NULL, LanguageName VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_FAB04CB153FBF288 (LanguageCode), PRIMARY KEY(LanguageID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE module_category_translations ADD CONSTRAINT FK_AE9FC350E8042869 FOREIGN KEY (CategoryID) REFERENCES module_categories (CategoryID)');
        $this->addSql('ALTER TABLE module_category_translations ADD CONSTRAINT FK_AE9FC350E03EAF66 FOREIGN KEY (LanguageID) REFERENCES module_languages (LanguageID)');
        $this->addSql('ALTER TABLE module_country_translations ADD CONSTRAINT FK_88253B2C423D04DF FOREIGN KEY (CountryID) REFERENCES module_countries (CountryID)');
        $this->addSql('ALTER TABLE module_country_translations ADD CONSTRAINT FK_88253B2CE03EAF66 FOREIGN KEY (LanguageID) REFERENCES module_languages (LanguageID)');
        $this->addSql('ALTER TABLE module_employees ADD CONSTRAINT FK_E0E3DCC8E03EAF66 FOREIGN KEY (LanguageID) REFERENCES module_languages (LanguageID)');
        $this->addSql('ALTER TABLE module_employees ADD CONSTRAINT FK_E0E3DCC8E8042869 FOREIGN KEY (CategoryID) REFERENCES module_categories (CategoryID)');
        $this->addSql('ALTER TABLE module_employees ADD CONSTRAINT FK_E0E3DCC8F599824F FOREIGN KEY (EmployeeJobTitleID) REFERENCES module_employees_job_title (EmployeeJobTitleID)');
        $this->addSql('ALTER TABLE module_employees_job_title_translations ADD CONSTRAINT FK_EC30B0C5F599824F FOREIGN KEY (EmployeeJobTitleID) REFERENCES module_employees_job_title (EmployeeJobTitleID)');
        $this->addSql('ALTER TABLE module_employees_job_title_translations ADD CONSTRAINT FK_EC30B0C5E03EAF66 FOREIGN KEY (LanguageID) REFERENCES module_languages (LanguageID)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module_category_translations DROP FOREIGN KEY FK_AE9FC350E8042869');
        $this->addSql('ALTER TABLE module_category_translations DROP FOREIGN KEY FK_AE9FC350E03EAF66');
        $this->addSql('ALTER TABLE module_country_translations DROP FOREIGN KEY FK_88253B2C423D04DF');
        $this->addSql('ALTER TABLE module_country_translations DROP FOREIGN KEY FK_88253B2CE03EAF66');
        $this->addSql('ALTER TABLE module_employees DROP FOREIGN KEY FK_E0E3DCC8E03EAF66');
        $this->addSql('ALTER TABLE module_employees DROP FOREIGN KEY FK_E0E3DCC8E8042869');
        $this->addSql('ALTER TABLE module_employees DROP FOREIGN KEY FK_E0E3DCC8F599824F');
        $this->addSql('ALTER TABLE module_employees_job_title_translations DROP FOREIGN KEY FK_EC30B0C5F599824F');
        $this->addSql('ALTER TABLE module_employees_job_title_translations DROP FOREIGN KEY FK_EC30B0C5E03EAF66');
        $this->addSql('DROP TABLE module_categories');
        $this->addSql('DROP TABLE module_category_translations');
        $this->addSql('DROP TABLE module_countries');
        $this->addSql('DROP TABLE module_country_translations');
        $this->addSql('DROP TABLE module_employees');
        $this->addSql('DROP TABLE module_employees_job_title');
        $this->addSql('DROP TABLE module_employees_job_title_translations');
        $this->addSql('DROP TABLE module_languages');
    }
}
