<?php

namespace Module\Employees\EmployeesJobTitle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Employees\EmployeesJobTitle\Repository\EmployeeJobTitleTranslationsRepository;
use Module\Languages\Entity\Language;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeJobTitleTranslationsRepository::class)]
#[ORM\Table(name: 'module_employees_job_title_translations')]
class EmployeeJobTitleTranslations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'EmployeeJobTitleTranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EmployeesJobTitle::class)]
    #[ORM\JoinColumn(name: 'EmployeeJobTitleID', referencedColumnName: 'EmployeeJobTitleID', nullable: false)]
    private EmployeesJobTitle $employeeJobTitleID;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'LanguageID', referencedColumnName: 'LanguageID', nullable: false)]
    private Language $languageID;

    #[ORM\Column(name: 'EmployeeJobTitleName', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "EmployeeJobTitleName is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "EmployeeJobTitleName can contain only letters, numbers, underscores, and hyphens.")]
    private string $employeeJobTitleName;

    public function getEmployeeJobTitleTranslationID(): ?int
    {
        return $this->id;
    }

    public function getEmployeeJobTitleID(): EmployeesJobTitle
    {
        return $this->employeeJobTitleID;
    }

    public function setEmployeeJobTitleID(EmployeesJobTitle $employeeJobTitleID): self
    {
        $this->employeeJobTitleID = $employeeJobTitleID;
        return $this;
    }

    public function getLanguageID(): Language
    {
        return $this->languageID;
    }

    public function setLanguageID(Language $languageID): self
    {
        $this->languageID = $languageID;
        return $this;
    }

    public function getEmployeeJobTitleName(): string
    {
        return $this->employeeJobTitleName;
    }

    public function setEmployeeJobTitleName(string $employeeJobTitleName): self
    {
        $this->employeeJobTitleName = $employeeJobTitleName;
        return $this;
    }

}
