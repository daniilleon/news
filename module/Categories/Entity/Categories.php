<?php

namespace Module\Categories\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Categories\Repository\CategoriesRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
#[ORM\Table(name: 'module_categories')]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CategoryID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: 'CategoryLink', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "CategoryLink is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "CategoryLink can contain only letters, numbers, underscores, and hyphens.")]
    private string $categoryLink;

    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    public function getCategoryID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    public function getCategoryLink(): string
    {
        return $this->categoryLink;
    }

    public function setCategoryLink(string $categoryLink): self
    {
        $this->categoryLink = strtolower(trim($categoryLink));
        return $this;
    }
}
