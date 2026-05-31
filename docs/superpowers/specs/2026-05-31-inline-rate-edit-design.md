# Inline Rate Edit — Design Spec

**Date:** 2026-05-31
**Status:** Approved

## Overview

Add inline editing for the project hourly rate using a reusable Symfony UX LiveComponent. The component is embedded in two places: the project list datagrid and the project detail/show page. Hovering reveals a pencil icon; clicking switches to an input that saves on blur or explicit confirm, and cancels on escape/cancel button.

---

## Component Structure

**File:** `src/Twig/Components/InlineRateEdit.php`

```php
#[AsLiveComponent]
final class InlineRateEdit extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp]
    public Project $project;

    #[LiveProp(writable: true)]
    public bool $editing = false;

    #[LiveProp(writable: true)]
    public string $rate = '';

    public function mount(): void        // seeds $rate from project->getHourlyRate()
    public function startEdit(): void    // #[LiveAction] — sets editing = true
    public function save(): void         // #[LiveAction] — validates, persists, editing = false
    public function cancel(): void       // #[LiveAction] — reverts rate, editing = false
}
```

**Validation** — a `RateData` DTO with `?float $rate` annotated `#[Assert\PositiveOrZero]` and `#[Assert\Type('float')]`. The `save()` action runs the Symfony Validator against this DTO. On failure it populates `$errors` (exposed to template); on success it calls `$projectRepository->save($project)`.

**Null support** — an empty string input submitted to `save()` clears `hourlyRate` to `null`.

**Currency** — resolved as `$project->getClient()?->getCurrency() ?? 'USD'` for display formatting.

---

## Template

**File:** `templates/components/InlineRateEdit.html.twig`

Root element: `<span {{ attributes }}>` (inline, fits table cells and subtitle areas).

### Read mode (`editing = false`)

- Wrapper `<span class="st-inline-edit-trigger">`
- Displays `format_currency(rate, currency) ~ ' / hour'` when rate is set; `'No rate set'` in muted text when null
- Pencil icon (`tabler:pencil`) beside the value, hidden by default, revealed on hover via CSS (`opacity: 0` → `opacity: 1` on `.st-inline-edit-trigger:hover`)
- Clicking the value or pencil fires the `startEdit` LiveAction

### Edit mode (`editing = true`)

- `<input type="number" min="0" step="0.01" data-model="rate">` with a fixed `min-width` so it doesn't collapse in a table cell
- Confirm button (✓) wired to `save` LiveAction
- Cancel button (✗) wired to `cancel` LiveAction
- `data-action="blur->live#action" data-live-action-param="save"` on the input for save-on-blur
- On validation failure: input gets `is-invalid` class; `<div class="invalid-feedback d-block">` shows the first error message beneath the input

**CSS** — two utility rules (hover-reveal pencil + input min-width) added to a new `assets/styles/inline-edit.css` Encore entry or inlined in the component template's `<style>` block.

---

## Integration Points

### Project list — `templates/components/ProjectList.html.twig`

Replace the static rate `<td>` cell:

```twig
{# before #}
<td class="text-end">
    {% if row.project.hourlyRate is not null %}
        {{ row.project.hourlyRate }} {{ row.project.client ? row.project.client.currency : '' }}
    {% else %}
        <span class="text-muted">—</span>
    {% endif %}
</td>

{# after #}
<td class="text-end">
    <twig:InlineRateEdit :project="row.project" />
</td>
```

### Project show page — `templates/project/show.html.twig`

Replace the static rate span in the page-header subtitle:

```twig
{# before #}
<span>
    {% if project.hourlyRate is not null %}
        {{ project.hourlyRate|format_currency(project.client ? project.client.currency : 'USD') }} / {{ 'hour'|trans }}
    {% else %}
        {{ 'No rate set'|trans }}
    {% endif %}
</span>

{# after #}
<twig:InlineRateEdit :project="project" />
```

No controller changes required — both locations already have the `Project` object in scope.

---

## Tests

**File:** `tests/Twig/Components/InlineRateEditTest.php`

`#[CoversClass(InlineRateEdit::class)]` — covers:

1. Initial render with a rate set (displays formatted currency)
2. Initial render with no rate (displays "No rate set")
3. `startEdit` sets `editing = true` and pre-fills `rate`
4. `save` with a valid positive float — persists and returns to read mode
5. `save` with empty string — clears `hourlyRate` to `null` and returns to read mode
6. `save` with an invalid value (letters / negative) — shows validation error, does not persist, stays in edit mode
7. `cancel` — reverts `rate` to original value and sets `editing = false`

---

## File Summary

| Action | Path |
|--------|------|
| Create | `src/Twig/Components/InlineRateEdit.php` |
| Create | `templates/components/InlineRateEdit.html.twig` |
| Create | `tests/Twig/Components/InlineRateEditTest.php` |
| Modify | `templates/components/ProjectList.html.twig` |
| Modify | `templates/project/show.html.twig` |
| Modify | `assets/styles/app.css` (or new `inline-edit.css`) |
