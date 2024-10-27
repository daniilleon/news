<?php
namespace Module\Languages\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Languages\Repository\LanguageRepository;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
#[ORM\Table(name: 'module_languages')]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'LanguageID', type: 'integer')]
    private int $id;

    #[ORM\Column(name: 'LanguageCode', type: 'string', length: 10, unique: true)]
    private string $code;

    #[ORM\Column(name: 'LanguageName', type: 'string', length: 100)]
    private string $name;

    // Получение ID языка
    public function getId(): int
    {
        return $this->id;
    }

    // Получение кода языка
    public function getCode(): string
    {
        return $this->code;
    }

    // Установка кода языка
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    // Получение названия языка
    public function getName(): string
    {
        return $this->name;
    }

    // Установка названия языка
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}