<?php

namespace Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity\{{ENTITY_NAME}};
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository\{{ENTITY_NAME_ONE}}TranslationsRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: {{ENTITY_NAME_ONE}}TranslationsRepository::class)]
#[ORM\Table(name: 'module_{{MODULE_NAMESPACE_LOWER}}_{{ENTITY_NAME_LOWER_ONLY}}_translations')]
class {{ENTITY_NAME_ONE}}Translations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: '{{ENTITY_NAME_ONE}}TranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: {{ENTITY_NAME}}::class)]
    #[ORM\JoinColumn(name: '{{ENTITY_NAME_ONE}}ID', referencedColumnName: '{{ENTITY_NAME_ONE}}ID', nullable: false)]
    private {{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}ID;

    #[ORM\Column(name: 'LanguageID', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "LanguageID is required.")]
    private int $languageID;

    #[ORM\Column(name: '{{ENTITY_NAME_ONE}}Name', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "{{ENTITY_NAME_ONE}}Name is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "{{ENTITY_NAME_ONE}}Name can contain only letters, numbers, underscores, and hyphens.")]
    private string ${{ENTITY_NAME_LOWER}}Name;

    #[ORM\Column(name: '{{ENTITY_NAME_ONE}}Description', type: 'text', nullable: true)]
    private ?string ${{ENTITY_NAME_LOWER}}Description = null;

    public function get{{ENTITY_NAME_ONE}}TranslationID(): ?int
{
    return $this->id;
}

    public function get{{ENTITY_NAME_ONE}}ID(): {{ENTITY_NAME}}
{
    return $this->{{ENTITY_NAME_LOWER}}ID;
}

    public function set{{ENTITY_NAME_ONE}}ID({{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}ID): self
{
    $this->{{ENTITY_NAME_LOWER}}ID = ${{ENTITY_NAME_LOWER}}ID;
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

    public function get{{ENTITY_NAME_ONE}}Name(): string
{
    return $this->{{ENTITY_NAME_LOWER}}Name;
}

    public function set{{ENTITY_NAME_ONE}}Name(string ${{ENTITY_NAME_LOWER}}Name): self
{
    $this->{{ENTITY_NAME_LOWER}}Name = ${{ENTITY_NAME_LOWER}}Name;
    return $this;
}

    public function get{{ENTITY_NAME_ONE}}Description(): ?string
{
    return $this->{{ENTITY_NAME_LOWER}}Description;
}

    public function set{{ENTITY_NAME_ONE}}Description(?string ${{ENTITY_NAME_LOWER}}Description): self
{
    $this->{{ENTITY_NAME_LOWER}}Description = ${{ENTITY_NAME_LOWER}}Description;
    return $this;
}
}
