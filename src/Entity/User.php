<?php

declare(strict_types=1);

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SolidWorx\Platform\PlatformBundle\Model\User as PlatformUser;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: self::TABLE_NAME)]
class User extends PlatformUser
{
    public const string TABLE_NAME = 'users';

    /**
     * @var Collection<int, TimeEntry>
     */
    #[ORM\OneToMany(targetEntity: TimeEntry::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $timeEntries;

    public function __construct()
    {
        parent::__construct();

        $this->timeEntries = new ArrayCollection();
    }

    /**
     * @return Collection<int, TimeEntry>
     */
    public function getTimeEntries(): Collection
    {
        return $this->timeEntries;
    }

    public function addTimeEntry(TimeEntry $timeEntry): static
    {
        if (! $this->timeEntries->contains($timeEntry)) {
            $this->timeEntries->add($timeEntry);
            $timeEntry->setUser($this);
        }

        return $this;
    }

    public function removeTimeEntry(TimeEntry $timeEntry): static
    {
        if ($this->timeEntries->removeElement($timeEntry) && $timeEntry->getUser() === $this) {
            $timeEntry->setUser(null);
        }

        return $this;
    }
}
