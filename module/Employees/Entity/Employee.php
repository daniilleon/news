<?php

namespace Module\Employees\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Employees\Repository\EmployeeRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\Table(name: 'module_employees')]
class Employee
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'EmployeeID', type: 'integer')]
    private ?int $id = null;


    #[ORM\Column(name: 'LanguageID', type: 'integer')]
    #[Assert\NotBlank(message: "LanguageID is required.")]
    private int $languageID;

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

    #[ORM\Column(name: 'LinkedIn', type: 'string', length: 255, nullable: true)]
    #[Assert\Regex("/^[a-zA-Z0-9@._-]*$/", message: "LinkedIn can contain only letters, numbers, @, ., _ and -.")]
    private ?string $linkedIn = null;

    #[ORM\Column(name: 'Instagram', type: 'string', length: 255, nullable: true)]
    #[Assert\Regex("/^[a-zA-Z0-9@._-]*$/", message: "Instagram can contain only letters, numbers, @, ., _ and -.")]
    private ?string $instagram = null;

    #[ORM\Column(name: 'Facebook', type: 'string', length: 255, nullable: true)]
    #[Assert\Regex("/^[a-zA-Z0-9@._-]*$/", message: "Facebook can contain only letters, numbers, @, ., _ and -.")]
    private ?string $facebook = null;

    #[ORM\Column(name: 'Twitter', type: 'string', length: 255, nullable: true)]
    #[Assert\Regex("/^[a-zA-Z0-9@._-]*$/", message: "Twitter can contain only letters, numbers, @, ., _ and -.")]
    private ?string $twitter = null;

    #[ORM\Column(name: 'CategoryID', type: 'integer')]
    #[Assert\NotBlank(message: "CategoryID is required.")]
    private int $categoryID;

    // Получение ID сотрудника
    public function getEmployeeID(): ?int
    {
        return $this->id;
    }

    // Получение LanguageID
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    // Установка LanguageID
    public function setLanguageID(int $languageID): self
    {
        $this->languageID = $languageID;
        return $this;
    }

    // Получение EmployeeLink
    public function getEmployeeLink(): string
    {
        return $this->employeeLink;
    }

    // Установка EmployeeLink
    public function setEmployeeLink(string $employeeLink): self
    {
        $this->employeeLink = strtolower(trim($employeeLink));
        return $this;
    }

    // Получение EmployeeName
    public function getEmployeeName(): string
    {
        return $this->employeeName;
    }

    // Установка EmployeeName
    public function setEmployeeName(string $employeeName): self
    {
        $this->employeeName = ucwords(strtolower(trim($employeeName)));
        return $this;
    }

    // Получение EmployeeJobTitle
    public function getEmployeeJobTitle(): string
    {
        return $this->employeeJobTitle;
    }

    // Установка EmployeeJobTitle
    public function setEmployeeJobTitle(string $employeeJobTitle): self
    {
        $this->employeeJobTitle = trim($employeeJobTitle);
        return $this;
    }

    // Получение EmployeeDescription
    public function getEmployeeDescription(): ?string
    {
        return $this->employeeDescription;
    }

    // Установка EmployeeDescription
    public function setEmployeeDescription(?string $employeeDescription): self
    {
        $this->employeeDescription = $employeeDescription ? trim($employeeDescription) : null;
        return $this;
    }

    // Получение LinkedIn
    public function getLinkedIn(): ?string
    {
        return $this->linkedIn;
    }

    // Установка LinkedIn
    public function setLinkedIn(?string $linkedIn): self
    {
        $this->linkedIn = $linkedIn ? trim($linkedIn) : null;
        return $this;
    }

    // Получение Instagram
    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    // Установка Instagram
    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram ? trim($instagram) : null;
        return $this;
    }

    // Получение Facebook
    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    // Установка Facebook
    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook ? trim($facebook) : null;
        return $this;
    }

    // Получение Twitter
    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    // Установка Twitter
    public function setTwitter(?string $twitter): self
    {
        $this->twitter = $twitter ? trim($twitter) : null;
        return $this;
    }

    // Получение CategoryID
    public function getCategoryID(): int
    {
        return $this->categoryID;
    }

    // Установка CategoryID
    public function setCategoryID(int $categoryID): self
    {
        $this->categoryID = $categoryID;
        return $this;
    }
}
