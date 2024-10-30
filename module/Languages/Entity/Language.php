<?php
namespace Module\Languages\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Languages\Repository\LanguagesRepository;

#[ORM\Entity(repositoryClass: LanguagesRepository::class)]
#[ORM\Table(name: 'module_languages')]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'LanguageID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'LanguageCode', type: 'string', length: 10, unique: true)]
    private string $LanguageCode;

    #[ORM\Column(name: 'LanguageName', type: 'string', length: 100)]
    private string $languageName;

    // Получение ID языка
    public function getLanguageID(): ?int
    {
        return $this->id;
    }

    // Получение кода языка
    public function getLanguageCode(): string
    {
        return $this->LanguageCode;
    }

    // Установка кода языка
    public function setLanguageCode(string $LanguageCode): self
    {
        $this->LanguageCode = $LanguageCode;
        return $this;
    }

    // Получение названия языка
    public function getLanguageName(): string
    {
        return $this->languageName;
    }

    // Установка названия языка
    public function setLanguageName(string $languageName): self
    {
        $this->languageName = $languageName;
        return $this;
    }
}