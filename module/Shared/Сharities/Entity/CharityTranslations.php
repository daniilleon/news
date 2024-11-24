<?php

namespace Module\Shared\Charities\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Shared\Charities\Repository\CharityTranslationsRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CharityTranslationsRepository::class)]
#[ORM\Table(name: 'module_charity_translations')]
class CharityTranslations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CharityTranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Charities::class)]
    #[ORM\JoinColumn(name: 'CharityID', referencedColumnName: 'CharityID', nullable: false)]
    private Charities $charityID;

    #[ORM\Column(name: 'LanguageID', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "LanguageID is required.")]
    private int $languageID;

    #[ORM\Column(name: 'CharityName', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "CharityName is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "CharityName can contain only letters, numbers, underscores, and hyphens.")]
    private string $charityName;

    #[ORM\Column(name: 'CharityDescription', type: 'text', nullable: true)]
    private ?string $charityDescription = null;

    public function getCharityTranslationID(): ?int
    {
        return $this->id;
    }

    public function getCharityID(): Charities
    {
        return $this->charityID;
    }

    public function setCharityID(Charities $charityID): self
    {
        $this->charityID = $charityID;
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

    public function getCharityName(): string
    {
        return $this->charityName;
    }

    public function setCharityName(string $charityName): self
    {
        $this->charityName = $charityName;
        return $this;
    }

    public function getCharityDescription(): ?string
    {
        return $this->charityDescription;
    }

    public function setCharityDescription(?string $charityDescription): self
    {
        $this->charityDescription = $charityDescription;
        return $this;
    }
}
