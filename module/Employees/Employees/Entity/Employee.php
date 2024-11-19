<?php

namespace Module\Employees\Employees\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Categories\Entity\Categories;
use Module\Employees\Employees\Repository\EmployeesRepository;
use Module\Employees\EmployeesJobTitle\Entity\EmployeesJobTitle;
use Module\Languages\Entity\Language;
use Symfony\Component\Validator\Constraints as Assert;

// Подключаем сущность Language

#[ORM\Entity(repositoryClass: EmployeesRepository::class)]
#[ORM\Table(name: 'module_employees')]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'EmployeeID', type: 'integer')]
    private ?int $id = null;

    // Связь с таблицей Languages для хранения идентификатора языка
    #[ORM\Column(name: 'LanguageID', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "LanguageID is required.")]
    private int $languageID;

    #[ORM\Column(name: 'CategoryID', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "CategoryID is required.")]
    private int $categoryID;

    #[ORM\ManyToOne(targetEntity: EmployeesJobTitle::class)]
    #[ORM\JoinColumn(name: 'EmployeeJobTitleID', referencedColumnName: 'EmployeeJobTitleID', nullable: false)]
    private EmployeesJobTitle $employeeJobTitleID;

    #[ORM\Column(name: 'EmployeeActive', type: 'boolean', options: ['default' => false])]
    #[Assert\NotBlank(message: "EmployeeActive is required.")]
    private bool $employeeActive = false; // Значение по умолчанию: true, если сотрудник активен


    #[ORM\Column(name: 'EmployeeLink', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "EmployeeLink is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "EmployeeLink can contain only letters, numbers, underscores, and hyphens.")]
    private string $employeeLink;

    #[ORM\Column(name: 'EmployeeName', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "EmployeeName is required.")]
    #[Assert\Regex("/^[a-zA-Z\s]+$/", message: "EmployeeName can contain only letters and spaces.")]
    private string $employeeName;

    #[ORM\Column(name: 'EmployeeDescription', type: 'text', nullable: true)]
    #[Assert\Regex("/^[a-zA-Z\s0-9]*$/", message: "EmployeeDescription can contain only letters, spaces, and numbers.")]
    private ?string $employeeDescription = null;

    #[ORM\Column(name: 'EmployeeLinkedIn', type: 'string', length: 255, nullable: true)]
    #[Assert\Regex("/^[a-zA-Z0-9@._-]*$/", message: "LinkedIn can contain only letters, numbers, @, ., _ and -.")]
    private ?string $employeeLinkedIn = null;

    #[ORM\Column(name: 'EmployeeInstagram', type: 'string', length: 255, nullable: true)]
    #[Assert\Regex("/^[a-zA-Z0-9@._-]*$/", message: "Instagram can contain only letters, numbers, @, ., _ and -.")]
    private ?string $employeeInstagram = null;

    #[ORM\Column(name: 'EmployeeFacebook', type: 'string', length: 255, nullable: true)]
    #[Assert\Regex("/^[a-zA-Z0-9@._-]*$/", message: "Facebook can contain only letters, numbers, @, ., _ and -.")]
    private ?string $employeeFacebook = null;

    #[ORM\Column(name: 'EmployeeTwitter', type: 'string', length: 255, nullable: true)]
    #[Assert\Regex("/^[a-zA-Z0-9@._-]*$/", message: "Twitter can contain only letters, numbers, @, ., _ and -.")]
    private ?string $employeeTwitter = null;

    // Получение ID сотрудника
    public function getEmployeeID(): ?int
    {
        return $this->id;
    }

    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    public function setLanguageID(int $languageID): self
    {
        $this->languageID = $languageID;
        return $this;
    }

    public function getCategoryID(): int
    {
        return $this->categoryID;
    }

    public function setCategoryID(int $categoryID): self
    {
        $this->categoryID = $categoryID;
        return $this;
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

    // Геттер для EmployeeActive
    public function getEmployeeActive(): bool
    {
        return $this->employeeActive;
    }

    // Сеттер для EmployeeActive
    public function setEmployeeActive(bool $employeeActive): self
    {
        $this->employeeActive = $employeeActive;
        return $this;
    }

    public function getEmployeeLink(): string
    {
        return $this->employeeLink;
    }

    public function setEmployeeLink(string $employeeLink): self
    {
        $this->employeeLink = strtolower(trim($employeeLink));
        return $this;
    }

    public function getEmployeeName(): string
    {
        return $this->employeeName;
    }

    public function setEmployeeName(string $employeeName): self
    {
        $this->employeeName = ucwords(strtolower(trim($employeeName)));
        return $this;
    }


    public function getEmployeeDescription(): ?string
    {
        return $this->employeeDescription;
    }

    public function setEmployeeDescription(?string $employeeDescription): self
    {
        $this->employeeDescription = $employeeDescription ? trim($employeeDescription) : null;
        return $this;
    }

    public function getEmployeeLinkedIn(): ?string
    {
        return $this->employeeLinkedIn;
    }

    public function setEmployeeLinkedIn(?string $employeeLinkedIn): self
    {
        $this->employeeLinkedIn = $employeeLinkedIn ? trim($employeeLinkedIn) : null;
        return $this;
    }

    public function getEmployeeInstagram(): ?string
    {
        return $this->employeeInstagram;
    }

    public function setEmployeeInstagram(?string $employeeInstagram): self
    {
        $this->employeeInstagram = $employeeInstagram ? trim($employeeInstagram) : null;
        return $this;
    }

    public function getEmployeeFacebook(): ?string
    {
        return $this->employeeFacebook;
    }

    public function setEmployeeFacebook(?string $employeeFacebook): self
    {
        $this->employeeFacebook = $employeeFacebook ? trim($employeeFacebook) : null;
        return $this;
    }

    public function getEmployeeTwitter(): ?string
    {
        return $this->employeeTwitter;
    }

    public function setEmployeeTwitter(?string $employeeTwitter): self
    {
        $this->employeeTwitter = $employeeTwitter ? trim($employeeTwitter) : null;
        return $this;
    }

}
