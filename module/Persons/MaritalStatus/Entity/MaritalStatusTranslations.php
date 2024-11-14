<?php

namespace Module\Persons\MaritalStatus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Persons\MaritalStatus\Repository\MaritalStatusTranslationsRepository;
use Module\Languages\Entity\Language;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaritalStatusTranslationsRepository::class)]
#[ORM\Table(name: 'module_marital_status_translations')]
class MaritalStatusTranslations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'MaritalStatusTranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MaritalStatus::class)]
    #[ORM\JoinColumn(name: 'MaritalStatusID', referencedColumnName: 'MaritalStatusID', nullable: false)]
    private MaritalStatus $maritalStatusID;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'LanguageID', referencedColumnName: 'LanguageID', nullable: false)]
    private Language $languageID;

    #[ORM\Column(name: 'MaritalStatusName', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "MaritalStatusName is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "MaritalStatusName can contain only letters, numbers, underscores, and hyphens.")]
    private string $maritalStatusName;

    public function getMaritalStatusTranslationID(): ?int
    {
        return $this->id;
    }

    public function getMaritalStatusID(): MaritalStatus
    {
        return $this->maritalStatusID;
    }

    public function setMaritalStatusID(MaritalStatus $maritalStatusID): self
    {
        $this->maritalStatusID = $maritalStatusID;
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

    public function getMaritalStatusName(): string
    {
        return $this->maritalStatusName;
    }

    public function setMaritalStatusName(string $maritalStatusName): self
    {
        $this->maritalStatusName = $maritalStatusName;
        return $this;
    }

}