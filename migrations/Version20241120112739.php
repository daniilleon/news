<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241120112739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE module_categories (CategoryID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, CategoryLink VARCHAR(255) NOT NULL, og_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(CategoryID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_category_translations (CategoryTranslationID INT AUTO_INCREMENT NOT NULL, LanguageID INT NOT NULL, CategoryName VARCHAR(100) NOT NULL, CategoryDescription LONGTEXT DEFAULT NULL, CategoryID INT NOT NULL, INDEX IDX_AE9FC350E8042869 (CategoryID), PRIMARY KEY(CategoryTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_countries (CountryID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, CountryLink VARCHAR(255) NOT NULL, og_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(CountryID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_country_translations (CountryTranslationID INT AUTO_INCREMENT NOT NULL, LanguageID INT NOT NULL, CountryName VARCHAR(100) NOT NULL, CountryDescription LONGTEXT DEFAULT NULL, CountryID INT NOT NULL, INDEX IDX_88253B2C423D04DF (CountryID), PRIMARY KEY(CountryTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_employees (EmployeeID INT AUTO_INCREMENT NOT NULL, LanguageID INT NOT NULL, CategoryID INT NOT NULL, EmployeeActive TINYINT(1) DEFAULT 0 NOT NULL, EmployeeLink VARCHAR(255) NOT NULL, EmployeeName VARCHAR(255) NOT NULL, EmployeeDescription LONGTEXT DEFAULT NULL, EmployeeLinkedIn VARCHAR(255) DEFAULT NULL, EmployeeInstagram VARCHAR(255) DEFAULT NULL, EmployeeFacebook VARCHAR(255) DEFAULT NULL, EmployeeTwitter VARCHAR(255) DEFAULT NULL, EmployeeJobTitleID INT NOT NULL, INDEX IDX_E0E3DCC8F599824F (EmployeeJobTitleID), PRIMARY KEY(EmployeeID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_employees_job_title (EmployeeJobTitleID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, EmployeeJobTitleCode VARCHAR(255) NOT NULL, PRIMARY KEY(EmployeeJobTitleID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_employees_job_title_translations (EmployeeJobTitleTranslationID INT AUTO_INCREMENT NOT NULL, LanguageID INT NOT NULL, EmployeeJobTitleName VARCHAR(100) NOT NULL, EmployeeJobTitleID INT NOT NULL, INDEX IDX_EC30B0C5F599824F (EmployeeJobTitleID), PRIMARY KEY(EmployeeJobTitleTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_industries (IndustryID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, IndustryLink VARCHAR(255) NOT NULL, og_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(IndustryID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_industry_translations (IndustryTranslationID INT AUTO_INCREMENT NOT NULL, LanguageID INT NOT NULL, IndustryName VARCHAR(100) NOT NULL, IndustryDescription LONGTEXT DEFAULT NULL, IndustryID INT NOT NULL, INDEX IDX_F67277F9B48D3C8A (IndustryID), PRIMARY KEY(IndustryTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_languages (LanguageID INT AUTO_INCREMENT NOT NULL, LanguageCode VARCHAR(10) NOT NULL, LanguageName VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_FAB04CB153FBF288 (LanguageCode), PRIMARY KEY(LanguageID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_persons_education_levels (EducationLevelID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, EducationLevelCode VARCHAR(255) NOT NULL, PRIMARY KEY(EducationLevelID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_persons_education_levels_translations (EducationLevelTranslationID INT AUTO_INCREMENT NOT NULL, LanguageID INT NOT NULL, EducationLevelName VARCHAR(100) NOT NULL, EducationLevelID INT NOT NULL, INDEX IDX_5A2363DD8B5129EA (EducationLevelID), PRIMARY KEY(EducationLevelTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_persons_marital_status (MaritalStatusID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, MaritalStatusCode VARCHAR(255) NOT NULL, PRIMARY KEY(MaritalStatusID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_persons_marital_status_translations (MaritalStatusTranslationID INT AUTO_INCREMENT NOT NULL, LanguageID INT NOT NULL, MaritalStatusName VARCHAR(100) NOT NULL, MaritalStatusID INT NOT NULL, INDEX IDX_A51E7392306AA9F (MaritalStatusID), PRIMARY KEY(MaritalStatusTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_role_status_translations (RoleStatusTranslationID INT AUTO_INCREMENT NOT NULL, LanguageID INT NOT NULL, RoleStatusName VARCHAR(100) NOT NULL, RoleStatusID INT NOT NULL, INDEX IDX_E4D7569DE3BE2D98 (RoleStatusID), PRIMARY KEY(RoleStatusTranslationID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_roles_status (RoleStatusID INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, RoleStatusCode VARCHAR(255) NOT NULL, PRIMARY KEY(RoleStatusID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE module_category_translations ADD CONSTRAINT FK_AE9FC350E8042869 FOREIGN KEY (CategoryID) REFERENCES module_categories (CategoryID)');
        $this->addSql('ALTER TABLE module_country_translations ADD CONSTRAINT FK_88253B2C423D04DF FOREIGN KEY (CountryID) REFERENCES module_countries (CountryID)');
        $this->addSql('ALTER TABLE module_employees ADD CONSTRAINT FK_E0E3DCC8F599824F FOREIGN KEY (EmployeeJobTitleID) REFERENCES module_employees_job_title (EmployeeJobTitleID)');
        $this->addSql('ALTER TABLE module_employees_job_title_translations ADD CONSTRAINT FK_EC30B0C5F599824F FOREIGN KEY (EmployeeJobTitleID) REFERENCES module_employees_job_title (EmployeeJobTitleID)');
        $this->addSql('ALTER TABLE module_industry_translations ADD CONSTRAINT FK_F67277F9B48D3C8A FOREIGN KEY (IndustryID) REFERENCES module_industries (IndustryID)');
        $this->addSql('ALTER TABLE module_persons_education_levels_translations ADD CONSTRAINT FK_5A2363DD8B5129EA FOREIGN KEY (EducationLevelID) REFERENCES module_persons_education_levels (EducationLevelID)');
        $this->addSql('ALTER TABLE module_persons_marital_status_translations ADD CONSTRAINT FK_A51E7392306AA9F FOREIGN KEY (MaritalStatusID) REFERENCES module_persons_marital_status (MaritalStatusID)');
        $this->addSql('ALTER TABLE module_role_status_translations ADD CONSTRAINT FK_E4D7569DE3BE2D98 FOREIGN KEY (RoleStatusID) REFERENCES module_roles_status (RoleStatusID)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module_category_translations DROP FOREIGN KEY FK_AE9FC350E8042869');
        $this->addSql('ALTER TABLE module_country_translations DROP FOREIGN KEY FK_88253B2C423D04DF');
        $this->addSql('ALTER TABLE module_employees DROP FOREIGN KEY FK_E0E3DCC8F599824F');
        $this->addSql('ALTER TABLE module_employees_job_title_translations DROP FOREIGN KEY FK_EC30B0C5F599824F');
        $this->addSql('ALTER TABLE module_industry_translations DROP FOREIGN KEY FK_F67277F9B48D3C8A');
        $this->addSql('ALTER TABLE module_persons_education_levels_translations DROP FOREIGN KEY FK_5A2363DD8B5129EA');
        $this->addSql('ALTER TABLE module_persons_marital_status_translations DROP FOREIGN KEY FK_A51E7392306AA9F');
        $this->addSql('ALTER TABLE module_role_status_translations DROP FOREIGN KEY FK_E4D7569DE3BE2D98');
        $this->addSql('DROP TABLE module_categories');
        $this->addSql('DROP TABLE module_category_translations');
        $this->addSql('DROP TABLE module_countries');
        $this->addSql('DROP TABLE module_country_translations');
        $this->addSql('DROP TABLE module_employees');
        $this->addSql('DROP TABLE module_employees_job_title');
        $this->addSql('DROP TABLE module_employees_job_title_translations');
        $this->addSql('DROP TABLE module_industries');
        $this->addSql('DROP TABLE module_industry_translations');
        $this->addSql('DROP TABLE module_languages');
        $this->addSql('DROP TABLE module_persons_education_levels');
        $this->addSql('DROP TABLE module_persons_education_levels_translations');
        $this->addSql('DROP TABLE module_persons_marital_status');
        $this->addSql('DROP TABLE module_persons_marital_status_translations');
        $this->addSql('DROP TABLE module_role_status_translations');
        $this->addSql('DROP TABLE module_roles_status');
    }
}
