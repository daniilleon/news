<?php

namespace Module\Categories\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Categories\Repository\CategoryTranslationRepository;
use Module\Languages\Entity\Language;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryTranslationRepository::class)]
#[ORM\Table(name: 'module_category_translations')]
class CategoryTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CategoryTranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Categories::class)]
    #[ORM\JoinColumn(name: 'CategoryID', referencedColumnName: 'CategoryID', nullable: false)]
    private Categories $categoryID;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'LanguageID', referencedColumnName: 'LanguageID', nullable: false)]
    private Language $languageID;

    #[ORM\Column(name: 'CategoryName', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "CategoryName is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "CategoryName can contain only letters, numbers, underscores, and hyphens.")]
    private string $categoryName;

    #[ORM\Column(name: 'CategoryDescription', type: 'text', nullable: true)]
    private ?string $categoryDescription = null;

    public function getCategoryTranslationID(): ?int
    {
        return $this->id;
    }

    public function getCategoryID(): Categories
    {
        return $this->categoryID;
    }

    public function setCategoryID(Categories $categoryID): self
    {
        $this->categoryID = $categoryID;
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

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function setCategoryName(string $categoryName): self
    {
        $this->categoryName = $categoryName;
        return $this;
    }

    public function getCategoryDescription(): ?string
    {
        return $this->categoryDescription;
    }

    public function setCategoryDescription(?string $categoryDescription): self
    {
        $this->categoryDescription = $categoryDescription;
        return $this;
    }
}
