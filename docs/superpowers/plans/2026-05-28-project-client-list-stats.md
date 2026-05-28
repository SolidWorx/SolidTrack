# Project & Client List At-a-Glance Stats Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show total tracked time, total earned, and last activity per project and per client on their list pages, with a period toggle (All time / This month / This year), styled to modern SaaS standards.

**Architecture:** Two new Live Components (`ProjectList`, `ClientList`) own a `period` LiveProp and render enhanced tables. A `StatsPeriod` enum maps the period to a date range via the injected clock. Two new `TimeEntryRepository` methods aggregate the current user's completed entries (single query each, folded in PHP) into `UsageSummary` value objects keyed by project/client id. The list controllers shrink to a shell that embeds the component.

**Tech Stack:** PHP 8.2+, Symfony 7.2, Symfony UX LiveComponent, Doctrine ORM, Carbon, Twig, Webpack Encore (SCSS), PHPUnit 11 + dama/doctrine-test-bundle.

---

## Background / conventions (read before starting)

- **Earned = billable hours × `project.hourlyRate`.** Non-billable time and projects with no rate contribute 0. Currency comes from `client.currency`.
- Completed entries only: `TimeEntryStatus::COMPLETED`. Active trackers have a null `dateEnd` and are excluded by that status filter anyway.
- `TimeEntry::getDuration()` returns a `CarbonInterval` (`diff(dateStart, dateEnd)`); `->totalHours` is fractional hours. Returns null if either date is null.
- Mandatory file header + `declare(strict_types=1);`: every new PHP file under `src/` and `tests/` must start with them. Always run `vendor/bin/ecs check --fix` on new files — it inserts the header automatically. The code blocks below include the header so the engineer knows it belongs there.
- LiveComponents in this app: class in `src/Twig/Components/`, `#[AsLiveComponent]`, `extends AbstractController`, `use DefaultActionTrait`, expose data with `#[ExposeInTemplate(name: '...')]`, bind inputs in the template with `data-model="..."`. Template root is `<div {{ attributes }}>`. Embed with `<twig:ProjectList />`.
- Twig helpers already available: `format_interval(CarbonInterval, true)` (human short), `amount|format_currency(currency)` (Intl), and Carbon's `.diffForHumans` callable on a `CarbonImmutable`.
- Tests use `WebTestCase`/`KernelTestCase`; dama wraps each test in a rolled-back transaction. New test classes need `#[CoversClass(...)]` (config sets `requireCoverageMetadata="true"`).

## File structure

**New**
- `src/Enum/StatsPeriod.php` — period enum + range logic.
- `src/Stats/UsageSummary.php` — readonly value object for an aggregated row.
- `src/Twig/Components/ProjectList.php` + `templates/components/ProjectList.html.twig`.
- `src/Twig/Components/ClientList.php` + `templates/components/ClientList.html.twig`.
- `tests/Enum/StatsPeriodTest.php`.
- `tests/Repository/TimeEntryAggregateTest.php`.
- `tests/Controller/ProjectListPageTest.php` and `tests/Controller/ClientListPageTest.php` (functional smoke).

**Modified**
- `src/Repository/TimeEntryRepository.php` — add `aggregateByProjectForUser`, `aggregateByClientForUser`, private `findCompletedForUserInPeriod`.
- `src/Controller/ProjectController.php` / `ClientController.php` — `index` becomes a shell render (other actions unchanged).
- `templates/project/index.html.twig` / `templates/client/index.html.twig` — embed the components.
- `assets/styles/app.scss` — `.st-segment` toggle styling.

---

## Task 1: `StatsPeriod` enum

**Files:**
- Create: `src/Enum/StatsPeriod.php`
- Test: `tests/Enum/StatsPeriodTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Enum/StatsPeriodTest.php`:

```php
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

namespace App\Test\Enum;

use App\Enum\StatsPeriod;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatsPeriod::class)]
final class StatsPeriodTest extends TestCase
{
    public function testAllTimeHasNoRange(): void
    {
        $now = CarbonImmutable::parse('2026-05-28 14:00:00');

        self::assertNull(StatsPeriod::AllTime->range($now));
    }

    public function testMonthRangeCoversCurrentCalendarMonth(): void
    {
        $now = CarbonImmutable::parse('2026-05-28 14:00:00');

        [$from, $to] = StatsPeriod::Month->range($now);

        self::assertSame('2026-05-01 00:00:00', $from->format('Y-m-d H:i:s'));
        self::assertSame('2026-05-31 23:59:59', $to->format('Y-m-d H:i:s'));
    }

    public function testYearRangeCoversCurrentCalendarYear(): void
    {
        $now = CarbonImmutable::parse('2026-05-28 14:00:00');

        [$from, $to] = StatsPeriod::Year->range($now);

        self::assertSame('2026-01-01 00:00:00', $from->format('Y-m-d H:i:s'));
        self::assertSame('2026-12-31 23:59:59', $to->format('Y-m-d H:i:s'));
    }

    public function testLabels(): void
    {
        self::assertSame('All time', StatsPeriod::AllTime->label());
        self::assertSame('This month', StatsPeriod::Month->label());
        self::assertSame('This year', StatsPeriod::Year->label());
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `vendor/bin/phpunit tests/Enum/StatsPeriodTest.php`
Expected: FAIL — `Class "App\Enum\StatsPeriod" not found`.

- [ ] **Step 3: Create the enum**

Create `src/Enum/StatsPeriod.php`:

```php
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

namespace App\Enum;

use Carbon\CarbonImmutable;

enum StatsPeriod: string
{
    case AllTime = 'all';
    case Month = 'month';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::AllTime => 'All time',
            self::Month => 'This month',
            self::Year => 'This year',
        };
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}|null Null means "no date filter" (all time).
     */
    public function range(CarbonImmutable $now): ?array
    {
        return match ($this) {
            self::AllTime => null,
            self::Month => [$now->startOfMonth(), $now->endOfMonth()],
            self::Year => [$now->startOfYear(), $now->endOfYear()],
        };
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `vendor/bin/phpunit tests/Enum/StatsPeriodTest.php`
Expected: PASS (4 tests).

- [ ] **Step 5: Style + commit**

```bash
vendor/bin/ecs check --fix src/Enum/StatsPeriod.php tests/Enum/StatsPeriodTest.php
git add src/Enum/StatsPeriod.php tests/Enum/StatsPeriodTest.php
git commit -m "feat: add StatsPeriod enum with date-range logic"
```

---

## Task 2: `UsageSummary` DTO + repository aggregation

**Files:**
- Create: `src/Stats/UsageSummary.php`
- Modify: `src/Repository/TimeEntryRepository.php`
- Test: `tests/Repository/TimeEntryAggregateTest.php`

- [ ] **Step 1: Create the `UsageSummary` value object**

Create `src/Stats/UsageSummary.php`:

```php
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

namespace App\Stats;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;

final readonly class UsageSummary
{
    public function __construct(
        public CarbonInterval $totalDuration,
        public CarbonInterval $billableDuration,
        public float $amount,
        public ?string $currency,
        public ?CarbonImmutable $lastActivity,
    ) {
    }

    public static function empty(?string $currency = null): self
    {
        return new self(
            CarbonInterval::seconds(0),
            CarbonInterval::seconds(0),
            0.0,
            $currency,
            null,
        );
    }

    public function hasActivity(): bool
    {
        return $this->lastActivity !== null;
    }
}
```

- [ ] **Step 2: Write the failing repository test**

Create `tests/Repository/TimeEntryAggregateTest.php`:

```php
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

namespace App\Test\Repository;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use App\Repository\TimeEntryRepository;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(TimeEntryRepository::class)]
final class TimeEntryAggregateTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    private TimeEntryRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->em->getRepository(TimeEntry::class);
    }

    public function testAggregateByProjectSumsTrackedTimeAndBillableEarnings(): void
    {
        $user = $this->createUser('owner@example.test');
        $client = $this->createClient('Acme', 'USD');
        $project = $this->createProject('Website', $client, 100.0);

        // 2h billable -> $200, 1h non-billable -> $0.
        $this->createEntry($user, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00', true);
        $this->createEntry($user, $project, '2026-05-11 09:00:00', '2026-05-11 10:00:00', false);
        $this->em->flush();

        $summaries = $this->repository->aggregateByProjectForUser($user, null, null);
        $key = $project->getId()->toRfc4122();

        self::assertArrayHasKey($key, $summaries);
        self::assertEqualsWithDelta(3.0, $summaries[$key]->totalDuration->totalHours, 0.001);
        self::assertEqualsWithDelta(2.0, $summaries[$key]->billableDuration->totalHours, 0.001);
        self::assertEqualsWithDelta(200.0, $summaries[$key]->amount, 0.001);
        self::assertSame('USD', $summaries[$key]->currency);
    }

    public function testProjectWithoutRateEarnsNothing(): void
    {
        $user = $this->createUser('owner@example.test');
        $client = $this->createClient('Acme', 'USD');
        $project = $this->createProject('Internal', $client, null);

        $this->createEntry($user, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00', true);
        $this->em->flush();

        $summaries = $this->repository->aggregateByProjectForUser($user, null, null);
        $key = $project->getId()->toRfc4122();

        self::assertEqualsWithDelta(0.0, $summaries[$key]->amount, 0.001);
        self::assertEqualsWithDelta(2.0, $summaries[$key]->totalDuration->totalHours, 0.001);
    }

    public function testRangeFilterExcludesEntriesOutsideWindow(): void
    {
        $user = $this->createUser('owner@example.test');
        $client = $this->createClient('Acme', 'USD');
        $project = $this->createProject('Website', $client, 100.0);

        $this->createEntry($user, $project, '2026-04-30 09:00:00', '2026-04-30 11:00:00', true); // April
        $this->createEntry($user, $project, '2026-05-10 09:00:00', '2026-05-10 12:00:00', true); // May
        $this->em->flush();

        $from = CarbonImmutable::parse('2026-05-01 00:00:00');
        $to = CarbonImmutable::parse('2026-05-31 23:59:59');

        $summaries = $this->repository->aggregateByProjectForUser($user, $from, $to);
        $key = $project->getId()->toRfc4122();

        self::assertEqualsWithDelta(3.0, $summaries[$key]->totalDuration->totalHours, 0.001);
        self::assertSame(
            '2026-05-10',
            $summaries[$key]->lastActivity->format('Y-m-d'),
        );
    }

    public function testAggregateIsScopedToTheGivenUser(): void
    {
        $owner = $this->createUser('owner@example.test');
        $other = $this->createUser('other@example.test');
        $client = $this->createClient('Acme', 'USD');
        $project = $this->createProject('Website', $client, 100.0);

        $this->createEntry($owner, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00', true);
        $this->createEntry($other, $project, '2026-05-10 09:00:00', '2026-05-10 15:00:00', true);
        $this->em->flush();

        $summaries = $this->repository->aggregateByProjectForUser($owner, null, null);
        $key = $project->getId()->toRfc4122();

        self::assertEqualsWithDelta(2.0, $summaries[$key]->totalDuration->totalHours, 0.001);
    }

    public function testAggregateByClientGroupsAcrossProjects(): void
    {
        $user = $this->createUser('owner@example.test');
        $client = $this->createClient('Acme', 'EUR');
        $projectA = $this->createProject('Site', $client, 100.0);
        $projectB = $this->createProject('App', $client, 50.0);

        $this->createEntry($user, $projectA, '2026-05-10 09:00:00', '2026-05-10 11:00:00', true); // 2h * 100 = 200
        $this->createEntry($user, $projectB, '2026-05-11 09:00:00', '2026-05-11 13:00:00', true); // 4h * 50 = 200
        $this->em->flush();

        $summaries = $this->repository->aggregateByClientForUser($user, null, null);
        $key = $client->getId()->toRfc4122();

        self::assertEqualsWithDelta(6.0, $summaries[$key]->totalDuration->totalHours, 0.001);
        self::assertEqualsWithDelta(400.0, $summaries[$key]->amount, 0.001);
        self::assertSame('EUR', $summaries[$key]->currency);
    }

    private function createUser(string $email): User
    {
        $user = new User();
        $user->setEmail($email)
            ->setEnabled(true)
            ->setVerified(true)
            ->setRoles(['ROLE_USER']);
        $user->setPassword('hashed');
        $this->em->persist($user);

        return $user;
    }

    private function createClient(string $name, string $currency): Client
    {
        $client = new Client();
        $client->setName($name)->setCurrency($currency);
        $this->em->persist($client);

        return $client;
    }

    private function createProject(string $name, Client $client, ?float $rate): Project
    {
        $project = new Project();
        $project->setName($name);
        $project->setClient($client);
        $project->setHourlyRate($rate);
        $this->em->persist($project);

        return $project;
    }

    private function createEntry(
        User $user,
        Project $project,
        string $start,
        string $end,
        bool $billable,
    ): TimeEntry {
        $entry = new TimeEntry();
        $entry->setUser($user)
            ->setProject($project)
            ->setDateStart(CarbonImmutable::parse($start))
            ->setDateEnd(CarbonImmutable::parse($end))
            ->setBillable($billable)
            ->setStatus(TimeEntryStatus::COMPLETED)
            ->setEntryType(TimeEntryType::MANUAL);
        $this->em->persist($entry);

        return $entry;
    }
}
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `vendor/bin/phpunit tests/Repository/TimeEntryAggregateTest.php`
Expected: FAIL — `Call to undefined method ...::aggregateByProjectForUser()`.

- [ ] **Step 4: Add the aggregation methods to the repository**

In `src/Repository/TimeEntryRepository.php`, add these imports to the existing `use` block (keep the existing ones):

```php
use App\Stats\UsageSummary;
use Carbon\CarbonImmutable;
```

(`Carbon\CarbonInterval`, `App\Entity\User`, `App\Entity\TimeEntry`, `App\Enum\TimeEntryStatus`, `DateTimeInterface`, and `UlidType` are already imported. `CarbonImmutable` is referenced in the new methods' docblock array shapes.)

Then add these three methods to the class (after `findCompletedTrackersForUserInRange`):

```php
    /**
     * @return array<string, UsageSummary> Keyed by project id (RFC 4122).
     */
    public function aggregateByProjectForUser(User $user, ?DateTimeInterface $from, ?DateTimeInterface $to): array
    {
        /** @var array<string, array{total: float, billable: float, amount: float, currency: ?string, last: ?CarbonImmutable}> $acc */
        $acc = [];

        foreach ($this->findCompletedForUserInPeriod($user, $from, $to) as $entry) {
            $project = $entry->getProject();
            $duration = $entry->getDuration();
            $key = $project?->getId()?->toRfc4122();
            if ($project === null || $duration === null || $key === null) {
                continue;
            }

            $this->fold(
                $acc,
                $key,
                $entry,
                $duration->totalHours,
                $project->getHourlyRate(),
                $project->getClient()?->getCurrency(),
            );
        }

        return $this->materialise($acc);
    }

    /**
     * @return array<string, UsageSummary> Keyed by client id (RFC 4122).
     */
    public function aggregateByClientForUser(User $user, ?DateTimeInterface $from, ?DateTimeInterface $to): array
    {
        /** @var array<string, array{total: float, billable: float, amount: float, currency: ?string, last: ?CarbonImmutable}> $acc */
        $acc = [];

        foreach ($this->findCompletedForUserInPeriod($user, $from, $to) as $entry) {
            $project = $entry->getProject();
            $duration = $entry->getDuration();
            $client = $project?->getClient();
            $key = $client?->getId()?->toRfc4122();
            if ($duration === null || $client === null || $key === null) {
                continue;
            }

            $this->fold(
                $acc,
                $key,
                $entry,
                $duration->totalHours,
                $project?->getHourlyRate(),
                $client->getCurrency(),
            );
        }

        return $this->materialise($acc);
    }

    /**
     * @param array<string, array{total: float, billable: float, amount: float, currency: ?string, last: ?CarbonImmutable}> $acc
     */
    private function fold(array &$acc, string $key, TimeEntry $entry, float $hours, ?float $rate, ?string $currency): void
    {
        $acc[$key] ??= ['total' => 0.0, 'billable' => 0.0, 'amount' => 0.0, 'currency' => $currency, 'last' => null];
        $acc[$key]['total'] += $hours;

        if ($entry->isBillable()) {
            $acc[$key]['billable'] += $hours;
            if ($rate !== null) {
                $acc[$key]['amount'] += $hours * $rate;
            }
        }

        $start = $entry->getDateStart();
        if ($start !== null && ($acc[$key]['last'] === null || $start->greaterThan($acc[$key]['last']))) {
            $acc[$key]['last'] = $start;
        }
    }

    /**
     * @param array<string, array{total: float, billable: float, amount: float, currency: ?string, last: ?CarbonImmutable}> $acc
     *
     * @return array<string, UsageSummary>
     */
    private function materialise(array $acc): array
    {
        return array_map(
            static fn (array $row): UsageSummary => new UsageSummary(
                CarbonInterval::hours($row['total']),
                CarbonInterval::hours($row['billable']),
                $row['amount'],
                $row['currency'],
                $row['last'],
            ),
            $acc,
        );
    }

    /**
     * @return list<TimeEntry>
     */
    private function findCompletedForUserInPeriod(User $user, ?DateTimeInterface $from, ?DateTimeInterface $to): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->addSelect('p')
            ->leftJoin('p.client', 'c')
            ->addSelect('c')
            ->where('t.status = :status')
            ->andWhere('t.user = :user')
            ->setParameter('status', TimeEntryStatus::COMPLETED)
            ->setParameter('user', $user->getId(), UlidType::NAME);

        if ($from !== null && $to !== null) {
            $qb->andWhere('t.dateStart BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->getQuery()->getResult();
    }
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `vendor/bin/phpunit tests/Repository/TimeEntryAggregateTest.php`
Expected: PASS (5 tests).

- [ ] **Step 6: Static analysis + style + commit**

```bash
vendor/bin/ecs check --fix src/Stats/UsageSummary.php src/Repository/TimeEntryRepository.php tests/Repository/TimeEntryAggregateTest.php
vendor/bin/phpstan analyse src/Stats/UsageSummary.php src/Repository/TimeEntryRepository.php
git add src/Stats/UsageSummary.php src/Repository/TimeEntryRepository.php tests/Repository/TimeEntryAggregateTest.php
git commit -m "feat: aggregate tracked time and earnings by project and client"
```

---

## Task 3: `ProjectList` Live Component

**Files:**
- Create: `src/Twig/Components/ProjectList.php`
- Create: `templates/components/ProjectList.html.twig`
- Modify: `src/Controller/ProjectController.php` (the `index` action only)
- Modify: `templates/project/index.html.twig`

- [ ] **Step 1: Create the component class**

Create `src/Twig/Components/ProjectList.php`:

```php
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

namespace App\Twig\Components;

use App\Entity\Project;
use App\Entity\User;
use App\Enum\StatsPeriod;
use App\Repository\ProjectRepository;
use App\Repository\TimeEntryRepository;
use App\Stats\UsageSummary;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use LogicException;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class ProjectList extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $period = 'all';

    /**
     * @var list<array{project: Project, summary: UsageSummary}>|null
     */
    private ?array $rows = null;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return list<array{project: Project, summary: UsageSummary}>
     */
    #[ExposeInTemplate(name: 'rows')]
    public function rows(): array
    {
        if ($this->rows !== null) {
            return $this->rows;
        }

        [$from, $to] = $this->resolvePeriod();
        $summaries = $this->timeEntryRepository->aggregateByProjectForUser($this->currentUser(), $from, $to);

        $rows = [];
        foreach ($this->projectRepository->findBy([], ['name' => 'ASC']) as $project) {
            $key = $project->getId()?->toRfc4122();
            $rows[] = [
                'project' => $project,
                'summary' => ($key !== null && isset($summaries[$key]))
                    ? $summaries[$key]
                    : UsageSummary::empty($project->getClient()?->getCurrency()),
            ];
        }

        return $this->rows = $rows;
    }

    /**
     * @return array{tracked: CarbonInterval, earnings: array<string, float>}
     */
    #[ExposeInTemplate(name: 'totals')]
    public function totals(): array
    {
        $hours = 0.0;
        $earnings = [];

        foreach ($this->rows() as $row) {
            $summary = $row['summary'];
            $hours += $summary->totalDuration->totalHours;
            if ($summary->amount > 0 && $summary->currency !== null) {
                $earnings[$summary->currency] = ($earnings[$summary->currency] ?? 0.0) + $summary->amount;
            }
        }

        return ['tracked' => CarbonInterval::hours($hours), 'earnings' => $earnings];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[ExposeInTemplate(name: 'periodOptions')]
    public function periodOptions(): array
    {
        return array_map(
            static fn (StatsPeriod $p): array => ['value' => $p->value, 'label' => $p->label()],
            StatsPeriod::cases(),
        );
    }

    /**
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function resolvePeriod(): array
    {
        $period = StatsPeriod::tryFrom($this->period) ?? StatsPeriod::AllTime;
        $range = $period->range(CarbonImmutable::instance($this->clock->now()));

        return $range ?? [null, null];
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('ProjectList requires an authenticated User.');
        }

        return $user;
    }
}
```

- [ ] **Step 2: Create the component template**

Create `templates/components/ProjectList.html.twig`:

```twig
<div {{ attributes }}>
    <div class="st-card">
        <div class="st-card-head">
            <h3 class="st-card-title">{{ 'All projects'|trans }}</h3>
            <div class="st-segment" role="group" aria-label="{{ 'Time window'|trans }}">
                {% for opt in periodOptions %}
                    <label class="{{ period == opt.value ? 'active' : '' }}">
                        <input type="radio" name="period" value="{{ opt.value }}" data-model="period" {{ period == opt.value ? 'checked' : '' }}>
                        {{ opt.label|trans }}
                    </label>
                {% endfor %}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>{{ 'Name'|trans }}</th>
                        <th>{{ 'Client'|trans }}</th>
                        <th class="text-end">{{ 'Rate'|trans }}</th>
                        <th class="text-end">{{ 'Tracked'|trans }}</th>
                        <th class="text-end">{{ 'Earned'|trans }}</th>
                        <th>{{ 'Last activity'|trans }}</th>
                        <th>{{ 'Actions'|trans }}</th>
                    </tr>
                </thead>
                <tbody>
                    {% for row in rows %}
                        {% set s = row.summary %}
                        <tr>
                            <td>
                                <span class="st-pdot align-middle me-2" style="background-color: {{ row.project.color }};" aria-label="{{ row.project.color }}"></span>
                                {{ row.project.name }}
                            </td>
                            <td>{{ row.project.client }}</td>
                            <td class="text-end">
                                {% if row.project.hourlyRate is not null %}
                                    {{ row.project.hourlyRate }} {{ row.project.client.currency }}
                                {% else %}
                                    <span class="text-muted">—</span>
                                {% endif %}
                            </td>
                            <td class="text-end">
                                {% if s.hasActivity %}
                                    <time>{{ format_interval(s.totalDuration, true) }}</time>
                                {% else %}
                                    <span class="text-muted">—</span>
                                {% endif %}
                            </td>
                            <td class="text-end">
                                {% if s.amount > 0 and s.currency %}
                                    {{ s.amount|format_currency(s.currency) }}
                                {% else %}
                                    <span class="text-muted">—</span>
                                {% endif %}
                            </td>
                            <td>
                                {% if s.lastActivity %}
                                    {{ s.lastActivity.diffForHumans }}
                                {% else %}
                                    <span class="text-muted">—</span>
                                {% endif %}
                            </td>
                            <td>
                                <a href="{{ path('app_project_edit', {'id': row.project.id}) }}">
                                    {{ ux_icon('tabler:pencil', {width: '16px', height: '16px'}) }}
                                    {{ 'Edit'|trans }}
                                </a>
                                {{ include('project/_delete_form.html.twig', {'project': row.project}) }}
                            </td>
                        </tr>
                    {% else %}
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding: 24px;">{{ 'No records found'|trans }}</td>
                        </tr>
                    {% endfor %}
                </tbody>
                {% if rows is not empty %}
                    <tfoot>
                        <tr style="font-weight: 600;">
                            <td colspan="3" class="text-end">{{ 'Totals'|trans }}</td>
                            <td class="text-end"><time>{{ format_interval(totals.tracked, true) }}</time></td>
                            <td class="text-end" colspan="3">
                                {% if totals.earnings is empty %}
                                    <span class="text-muted">—</span>
                                {% else %}
                                    {{ totals.earnings|map((amount, currency) => amount|format_currency(currency))|join(' · ') }}
                                {% endif %}
                            </td>
                        </tr>
                    </tfoot>
                {% endif %}
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 3: Shrink the controller `index` action**

In `src/Controller/ProjectController.php`, replace the `index` method body so it no longer needs `ProjectRepository`:

```php
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('project/index.html.twig');
    }
```

Leave the `ProjectRepository` import in place — it is still used by `new`, `edit`, and `delete`.

- [ ] **Step 4: Replace the index template content block**

In `templates/project/index.html.twig`, replace the entire `{% block content %} ... {% endblock %}` (the `table-responsive` markup) with:

```twig
{% block content %}
    <twig:ProjectList />
{% endblock %}
```

Leave the `{% block page_header %}` (title + "Create new project" button) exactly as it is.

- [ ] **Step 5: Build assets and verify the page renders**

Run: `bun run dev`
Then start the app if not running and load `/projects` in the browser (or run the functional test added in Task 5). Expected: the projects table renders with Tracked / Earned / Last activity columns, a totals footer, and an "All time / This month / This year" toggle that updates the numbers when clicked.

- [ ] **Step 6: Style + commit**

```bash
vendor/bin/ecs check --fix src/Twig/Components/ProjectList.php src/Controller/ProjectController.php
vendor/bin/phpstan analyse src/Twig/Components/ProjectList.php src/Controller/ProjectController.php
git add src/Twig/Components/ProjectList.php templates/components/ProjectList.html.twig src/Controller/ProjectController.php templates/project/index.html.twig
git commit -m "feat: enhanced projects list with tracked time, earnings and period toggle"
```

---

## Task 4: `ClientList` Live Component

**Files:**
- Create: `src/Twig/Components/ClientList.php`
- Create: `templates/components/ClientList.html.twig`
- Modify: `src/Controller/ClientController.php` (the `index` action only)
- Modify: `templates/client/index.html.twig`

- [ ] **Step 1: Create the component class**

Create `src/Twig/Components/ClientList.php`:

```php
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

namespace App\Twig\Components;

use App\Entity\Client;
use App\Entity\User;
use App\Enum\StatsPeriod;
use App\Repository\ClientRepository;
use App\Repository\TimeEntryRepository;
use App\Stats\UsageSummary;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use LogicException;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class ClientList extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $period = 'all';

    /**
     * @var list<array{client: Client, summary: UsageSummary}>|null
     */
    private ?array $rows = null;

    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return list<array{client: Client, summary: UsageSummary}>
     */
    #[ExposeInTemplate(name: 'rows')]
    public function rows(): array
    {
        if ($this->rows !== null) {
            return $this->rows;
        }

        [$from, $to] = $this->resolvePeriod();
        $summaries = $this->timeEntryRepository->aggregateByClientForUser($this->currentUser(), $from, $to);

        $rows = [];
        foreach ($this->clientRepository->findBy([], ['name' => 'ASC']) as $client) {
            $key = $client->getId()?->toRfc4122();
            $rows[] = [
                'client' => $client,
                'summary' => ($key !== null && isset($summaries[$key]))
                    ? $summaries[$key]
                    : UsageSummary::empty($client->getCurrency()),
            ];
        }

        return $this->rows = $rows;
    }

    /**
     * @return array{tracked: CarbonInterval, earnings: array<string, float>}
     */
    #[ExposeInTemplate(name: 'totals')]
    public function totals(): array
    {
        $hours = 0.0;
        $earnings = [];

        foreach ($this->rows() as $row) {
            $summary = $row['summary'];
            $hours += $summary->totalDuration->totalHours;
            if ($summary->amount > 0 && $summary->currency !== null) {
                $earnings[$summary->currency] = ($earnings[$summary->currency] ?? 0.0) + $summary->amount;
            }
        }

        return ['tracked' => CarbonInterval::hours($hours), 'earnings' => $earnings];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[ExposeInTemplate(name: 'periodOptions')]
    public function periodOptions(): array
    {
        return array_map(
            static fn (StatsPeriod $p): array => ['value' => $p->value, 'label' => $p->label()],
            StatsPeriod::cases(),
        );
    }

    /**
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function resolvePeriod(): array
    {
        $period = StatsPeriod::tryFrom($this->period) ?? StatsPeriod::AllTime;
        $range = $period->range(CarbonImmutable::instance($this->clock->now()));

        return $range ?? [null, null];
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('ClientList requires an authenticated User.');
        }

        return $user;
    }
}
```

- [ ] **Step 2: Create the component template**

Create `templates/components/ClientList.html.twig`:

```twig
<div {{ attributes }}>
    <div class="st-card">
        <div class="st-card-head">
            <h3 class="st-card-title">{{ 'All clients'|trans }}</h3>
            <div class="st-segment" role="group" aria-label="{{ 'Time window'|trans }}">
                {% for opt in periodOptions %}
                    <label class="{{ period == opt.value ? 'active' : '' }}">
                        <input type="radio" name="period" value="{{ opt.value }}" data-model="period" {{ period == opt.value ? 'checked' : '' }}>
                        {{ opt.label|trans }}
                    </label>
                {% endfor %}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>{{ 'Name'|trans }}</th>
                        <th>{{ 'Currency'|trans }}</th>
                        <th class="text-end">{{ 'Projects'|trans }}</th>
                        <th class="text-end">{{ 'Tracked'|trans }}</th>
                        <th class="text-end">{{ 'Earned'|trans }}</th>
                        <th>{{ 'Last activity'|trans }}</th>
                        <th>{{ 'Actions'|trans }}</th>
                    </tr>
                </thead>
                <tbody>
                    {% for row in rows %}
                        {% set s = row.summary %}
                        <tr>
                            <td>{{ row.client.name }}</td>
                            <td>{{ row.client.currency }}</td>
                            <td class="text-end">{{ row.client.projects|length }}</td>
                            <td class="text-end">
                                {% if s.hasActivity %}
                                    <time>{{ format_interval(s.totalDuration, true) }}</time>
                                {% else %}
                                    <span class="text-muted">—</span>
                                {% endif %}
                            </td>
                            <td class="text-end">
                                {% if s.amount > 0 and s.currency %}
                                    {{ s.amount|format_currency(s.currency) }}
                                {% else %}
                                    <span class="text-muted">—</span>
                                {% endif %}
                            </td>
                            <td>
                                {% if s.lastActivity %}
                                    {{ s.lastActivity.diffForHumans }}
                                {% else %}
                                    <span class="text-muted">—</span>
                                {% endif %}
                            </td>
                            <td>
                                <a href="{{ path('app_client_edit', {'id': row.client.id}) }}">
                                    {{ ux_icon('tabler:pencil', {width: '16px', height: '16px'}) }}
                                    {{ 'Edit'|trans }}
                                </a>
                                {{ include('client/_delete_form.html.twig', {'client': row.client}) }}
                            </td>
                        </tr>
                    {% else %}
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding: 24px;">{{ 'No records found'|trans }}</td>
                        </tr>
                    {% endfor %}
                </tbody>
                {% if rows is not empty %}
                    <tfoot>
                        <tr style="font-weight: 600;">
                            <td colspan="3" class="text-end">{{ 'Totals'|trans }}</td>
                            <td class="text-end"><time>{{ format_interval(totals.tracked, true) }}</time></td>
                            <td class="text-end" colspan="3">
                                {% if totals.earnings is empty %}
                                    <span class="text-muted">—</span>
                                {% else %}
                                    {{ totals.earnings|map((amount, currency) => amount|format_currency(currency))|join(' · ') }}
                                {% endif %}
                            </td>
                        </tr>
                    </tfoot>
                {% endif %}
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 3: Shrink the controller `index` action**

In `src/Controller/ClientController.php`, replace the `index` method body:

```php
    #[Route('/', name: 'app_client_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('client/index.html.twig');
    }
```

Leave the `ClientRepository` import — still used by `new`/`edit`/`delete`.

- [ ] **Step 4: Replace the index template content block**

In `templates/client/index.html.twig`, replace the entire `{% block content %} ... {% endblock %}` with:

```twig
{% block content %}
    <twig:ClientList />
{% endblock %}
```

Leave `{% block page_header %}` as-is.

- [ ] **Step 5: Build assets and verify**

Run: `bun run dev`
Load `/clients`. Expected: clients table with Projects count + Tracked / Earned / Last activity columns, totals footer, and the period toggle.

- [ ] **Step 6: Style + commit**

```bash
vendor/bin/ecs check --fix src/Twig/Components/ClientList.php src/Controller/ClientController.php
vendor/bin/phpstan analyse src/Twig/Components/ClientList.php src/Controller/ClientController.php
git add src/Twig/Components/ClientList.php templates/components/ClientList.html.twig src/Controller/ClientController.php templates/client/index.html.twig
git commit -m "feat: enhanced clients list with tracked time, earnings and period toggle"
```

---

## Task 5: Segmented-toggle styling + functional smoke tests

**Files:**
- Modify: `assets/styles/app.scss`
- Create: `tests/Controller/ProjectListPageTest.php`
- Create: `tests/Controller/ClientListPageTest.php`

- [ ] **Step 1: Add `.st-segment` styles**

Append to `assets/styles/app.scss` (after the `.st-card-link` block, near the other `st-*` rules):

```scss
// Segmented period toggle (All time / This month / This year)
.st-segment {
    display: inline-flex;
    background: var(--st-gray-100);
    border-radius: 8px;
    padding: 3px;
    gap: 2px;

    label {
        margin: 0;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        color: var(--st-text-muted);
        padding: 5px 12px;
        border-radius: 6px;
        line-height: 1;
        transition: background 0.12s ease, color 0.12s ease;

        input { display: none; }

        &:hover { color: var(--st-text-primary); }

        &.active {
            background: #fff;
            color: var(--st-primary);
            box-shadow: var(--st-shadow-sm);
        }
    }
}
```

- [ ] **Step 2: Build assets**

Run: `bun run dev`
Expected: build succeeds, no SCSS errors.

- [ ] **Step 3: Write the projects functional smoke test**

Create `tests/Controller/ProjectListPageTest.php`:

```php
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

namespace App\Test\Controller;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(\App\Twig\Components\ProjectList::class)]
final class ProjectListPageTest extends WebTestCase
{
    public function testListShowsProjectWithTrackedTime(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        $user = new User();
        $user->setEmail('owner@example.test')->setEnabled(true)->setVerified(true)->setRoles(['ROLE_USER']);
        $user->setPassword('hashed');
        $em->persist($user);

        $clientEntity = new Client();
        $clientEntity->setName('Acme')->setCurrency('USD');
        $em->persist($clientEntity);

        $project = new Project();
        $project->setName('Marketing Website');
        $project->setClient($clientEntity);
        $project->setHourlyRate(100.0);
        $em->persist($project);

        $entry = new TimeEntry();
        $entry->setUser($user)
            ->setProject($project)
            ->setDateStart(CarbonImmutable::parse('2026-05-10 09:00:00'))
            ->setDateEnd(CarbonImmutable::parse('2026-05-10 11:00:00'))
            ->setBillable(true)
            ->setStatus(TimeEntryStatus::COMPLETED)
            ->setEntryType(TimeEntryType::MANUAL);
        $em->persist($entry);
        $em->flush();

        $client->loginUser($user);
        $client->request('GET', '/projects/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Marketing Website');
        self::assertSelectorTextContains('body', 'All time');
    }
}
```

- [ ] **Step 4: Write the clients functional smoke test**

Create `tests/Controller/ClientListPageTest.php`:

```php
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

namespace App\Test\Controller;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(\App\Twig\Components\ClientList::class)]
final class ClientListPageTest extends WebTestCase
{
    public function testListShowsClient(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        $user = new User();
        $user->setEmail('owner@example.test')->setEnabled(true)->setVerified(true)->setRoles(['ROLE_USER']);
        $user->setPassword('hashed');
        $em->persist($user);

        $clientEntity = new Client();
        $clientEntity->setName('Globex Corp')->setCurrency('USD');
        $em->persist($clientEntity);
        $em->flush();

        $client->loginUser($user);
        $client->request('GET', '/clients/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Globex Corp');
        self::assertSelectorTextContains('body', 'Last activity');
    }
}
```

- [ ] **Step 5: Run the functional tests**

Run: `vendor/bin/phpunit tests/Controller/ProjectListPageTest.php tests/Controller/ClientListPageTest.php`
Expected: PASS (2 tests).

If a test fails because `/projects/` redirects to login, the firewall requires auth before `loginUser` takes effect for that path — confirm `security.yaml` access control and adjust the request to follow redirects or ensure `loginUser` is called before `request` (it is here). If it fails on missing `format_currency`, ensure `twig/intl-extra` is installed (it is — `DashboardStats` uses it).

- [ ] **Step 6: Full suite, static analysis, style, commit**

```bash
vendor/bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/ecs check --fix
git add assets/styles/app.scss tests/Controller/ProjectListPageTest.php tests/Controller/ClientListPageTest.php
git commit -m "feat: style period toggle and add list-page smoke tests"
```

---

## Final verification

- [ ] `vendor/bin/phpunit` — all green.
- [ ] `vendor/bin/phpstan analyse` — clean.
- [ ] `vendor/bin/ecs check` — clean (no `--fix` needed; should report no diffs).
- [ ] `bun run build` — production asset build succeeds.
- [ ] Manual: log in, open `/projects` and `/clients`. Confirm tracked time, earnings (per currency in the footer), and last activity are correct against known data, and that toggling All time / This month / This year updates the totals in place without a full page reload.

## Self-review notes (addressed)

- **Spec coverage:** enhanced table (Tasks 3–4), all-time + period toggle (Task 1 enum + LiveProp), current-user scope (`currentUser()` + repo `t.user` filter), metrics tracked/earned/last-activity (templates), multi-currency footer grouping (`totals()`), kept clients "Projects" count column, `.st-segment` styling (Task 5), no migration. All present.
- **Deviation from spec (intentional):** the repository folds hydrated entities from one joined query rather than scalar rows. This guarantees correct Ulid keying and date typing, matches the existing `findCompletedTrackersForUserInRange` pattern, and still avoids N+1 (single query). The spec's intent (one query, current-user scoped, PHP fold) is preserved.
- **Type consistency:** `UsageSummary` shape (`totalDuration`, `billableDuration`, `amount`, `currency`, `lastActivity`, `hasActivity()`) is identical across repository, both components, and templates. `aggregateByProjectForUser`/`aggregateByClientForUser` signatures match their call sites. `period` LiveProp is a string parsed via `StatsPeriod::tryFrom`.
```
