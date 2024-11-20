<?php

namespace Module\Shared\RoleStatus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Shared\RoleStatus\Repository\RoleStatusTranslationsRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleStatusTranslationsRepository::class)]
#[ORM\Table(name: 'module_role_status_translations')]
class RoleStatusTranslations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'RoleStatusTranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: RoleStatus::class)]
    #[ORM\JoinColumn(name: 'RoleStatusID', referencedColumnName: 'RoleStatusID', nullable: false)]
    private RoleStatus $roleStatusID;

    #[ORM\Column(name: 'LanguageID', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "LanguageID is required.")]
    private int $languageID;

    #[ORM\Column(name: 'RoleStatusName', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "RoleStatusName is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "RoleStatusName can contain only letters, numbers, underscores, and hyphens.")]
    private string $roleStatusName;

    public function getRoleStatusTranslationID(): ?int
    {
        return $this->id;
    }

    public function getRoleStatusID(): RoleStatus
    {
        return $this->roleStatusID;
    }

    public function setRoleStatusID(RoleStatus $roleStatusID): self
    {
        $this->roleStatusID = $roleStatusID;
        return $this;
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

    public function getRoleStatusName(): string
    {
        return $this->roleStatusName;
    }

    public function setRoleStatusName(string $roleStatusName): self
    {
        $this->roleStatusName = $roleStatusName;
        return $this;
    }

}
