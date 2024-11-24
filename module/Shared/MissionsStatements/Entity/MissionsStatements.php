<?php

namespace Module\Shared\MissionsStatements\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Shared\MissionsStatements\Repository\MissionsStatementsRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: MissionsStatementsRepository::class)]
#[ORM\Table(name: 'module_charities_missionstatement')]
class MissionsStatements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'MissionStatementID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CreatedDate', type: 'datetime')]
    private DateTime $createdDate;

    #[ORM\Column(name: 'MissionStatementCode', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "MissionStatementCode is required.")]
    #[Assert\Regex("/^[a-zA-Z0-9_-]+$/", message: "MissionStatementCode can contain only letters, numbers, underscores, and hyphens.")]
    private string $missionStatementCode;

    public function __construct()
{
    $this->createdDate = new DateTime();
}

    public function getMissionStatementID(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): DateTime
{
    return $this->createdDate;
}

    public function getMissionStatementCode(): string
    {
        return $this->missionStatementCode;
    }

    public function setMissionStatementCode(string $missionStatementCode): self
    {
        $this->missionStatementCode = trim($missionStatementCode);
        return $this;
    }
}
