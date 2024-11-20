<?php

namespace Module\Core\Countries\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Core\Countries\Repository\CountryTranslationsRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CountryTranslationsRepository::class)]
#[ORM\Table(name: 'module_country_translations')]
class CountryTranslations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CountryTranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Countries::class)]
    #[ORM\JoinColumn(name: 'CountryID', referencedColumnName: 'CountryID', nullable: false)]
    private Countries $countryID;

    #[ORM\Column(name: 'LanguageID', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "LanguageID is required.")]
    private int $languageID;

    #[ORM\Column(name: 'CountryName', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "CountryName is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "CountryName can contain only letters, numbers, underscores, and hyphens.")]
    private string $countryName;

    #[ORM\Column(name: 'CountryDescription', type: 'text', nullable: true)]
    private ?string $countryDescription = null;

    public function getCountryTranslationID(): ?int
    {
        return $this->id;
    }

    public function getCountryID(): Countries
    {
        return $this->countryID;
    }

    public function setCountryID(Countries $countryID): self
    {
        $this->countryID = $countryID;
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

    public function getCountryName(): string
    {
        return $this->countryName;
    }

    public function setCountryName(string $countryName): self
    {
        $this->countryName = $countryName;
        return $this;
    }

    public function getCountryDescription(): ?string
    {
        return $this->countryDescription;
    }

    public function setCountryDescription(?string $countryDescription): self
    {
        $this->countryDescription = $countryDescription;
        return $this;
    }
}
