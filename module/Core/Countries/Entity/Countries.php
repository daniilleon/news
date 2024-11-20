<?php

namespace Module\Core\Countries\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Core\Countries\Repository\CountriesRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: CountriesRepository::class)]
#[ORM\Table(name: 'module_countries')]
class Countries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CountryID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: 'CountryLink', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "CountryLink is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "CountryLink can contain only letters, numbers, underscores, and hyphens.")]
    private string $countryLink;

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

    public function getCountryID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    public function getCountryLink(): string
    {
        return $this->countryLink;
    }

    public function setCountryLink(string $countryLink): self
    {
        $this->countryLink = strtolower(trim($countryLink));
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
