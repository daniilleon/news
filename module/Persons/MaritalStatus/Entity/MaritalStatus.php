<?php

namespace Module\Persons\MaritalStatus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Persons\MaritalStatus\Repository\MaritalStatusRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: MaritalStatusRepository::class)]
#[ORM\Table(name: 'module_persons_marital_status')]
class MaritalStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'MaritalStatusID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: 'MaritalStatusCode', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "MaritalStatusCode is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "MaritalStatusCode can contain only letters, numbers, underscores, and hyphens.")]
    private string $maritalStatusCode;

    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    public function getMaritalStatusID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    public function getMaritalStatusCode(): string
    {
        return $this->maritalStatusCode;
    }

    public function setMaritalStatusCode(string $maritalStatusCode): self
    {
        $this->maritalStatusCode = trim($maritalStatusCode);
        return $this;
    }

}
