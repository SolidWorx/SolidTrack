# Project & Client list at-a-glance stats — design

**Date:** 2026-05-28
**Status:** Approved (design); pending spec review

## Goal

Surface key time-tracking metrics directly on the Projects and Clients list
pages so the user gets a clean, modern, at-a-glance overview without opening
each project/client. Specifically:

- **Total time tracked** per project / per client.
- **Total earned** (billable tracked time × hourly rate) per project / per client.
- **Last activity** (when time was last tracked).

The lists are also visually modernised to fit SaaS standards, with a colored
totals footer and a period toggle.

## Decisions (locked)

| Decision        | Choice                                                              |
| --------------- | ------------------------------------------------------------------- |
| Layout          | Enhanced table (not card grid), with a totals footer                |
| Time window     | All-time by default, with a toggle: All time / This month / This year |
| Scope           | Current logged-in user only (consistent with dashboard & reports)   |
| Surfaced metrics| Total tracked, Earned, Last activity (no billable-% bar, no entry count) |
| Interactivity   | Live Components with a `period` LiveProp (`url: true`)               |

## Earnings & currency rules

- **Earned = billable hours × `project.hourlyRate`.** Non-billable time and
  time on projects with no `hourlyRate` contribute **0** to earnings.
- Currency comes from `client.currency` (a project inherits its client's
  currency), mirroring `DashboardStats`.
- The Projects list can span multiple client currencies, so the **footer
  groups earned totals by currency** (e.g. `$6,810 · €2,500`). Tracked time is
  currency-agnostic and sums to a single value.
- Each client has a single currency, so client rows are single-currency; the
  client footer uses the same per-currency grouping for consistency.
- A project/client with tracked time but no applicable rate shows `—` for
  Earned (muted), not `0`.

## Architecture

### Approach: Live Components (chosen over controller + query param)

Each list becomes a Live Component so toggling the period re-renders the table
in place (no full reload) and persists the selection in the URL. This matches
every other dynamic surface in the app (`DashboardStats`, `ReportSummary`,
`TopProjects`). The existing `index` controller actions shrink to a shell that
renders a template embedding the component. `new` / `edit` / `delete` actions
and routes are unchanged; the inline delete form keeps posting to the
controller and redirecting back (Turbo re-mounts the component afterwards).

### Aggregation (repository)

Two new methods on `TimeEntryRepository`, **scoped to the current user**, each a
single query with no full-entity hydration and no N+1:

```
aggregateByProjectForUser(User $user, ?DateTimeInterface $from, ?DateTimeInterface $to): array   // keyed by project id (rfc4122)
aggregateByClientForUser(User $user, ?DateTimeInterface $from, ?DateTimeInterface $to): array     // keyed by client id (rfc4122)
```

Each runs one DQL `SELECT` of scalar rows for `COMPLETED` entries:

```
SELECT IDENTITY(t.project) AS projectId,
       IDENTITY(p.client)  AS clientId,
       t.dateStart, t.dateEnd, t.billable,
       p.hourlyRate AS rate, c.currency AS currency
FROM TimeEntry t
JOIN t.project p
JOIN p.client  c
WHERE t.status = :completed
  AND t.user = :user
  [AND t.dateStart BETWEEN :from AND :to]
```

Rows are folded in PHP into per-key summaries. Duration is computed in PHP
(Carbon, from `dateStart`/`dateEnd`) to stay DB-agnostic — the same approach the
existing components already use. `dateEnd IS NULL` rows (active trackers) are
naturally excluded by the `COMPLETED` status filter.

Each summary value is a small readonly DTO `UsageSummary`:

- `totalDuration: CarbonInterval`
- `billableDuration: CarbonInterval`
- `amount: float` (earned, in the entity's currency)
- `currency: ?string`
- `lastActivity: ?CarbonImmutable` (max `dateStart`)

The component loads **all** projects / clients (ordered by name) so
zero-activity rows still render with `—`, then merges the aggregate map onto
them; missing keys yield an empty `UsageSummary`.

### Period enum

`App\Enum\StatsPeriod` mirrors the existing `App\Report\GroupBy` enum:

```
enum StatsPeriod: string {
    case AllTime = 'all';
    case Month   = 'month';
    case Year    = 'year';

    public function label(): string;                                   // "All time" / "This month" / "This year"
    public function range(CarbonImmutable $now): ?array;               // [from, to] or null for all-time
}
```

`range()` is driven by an injected `Psr\Clock\ClockInterface` so windows are
deterministic in tests.

## Presentation

### Projects table

Columns: `● Name | Client | Rate | Tracked | Earned | Last activity | Actions`.

- `●` is the existing project color dot.
- **Tracked** via `format_interval(summary.totalDuration, true)`.
- **Earned** via `format_currency` in the project's currency; `—` (muted) when
  no rate / zero.
- **Last activity** via Carbon `diffForHumans` (e.g. "2 days ago"); `—` when none.
- **Footer:** total tracked (single value) + earned grouped by currency.

### Clients table

Columns: `Name | Currency | Projects | Tracked | Earned | Last activity | Actions`.

- The existing **Projects** count column is kept (meaningful context; the spec
  cleans up styling rather than removing it).
- Tracked / Earned / Last activity / footer follow the same rules as projects.

### Period toggle

A compact segmented control (`All time · Month · Year`) in each page header,
bound to the component's `period` LiveProp. New `.st-segment` styling added to
`assets/styles/app.scss`. Empty / `—` states use `--st-text-muted`.

## Files

**New**

- `src/Enum/StatsPeriod.php`
- `src/Stats/UsageSummary.php` (readonly DTO)
- `src/Twig/Components/ProjectList.php` + `templates/components/ProjectList.html.twig`
- `src/Twig/Components/ClientList.php` + `templates/components/ClientList.html.twig`

**Modified**

- `src/Repository/TimeEntryRepository.php` — `aggregateByProjectForUser`, `aggregateByClientForUser` (+ shared private query helper)
- `templates/project/index.html.twig` — shrink to embed `<twig:ProjectList />` + period toggle
- `templates/client/index.html.twig` — shrink to embed `<twig:ClientList />` + period toggle
- `assets/styles/app.scss` — `.st-segment` toggle + table/footer polish
- `src/Controller/ProjectController.php` / `ClientController.php` — `index` becomes a shell render (other actions unchanged)

**No migration** — read-only feature.

## Testing & verification

- PHPUnit (transactional via `dama/doctrine-test-bundle`), `#[CoversClass]` on
  new test classes, using a fixed clock for deterministic period windows:
  - Repository: date-window boundaries (month/year edges), billable-only
    earnings, zero-rate → `—`/0, multi-currency grouping, current-user
    isolation (another user's entries excluded), zero-activity entity present.
  - Component smoke: renders with seeded data and toggling `period` changes totals.
- `vendor/bin/ecs check --fix` (mandatory file header) on all new files.
- `vendor/bin/phpstan analyse` clean.
- Manual: load `/projects` and `/clients`, verify numbers against a known
  dataset and that the toggle updates totals in place.

## Out of scope (YAGNI)

- Billable-% progress bar column (deliberately dropped per metric selection).
- Card-grid layout.
- Cross-user / team aggregation.
- Sorting/filtering the lists by the new metrics.
