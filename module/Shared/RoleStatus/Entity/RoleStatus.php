<?php

namespace Module\Shared\RoleStatus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Shared\RoleStatus\Repository\RoleStatusRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: RoleStatusRepository::class)]
#[ORM\Table(name: 'module_roles_status')]
class RoleStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'RoleStatusID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: 'RoleStatusCode', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "RoleStatusCode is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "RoleStatusCode can contain only letters, numbers, underscores, and hyphens.")]
    private string $roleStatusCode;

    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    public function getRoleStatusID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    public function getRoleStatusCode(): string
    {
        return $this->roleStatusCode;
    }

    public function setRoleStatusCode(string $roleStatusCode): self
    {
        $this->roleStatusCode = trim($roleStatusCode);
        return $this;
    }

}
