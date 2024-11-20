<?php

namespace Module\Shared\Industries\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Shared\Industries\Repository\IndustriesRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: IndustriesRepository::class)]
#[ORM\Table(name: 'module_industries')]
class Industries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'IndustryID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: 'IndustryLink', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "IndustryLink is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "IndustryLink can contain only letters, numbers, underscores, and hyphens.")]
    private string $industryLink;

    #[ORM\Column(name: 'og_image', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "The image path cannot be longer than {{ limit }} characters."
    )]
    private ?string $ogImage = null;

    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    public function getIndustryID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    public function getIndustryLink(): string
    {
        return $this->industryLink;
    }

    public function setIndustryLink(string $industryLink): self
    {
        $this->industryLink = strtolower(trim($industryLink));
        return $this;
    }


    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function setOgImage(?string $ogImage): self
    {
        $this->ogImage = $ogImage;
        return $this;
    }
}
