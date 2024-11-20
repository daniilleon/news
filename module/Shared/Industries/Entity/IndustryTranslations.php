<?php

namespace Module\Shared\Industries\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Shared\Industries\Repository\IndustryTranslationsRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IndustryTranslationsRepository::class)]
#[ORM\Table(name: 'module_industry_translations')]
class IndustryTranslations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'IndustryTranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Industries::class)]
    #[ORM\JoinColumn(name: 'IndustryID', referencedColumnName: 'IndustryID', nullable: false)]
    private Industries $industryID;

    #[ORM\Column(name: 'LanguageID', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "LanguageID is required.")]
    private int $languageID;

    #[ORM\Column(name: 'IndustryName', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "IndustryName is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "IndustryName can contain only letters, numbers, underscores, and hyphens.")]
    private string $industryName;

    #[ORM\Column(name: 'IndustryDescription', type: 'text', nullable: true)]
    private ?string $industryDescription = null;

    public function getIndustryTranslationID(): ?int
    {
        return $this->id;
    }

    public function getIndustryID(): Industries
    {
        return $this->industryID;
    }

    public function setIndustryID(Industries $industryID): self
    {
        $this->industryID = $industryID;
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

    public function getIndustryName(): string
    {
        return $this->industryName;
    }

    public function setIndustryName(string $industryName): self
    {
        $this->industryName = $industryName;
        return $this;
    }

    public function getIndustryDescription(): ?string
    {
        return $this->industryDescription;
    }

    public function setIndustryDescription(?string $industryDescription): self
    {
        $this->industryDescription = $industryDescription;
        return $this;
    }
}
