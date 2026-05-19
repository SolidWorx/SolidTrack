# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

SolidTrack is a Symfony 7.2 / PHP 8.2+ time-tracking app built on top of the **SolidWorx Platform** monorepo at `../platform`. The platform supplies authentication, a User model, console scaffolding, base controllers, base repositories, Doctrine types, UI templates (Tabler/Bootstrap 5), menu wiring, and a unified `platform.yaml` configuration system. SolidTrack should consume those building blocks rather than reimplement them.

Frontend is Webpack Encore + Stimulus + Symfony UX (Turbo, LiveComponent, Chart.js). API surface is API Platform 3. Persistence is Doctrine ORM with migrations.

## Common commands

Backend (PHP / Symfony):

- `composer install` — install PHP deps (resolves `solidworx/platform` from `../platform`)
- `bin/console` — Symfony console
- `bin/console doctrine:migrations:migrate` — apply migrations
- `bin/console platform:generate-schema` — regenerate `platform-schema.json` for IDE autocomplete on `platform.yaml`
- `bin/console app:create-user <email> <password>` — create an admin user
- `vendor/bin/phpunit [--filter|<file>]` — run tests
- `vendor/bin/phpstan analyse` — static analysis
- `vendor/bin/ecs check [--fix]` — code style (ECS) — required for the standard file header
- `vendor/bin/rector process [--dry-run]` — automated refactors

Frontend (Webpack Encore via Bun):

- `bun install` — install JS deps
- `bun run dev` / `bun run watch` / `bun run dev-server` / `bun run build`

Bun is the package manager / script runner only — the actual bundler is **Webpack Encore** (see `webpack.config.js`) and tests run under **PHPUnit**. Do not substitute `bun build` or `bun test`. The global Bun-first guidance in the user's profile does not apply here.

## Platform integration — load-bearing rules

**Always reuse platform building blocks** instead of re-rolling local equivalents. Specifically:

- **`platform.yaml`** at the repo root is the unified config. It is parsed by `SolidWorx\Platform\PlatformBundle\Kernel` (which `App\Kernel` extends) and fans out config into the `platform:`, `ui:`, and (when used) `saas:` sections. Schema lives in `platform-schema.json` (regenerate with `platform:generate-schema`).
- **`config/bundles.php`** must NOT list `SolidWorxPlatformBundle`, `TwigExtraBundle`, `KnpMenuBundle`, or `UXIconsBundle` — the platform `Kernel::registerBundles()` yields them automatically (deduped by name in `initializeBundles`). The bundle file at the top of `bundles.php` documents this.
- **User model**: `App\Entity\User` extends `SolidWorx\Platform\PlatformBundle\Model\User` (abstract; provides id/email/password/roles/firstName/lastName/enabled/verified/lastLogin/mobile/googleId, plus 2FA trait). The concrete class only adds `#[ORM\Entity]`, table name, and app-specific relations (e.g. `timeEntries`). The model is declared as `platform.models.user` in `platform.yaml` so the `platform_user` security provider resolves to it.
- **Repositories**: extend `SolidWorx\Platform\PlatformBundle\Repository\EntityRepository` (provides `save()`/`remove()` with entity-class guard). `UserRepository` extends the platform's `UserRepository` which already implements `UserLoaderInterface` and `loadUserByIdentifier` (queries `enabled = true`). Controllers should call `$repo->save($entity)` / `$repo->remove($entity)` rather than injecting `EntityManagerInterface`.
- **Controllers**: extend `SolidWorx\Platform\PlatformBundle\Controller\BaseController` (overrides `redirect()` to return the platform's `RedirectResponse`). Invokable controllers without `redirect`/`render` needs (e.g. pure `#[Template]` ones) can stay POPO.
- **Commands**: extend `SolidWorx\Platform\PlatformBundle\Console\Command`. The base finalises `run()`/`execute()` — implement `handle(): int` instead. A `ConsoleCommandEventSubscriber` injects `$this->io` (a `Console\IO` extending `SymfonyStyle` with `getOption()`/`getArgument()`). Do not override `execute()`.
- **Security/login**: the platform ships `Controller\Security\Login` and a `LoginPageRouteLoader` (imported by the platform Kernel) that auto-generates the login route at the firewall's `form_login.login_path` with route name `_login_<firewall>`. The login view is whatever `ui.templates.login` points to in `platform.yaml` (defaults to `@Ui/Security/login.html.twig`). Use `form_login` + `provider: platform_user` in `security.yaml`; do not write a custom `AbstractLoginFormAuthenticator`.
- **Templates**: `templates/layouts/base.html.twig` extends `@Ui/Layout/base.html.twig` and overrides `block body` to inject the page wrapper / menus / footer. App page templates extend `layouts/base.html.twig` and override `block content` (NOT `block body` — that block now contains the wrapper). UI components `<twig:Ui:Alert>`, `<twig:Ui:Card>`, `<twig:Ui:Modal>` ship from the UiBundle.
- **2FA**: disabled in `platform.yaml`. Flip `platform.security.two_factor.enabled: true` to have the platform Kernel yield `SchebTwoFactorBundle` and register the 2FA routes/services. The base 2FA template comes from `platform.security.two_factor.base_template`.

## Migrations

Every Doctrine migration in `migrations/` mutates the `Schema` object via the DBAL schema builder API (`$schema->getTable()`, `createTable()`, `addColumn()`, `addForeignKeyConstraint()`, `removeForeignKey()`, etc.) — **never** call `$this->addSql()` with hand-written DDL. `doctrine:migrations:diff` will auto-generate raw `addSql()` blocks (especially for SQLite FK/column changes that require table rebuilds); rewrite those blocks with the equivalent schema-builder mutations before committing, and let Doctrine emit the platform-specific DDL. Reference table names through entity constants (`Project::TABLE_NAME`) and pull foreign-key names from `bin/console doctrine:schema:create --dump-sql` rather than hard-coding them inline (constants on the migration class are fine). Both `up()` and `down()` follow this rule. Every migration class is `final`, declares `strict_types`, carries the ECS file header, marks `getDescription`/`up`/`down` with `#[Override]`, and is verified by running it down then up against a populated DB before committing.

## Architecture map

- `src/Entity/` — Doctrine entities (`Client`, `Project`, `TimeEntry`, `User`).
- `src/Repository/` — Doctrine repositories extending `Platform\Repository\EntityRepository`.
- `src/Controller/` — HTTP controllers extending `Platform\Controller\BaseController`. Mix of multi-action and invokable + `#[Template]` styles.
- `src/Command/` — console commands extending `Platform\Console\Command`.
- `src/ApiResource/` — API Platform resource definitions.
- `src/Form/`, `src/Menu/`, `src/Twig/`, `src/Enum/` — typical layering.
- `migrations/` — Doctrine migrations (committed).
- `templates/layouts/base.html.twig` — extends `@Ui/Layout/base.html.twig`. Page templates override `block content`.
- `assets/` — Stimulus controllers and Encore entrypoints; `assets/controllers.json` registers UX packages (including `@solidworx/platform` controllers like `password-visibility` used by the platform login form).
- `platform.yaml` + `platform-schema.json` — unified platform configuration.

## Conventions worth knowing

- Only one active `TimeEntry` is allowed per user (`TimeEntryRepository::findActiveTrackersForUser`).
- PHPUnit 11 with `dama/doctrine-test-bundle`: each test runs in a rolled-back transaction. `phpunit.xml.dist` sets `requireCoverageMetadata="true"` — new test classes need `#[CoversClass]`.
- ECS enforces PSR-12 + Symplify sets and a **mandatory file header** (template in `ecs.php`). Every PHP file in `src/`, `tests/`, `migrations/` must start with `declare(strict_types=1);` and the header. Run `vendor/bin/ecs check --fix` before committing new files.
- The platform UiBundle Twig globals: `ui_base_template` (the value of `ui.templates.base`) and `ui_app_name` (the value of `platform.name`). Security layouts (`@Ui/Layout/security.html.twig`) extend `ui_base_template` — so configuring `ui.templates.base: 'layouts/base.html.twig'` makes the login page inherit the app shell.
