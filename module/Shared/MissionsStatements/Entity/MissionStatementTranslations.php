<?php

namespace Module\Shared\MissionsStatements\Entity;

use Doctrine\ORM\Mapping as ORM;
use Module\Shared\MissionsStatements\Entity\MissionsStatements;
use Module\Shared\MissionsStatements\Repository\MissionStatementTranslationsRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MissionStatementTranslationsRepository::class)]
#[ORM\Table(name: 'module_charities_missionstatement_translations')]
class MissionStatementTranslations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'MissionStatementTranslationID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MissionsStatements::class)]
    #[ORM\JoinColumn(name: 'MissionStatementID', referencedColumnName: 'MissionStatementID', nullable: false)]
    private MissionsStatements $missionStatementID;

    #[ORM\Column(name: 'LanguageID', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "LanguageID is required.")]
    private int $languageID;

    #[ORM\Column(name: 'MissionStatementName', type: 'string', length: 100)]
    #[Assert\NotBlank(message: "MissionStatementName is required.")]
    #[Assert\Regex("/^[\p{L}0-9_-]+$/u", message: "MissionStatementName can contain only letters, numbers, underscores, and hyphens.")]
    private string $missionStatementName;

    #[ORM\Column(name: 'MissionStatementDescription', type: 'text', nullable: true)]
    private ?string $missionStatementDescription = null;

    public function getMissionStatementTranslationID(): ?int
{
    return $this->id;
}

    public function getMissionStatementID(): MissionsStatements
{
    return $this->missionStatementID;
}

    public function setMissionStatementID(MissionsStatements $missionStatementID): self
{
    $this->missionStatementID = $missionStatementID;
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

    public function getMissionStatementName(): string
{
    return $this->missionStatementName;
}

    public function setMissionStatementName(string $missionStatementName): self
{
    $this->missionStatementName = $missionStatementName;
    return $this;
}

    public function getMissionStatementDescription(): ?string
{
    return $this->missionStatementDescription;
}

    public function setMissionStatementDescription(?string $missionStatementDescription): self
{
    $this->missionStatementDescription = $missionStatementDescription;
    return $this;
}
}
