<?php

namespace Module\Shared\Charities\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Shared\Charities\Repository\CharitiesRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: CharitiesRepository::class)]
#[ORM\Table(name: 'module_charities')]
class Charities
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CharityID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: 'CharityLink', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "CharityLink is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "CharityLink can contain only letters, numbers, underscores, and hyphens.")]
    private string $charityLink;

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

    public function getCharityID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    public function getCharityLink(): string
    {
        return $this->charityLink;
    }

    public function setCharityLink(string $charityLink): self
    {
        $this->charityLink = strtolower(trim($charityLink));
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
