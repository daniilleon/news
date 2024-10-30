<?php

namespace Module\Employees\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Employees\Repository\EmployeesRepository;
use Module\Languages\Entity\Language; // Подключаем сущность Language
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeesRepository::class)]
#[ORM\Table(name: 'module_employees')]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'EmployeeID', type: 'integer')]
    private ?int $id = null;

    // Связь с таблицей Languages для хранения идентификатора языка
    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'LanguageID', referencedColumnName: 'LanguageID', nullable: false)]
    private Language $languageID;

    #[ORM\Column(name: 'EmployeeLink', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "EmployeeLink is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "EmployeeLink can contain only letters, numbers, underscores, and hyphens.")]
    private string $employeeLink;

    #[ORM\Column(name: 'EmployeeName', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "EmployeeName is required.")]
    #[Assert\Regex("/^[a-zA-Z\s]+$/", message: "EmployeeName can contain only letters and spaces.")]
    private string $employeeName;

    #[ORM\Column(name: 'EmployeeJobTitle', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "EmployeeJobTitle is required.")]
    #[Assert\Regex("/^[a-zA-Z\s0-9]+$/", message: "EmployeeJobTitle can contain only letters, spaces, and numbers.")]
    private string $employeeJobTitle;

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

    #[ORM\Column(name: 'CategoryID', type: 'integer')]
    #[Assert\NotBlank(message: "CategoryID is required.")]
    private int $categoryID;

    // Получение ID сотрудника
    public function getEmployeeID(): ?int
    {
        return $this->id;
    }

    // Получение объекта языка, к которому относится сотрудник
    public function getEmployeeLanguageID(): Language
    {
        return $this->languageID;
    }

    // Установка объекта языка для сотрудника
    public function setEmployeeLanguageID(Language $languageID): self
    {
        $this->languageID = $languageID;
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

    public function getEmployeeJobTitle(): string
    {
        return $this->employeeJobTitle;
    }

    public function setEmployeeJobTitle(string $employeeJobTitle): self
    {
        $this->employeeJobTitle = trim($employeeJobTitle);
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

    public function getEmployeeCategoryID(): int
    {
        return $this->categoryID;
    }

    public function setEmployeeCategoryID(int $categoryID): self
    {
        $this->categoryID = $categoryID;
        return $this;
    }
}
