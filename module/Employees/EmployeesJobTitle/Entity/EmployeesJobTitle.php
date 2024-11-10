<?php

namespace Module\Employees\EmployeesJobTitle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Employees\EmployeesJobTitle\Repository\EmployeesJobTitleRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: EmployeesJobTitleRepository::class)]
#[ORM\Table(name: 'module_employees_job_title')]
class EmployeesJobTitle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'EmployeeJobTitleID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: 'EmployeeJobTitleCode', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "EmployeeJobTitleCode is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "EmployeeJobTitleCode can contain only letters, numbers, underscores, and hyphens.")]
    private string $employeeJobTitleCode;

    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    public function getEmployeeJobTitleID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    public function getEmployeeJobTitleCode(): string
    {
        return $this->employeeJobTitleCode;
    }

    public function setEmployeeJobTitleCode(string $employeeJobTitleCode): self
    {
        //$this->employeeJobTitleCode = strtolower(trim($employeeJobTitleCode));
        $this->employeeJobTitleCode = trim($employeeJobTitleCode);
        return $this;
    }

}
