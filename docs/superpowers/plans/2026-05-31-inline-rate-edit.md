# Inline Rate Edit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a reusable `InlineRateEdit` LiveComponent that lets users edit a project's hourly rate inline (hover to reveal pencil, click to edit, blur to save, with Symfony Form validation) embedded in both the project list table and the project show page.

**Architecture:** A single `#[AsLiveComponent]` class (`InlineRateEdit`) owns the full edit lifecycle: read mode shows a formatted rate with a hover pencil, edit mode shows an `<input>` that saves on blur or cancel on button click. A dedicated `InlineRateType` Symfony Form type carries the validation constraint (`GreaterThanOrEqual(0)`). The component is embedded with `<twig:InlineRateEdit :project="…" />` — one line at each call site.

**Tech Stack:** Symfony UX LiveComponent ^2.18, Symfony Form + Validator, Doctrine ORM via `ProjectRepository::save()`, Twig + Tabler/Bootstrap 5 CSS, PHPUnit 11 + `InteractsWithLiveComponents` trait.

---

## File Map

| Action | Path | Responsibility |
|--------|------|---------------|
| Create | `src/Form/InlineRateType.php` | Symfony Form: single `hourlyRate` NumberType field with `GreaterThanOrEqual(0)` constraint |
| Create | `src/Twig/Components/InlineRateEdit.php` | LiveComponent: read/edit state, form validation, persistence |
| Create | `templates/components/InlineRateEdit.html.twig` | Read mode (hover pencil) + edit mode (input + cancel) |
| Create | `tests/Twig/Components/InlineRateEditTest.php` | 7 component tests via `InteractsWithLiveComponents` |
| Modify | `assets/styles/app.scss` | `.st-inline-edit-*` CSS for hover-reveal pencil + input sizing |
| Modify | `templates/components/ProjectList.html.twig` | Swap rate `<td>` to `<twig:InlineRateEdit>` |
| Modify | `templates/project/show.html.twig` | Swap rate `<span>` to `<twig:InlineRateEdit>` |

---

## Task 1: Add CSS for inline-edit

**Files:**
- Modify: `assets/styles/app.scss`

No tests needed — CSS only.

- [ ] **Step 1: Add the `.st-inline-edit-*` rules to `assets/styles/app.scss`**

Append the following block at the end of the file (before the final newline):

```scss
// ─────────────────────────────────────────────────────────────
// Inline edit — hover-reveal pencil, compact input
// ─────────────────────────────────────────────────────────────

.st-inline-edit-trigger {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;

    .st-inline-edit-pencil {
        opacity: 0;
        transition: opacity 120ms;
        color: var(--st-text-muted);
        flex-shrink: 0;
    }

    &:hover .st-inline-edit-pencil {
        opacity: 1;
    }
}

.st-inline-edit-field {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.st-inline-edit-input {
    min-width: 90px;
    width: 110px;
}
```

- [ ] **Step 2: Commit**

```bash
git add assets/styles/app.scss
git commit -m "style: add inline-edit CSS for hover-reveal pencil and input"
```

---

## Task 2: Create the InlineRateType form

**Files:**
- Create: `src/Form/InlineRateType.php`

- [ ] **Step 1: Create the form type**

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

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

final class InlineRateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('hourlyRate', NumberType::class, [
            'required' => false,
            'scale' => 2,
            'constraints' => [
                new GreaterThanOrEqual(value: 0, message: 'Rate must be 0 or greater.'),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => false,
        ]);
    }
}
```

- [ ] **Step 2: Run ECS to ensure the file header and style are correct**

```bash
vendor/bin/ecs check src/Form/InlineRateType.php --fix
```

Expected: no errors (or auto-fixed), file looks clean.

- [ ] **Step 3: Commit**

```bash
git add src/Form/InlineRateType.php
git commit -m "feat: add InlineRateType form with GreaterThanOrEqual validation"
```

---

## Task 3: Create the InlineRateEdit LiveComponent PHP class

**Files:**
- Create: `src/Twig/Components/InlineRateEdit.php`

- [ ] **Step 1: Create the component class**

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
use App\Form\InlineRateType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

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

    /**
     * @var list<string>
     */
    #[LiveProp(writable: true)]
    public array $formErrors = [];

    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function mount(): void
    {
        $hourlyRate = $this->project->getHourlyRate();
        $this->rate = $hourlyRate !== null ? (string) $hourlyRate : '';
    }

    #[LiveAction]
    public function startEdit(): void
    {
        $this->editing = true;
        $this->formErrors = [];
    }

    #[LiveAction]
    public function save(): void
    {
        $form = $this->createForm(InlineRateType::class);
        $form->submit(['hourlyRate' => $this->rate !== '' ? $this->rate : null]);

        if (! $form->isValid()) {
            $this->formErrors = array_values(array_map(
                static fn (FormError $e): string => $e->getMessage(),
                iterator_to_array($form->get('hourlyRate')->getErrors()),
            ));

            return;
        }

        /** @var array{hourlyRate: float|null} $data */
        $data = $form->getData();
        $this->project->setHourlyRate($data['hourlyRate']);
        $this->projectRepository->save($this->project);

        $hourlyRate = $this->project->getHourlyRate();
        $this->rate = $hourlyRate !== null ? (string) $hourlyRate : '';
        $this->editing = false;
        $this->formErrors = [];
    }

    #[LiveAction]
    public function cancel(): void
    {
        $hourlyRate = $this->project->getHourlyRate();
        $this->rate = $hourlyRate !== null ? (string) $hourlyRate : '';
        $this->editing = false;
        $this->formErrors = [];
    }
}
```

- [ ] **Step 2: Run ECS**

```bash
vendor/bin/ecs check src/Twig/Components/InlineRateEdit.php --fix
```

Expected: no errors.

- [ ] **Step 3: Run PHPStan to verify types**

```bash
vendor/bin/phpstan analyse src/Twig/Components/InlineRateEdit.php
```

Expected: no errors.

- [ ] **Step 4: Commit**

```bash
git add src/Twig/Components/InlineRateEdit.php
git commit -m "feat: add InlineRateEdit LiveComponent with save/cancel/validation"
```

---

## Task 4: Create the InlineRateEdit template

**Files:**
- Create: `templates/components/InlineRateEdit.html.twig`

- [ ] **Step 1: Create the template**

```twig
<span {{ attributes }}>
    {% if editing %}
        <span class="st-inline-edit-field">
            <input
                type="number"
                min="0"
                step="0.01"
                class="form-control form-control-sm st-inline-edit-input{% if formErrors is not empty %} is-invalid{% endif %}"
                data-model="norender|rate"
                value="{{ rate }}"
                data-action="blur->live#action"
                data-live-action-param="save"
                autofocus
            >
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                data-action="mousedown.prevent live#action"
                data-live-action-param="cancel"
                title="{{ 'Cancel'|trans }}"
            >✕</button>
            {% if formErrors is not empty %}
                <div class="invalid-feedback d-block">{{ formErrors[0] }}</div>
            {% endif %}
        </span>
    {% else %}
        <span
            class="st-inline-edit-trigger"
            data-action="click->live#action"
            data-live-action-param="startEdit"
            title="{{ 'Click to edit rate'|trans }}"
        >
            {% if project.hourlyRate is not null %}
                {{ project.hourlyRate|format_currency(project.client ? project.client.currency : 'USD') }}
                <span class="text-muted fw-normal"> / {{ 'hour'|trans }}</span>
            {% else %}
                <span class="text-muted">{{ 'No rate set'|trans }}</span>
            {% endif %}
            {{ ux_icon('tabler:pencil', {class: 'st-inline-edit-pencil', width: '13px', height: '13px'}) }}
        </span>
    {% endif %}
</span>
```

> **Note on blur/cancel interaction:** `mousedown.prevent` on the cancel button prevents the input from losing focus (which would fire `blur→save`) before the cancel action fires. This ensures clicking cancel truly cancels without triggering a save first.

- [ ] **Step 2: Commit**

```bash
git add templates/components/InlineRateEdit.html.twig
git commit -m "feat: add InlineRateEdit component template with read/edit modes"
```

---

## Task 5: Write component tests

**Files:**
- Create: `tests/Twig/Components/InlineRateEditTest.php`

- [ ] **Step 1: Write the full test file**

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

namespace App\Test\Twig\Components;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\User;
use App\Twig\Components\InlineRateEdit;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(InlineRateEdit::class)]
final class InlineRateEditTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    private EntityManagerInterface $em;

    private User $user;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        \assert($this->em instanceof EntityManagerInterface);

        $this->user = new User();
        $this->user->setEmail('rate-test@example.test')
            ->setEnabled(true)
            ->setVerified(true)
            ->setRoles(['ROLE_USER'])
            ->setPassword('hashed');
        $this->em->persist($this->user);
        $this->em->flush();
    }

    private function makeProject(?float $rate = null, string $currency = 'USD'): Project
    {
        $client = new Client();
        $client->setName('Acme')->setCurrency($currency);
        $this->em->persist($client);

        $project = new Project();
        $project->setName('Test Project')->setClient($client);
        if ($rate !== null) {
            $project->setHourlyRate($rate);
        }
        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function testRendersFormattedRateInReadMode(): void
    {
        $project = $this->makeProject(75.0, 'USD');

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component->render()->toString();

        self::assertStringContainsString('$75.00', $html);
        self::assertStringContainsString('/ hour', $html);
        self::assertStringNotContainsString('<input', $html);
    }

    public function testRendersNoRateMessageWhenRateIsNull(): void
    {
        $project = $this->makeProject(null);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component->render()->toString();

        self::assertStringContainsString('No rate set', $html);
        self::assertStringNotContainsString('<input', $html);
    }

    public function testStartEditSwitchesToEditMode(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component->call('startEdit')->render()->toString();

        self::assertStringContainsString('<input', $html);
        self::assertStringContainsString('50', $html);
    }

    public function testSaveValidRatePersistsAndReturnsToReadMode(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component
            ->call('startEdit')
            ->set('rate', '120')
            ->call('save')
            ->render()
            ->toString();

        self::assertStringContainsString('$120.00', $html);
        self::assertStringNotContainsString('<input', $html);

        $this->em->refresh($project);
        self::assertSame(120.0, $project->getHourlyRate());
    }

    public function testSaveEmptyRateClearsToNull(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component
            ->call('startEdit')
            ->set('rate', '')
            ->call('save')
            ->render()
            ->toString();

        self::assertStringContainsString('No rate set', $html);
        self::assertStringNotContainsString('<input', $html);

        $this->em->refresh($project);
        self::assertNull($project->getHourlyRate());
    }

    public function testSaveInvalidRateShowsErrorAndDoesNotPersist(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component
            ->call('startEdit')
            ->set('rate', '-10')
            ->call('save')
            ->render()
            ->toString();

        self::assertStringContainsString('<input', $html);
        self::assertStringContainsString('Rate must be 0 or greater', $html);

        $this->em->refresh($project);
        self::assertSame(50.0, $project->getHourlyRate());
    }

    public function testCancelRevertsRateAndReturnsToReadMode(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component
            ->call('startEdit')
            ->set('rate', '999')
            ->call('cancel')
            ->render()
            ->toString();

        self::assertStringContainsString('$50.00', $html);
        self::assertStringNotContainsString('<input', $html);

        $this->em->refresh($project);
        self::assertSame(50.0, $project->getHourlyRate());
    }
}
```

- [ ] **Step 2: Run ECS on the test file**

```bash
vendor/bin/ecs check tests/Twig/Components/InlineRateEditTest.php --fix
```

- [ ] **Step 3: Run the tests and verify they pass**

```bash
vendor/bin/phpunit tests/Twig/Components/InlineRateEditTest.php --testdox
```

Expected: all 7 tests pass.

If any test fails, debug before continuing. Common issues:
- `format_currency` output format: assert on `75.00` instead of `$75.00` if locale differs
- CSRF: the live component routes may require auth — `actingAs()` is already included
- Entity hydration: the component hydrator resolves the `Project` entity by ID, so it must be persisted before passing

- [ ] **Step 4: Run PHPStan on the test file**

```bash
vendor/bin/phpstan analyse tests/Twig/Components/InlineRateEditTest.php
```

Expected: no errors.

- [ ] **Step 5: Commit**

```bash
git add tests/Twig/Components/InlineRateEditTest.php
git commit -m "test: add InlineRateEdit component tests covering all 7 scenarios"
```

---

## Task 6: Integrate InlineRateEdit into the project list

**Files:**
- Modify: `templates/components/ProjectList.html.twig`

- [ ] **Step 1: Replace the static rate cell**

In `templates/components/ProjectList.html.twig`, find:

```twig
                            <td class="text-end">
                                {% if row.project.hourlyRate is not null %}
                                    {{ row.project.hourlyRate }} {{ row.project.client ? row.project.client.currency : '' }}
                                {% else %}
                                    <span class="text-muted">—</span>
                                {% endif %}
                            </td>
```

Replace with:

```twig
                            <td class="text-end">
                                <twig:InlineRateEdit :project="row.project" />
                            </td>
```

- [ ] **Step 2: Run the existing project list test**

```bash
vendor/bin/phpunit tests/Controller/ProjectListPageTest.php --testdox
```

Expected: passes.

- [ ] **Step 3: Commit**

```bash
git add templates/components/ProjectList.html.twig
git commit -m "feat: embed InlineRateEdit in project list rate cell"
```

---

## Task 7: Integrate InlineRateEdit into the project show page

**Files:**
- Modify: `templates/project/show.html.twig`

- [ ] **Step 1: Replace the static rate span**

In `templates/project/show.html.twig`, find:

```twig
                        <span>
                            {% if project.hourlyRate is not null %}
                                {{ project.hourlyRate|format_currency(project.client ? project.client.currency : 'USD') }} / {{ 'hour'|trans }}
                            {% else %}
                                {{ 'No rate set'|trans }}
                            {% endif %}
                        </span>
```

Replace with:

```twig
                        <twig:InlineRateEdit :project="project" />
```

- [ ] **Step 2: Run the existing project show test**

```bash
vendor/bin/phpunit tests/Controller/ProjectShowPageTest.php --testdox
```

Expected: passes.

- [ ] **Step 3: Run the full test suite to check for regressions**

```bash
vendor/bin/phpunit --testdox
```

Expected: all tests pass.

- [ ] **Step 4: Run PHPStan on all new/modified PHP files**

```bash
vendor/bin/phpstan analyse src/Form/InlineRateType.php src/Twig/Components/InlineRateEdit.php
```

Expected: no errors.

- [ ] **Step 5: Commit**

```bash
git add templates/project/show.html.twig
git commit -m "feat: embed InlineRateEdit in project show page rate display"
```
