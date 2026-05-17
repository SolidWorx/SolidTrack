<?php

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use App\Repository\TimeEntryRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: TimeEntryRepository::class)]
#[ORM\Table(name: self::TABLE_NAME)]
#[ApiResource]
class TimeEntry implements Stringable
{
    public const TABLE_NAME = 'time_entries';

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private Ulid $id;

    #[ORM\ManyToOne(inversedBy: 'timeEntries')]
    private ?Project $project = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?CarbonImmutable $dateStart = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?CarbonImmutable $dateEnd = null;

    #[ORM\Column]
    private bool $billable = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(enumType: TimeEntryStatus::class)]
    private ?TimeEntryStatus $status = null;

    #[ORM\Column(enumType: TimeEntryType::class)]
    private ?TimeEntryType $entryType = null;

    private ?CarbonInterval $duration = null;

    #[ORM\ManyToOne(inversedBy: 'timeEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getDateStart(): ?CarbonImmutable
    {
        return $this->dateStart;
    }

    public function setDateStart(CarbonImmutable $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?CarbonImmutable
    {
        return $this->dateEnd;
    }

    public function setDateEnd(CarbonImmutable $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function isBillable(): ?bool
    {
        return $this->billable;
    }

    public function setBillable(bool $billable): static
    {
        $this->billable = $billable;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function __toString(): string
    {
        return $this->description;
    }

    public function getStatus(): ?TimeEntryStatus
    {
        return $this->status;
    }

    public function setStatus(TimeEntryStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getEntryType(): ?TimeEntryType
    {
        return $this->entryType;
    }

    public function setEntryType(TimeEntryType $entryType): static
    {
        $this->entryType = $entryType;

        return $this;
    }

    public function getDuration(): ?CarbonInterval
    {
        if ($this->dateStart === null || $this->dateEnd === null) {
            return null;
        }

        return $this->duration ??= CarbonInterval::diff($this->dateStart, $this->dateEnd);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
