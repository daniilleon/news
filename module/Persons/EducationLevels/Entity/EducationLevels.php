<?php

namespace Module\Persons\EducationLevels\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Persons\EducationLevels\Repository\EducationLevelsRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: EducationLevelsRepository::class)]
#[ORM\Table(name: 'module_persons_education_levels')]
class EducationLevels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'EducationLevelID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: 'EducationLevelCode', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "EducationLevelCode is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "EducationLevelCode can contain only letters, numbers, underscores, and hyphens.")]
    private string $educationLevelCode;

    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    public function getEducationLevelID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    public function getEducationLevelCode(): string
    {
        return $this->educationLevelCode;
    }

    public function setEducationLevelCode(string $educationLevelCode): self
    {
        $this->educationLevelCode = trim($educationLevelCode);
        return $this;
    }

}