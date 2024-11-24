<?php

namespace Module\Persons\EducationLevels\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Persons\EducationLevels\Repository\EducationLevelTranslationsRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EducationLevelTranslationsRepository::class)]
#[ORM\Table(name: 'module_persons_education_levels_translations')]
class EducationLevelTranslations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'EducationLevelTranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EducationLevels::class)]
    #[ORM\JoinColumn(name: 'EducationLevelID', referencedColumnName: 'EducationLevelID', nullable: false)]
    private EducationLevels $educationLevelID;

    #[ORM\Column(name: 'LanguageID', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "LanguageID is required.")]
    private int $languageID;

    #[ORM\Column(name: 'EducationLevelName', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "EducationLevelName is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "EducationLevelName can contain only letters, numbers, underscores, and hyphens.")]
    private string $educationLevelName;

    public function getEducationLevelTranslationID(): ?int
    {
        return $this->id;
    }

    public function getEducationLevelID(): EducationLevels
    {
        return $this->educationLevelID;
    }

    public function setEducationLevelID(EducationLevels $educationLevelID): self
    {
        $this->educationLevelID = $educationLevelID;
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

    public function getEducationLevelName(): string
    {
        return $this->educationLevelName;
    }

    public function setEducationLevelName(string $educationLevelName): self
    {
        $this->educationLevelName = $educationLevelName;
        return $this;
    }

}