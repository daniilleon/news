<?php

namespace Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository\{{ENTITY_NAME}}Repository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: {{ENTITY_NAME}}Repository::class)]
#[ORM\Table(name: 'module_{{MODULE_NAMESPACE_LOWER}}_{{ENTITY_NAME_LOWER_ONLY}}')]
class {{ENTITY_NAME}}
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: '{{ENTITY_NAME_ONE}}ID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: '{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}} is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}} can contain only letters, numbers, underscores, and hyphens.")]
    private string ${{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}};

    public function __construct()
{
    $this->createdDate = new DateTime();
}

    public function get{{ENTITY_NAME_ONE}}ID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
{
    return $this->createdDate;
}

    public function get{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(): string
    {
        return $this->{{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}};
    }

    public function set{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(string ${{ENTITY_NAME_LOWER}}Code): self
    {
        $this->{{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}} = trim(${{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}});
        return $this;
    }
}
