# SolidTrack

A modern, self-hostable time-tracking application for freelancers, consultants, and small teams. Track time against clients and projects, tag work for context, mark entries as billable, and generate reports you can hand to a client or feed into invoicing.

SolidTrack is built on Symfony 7.2 and the [SolidWorx Platform](https://github.com/solidworx/platform), and ships with a Tabler/Bootstrap UI powered by Symfony UX (Turbo, Live Components, Stimulus).

## Features

- **Time tracking** — one-click start/stop timer, manual entry, and edit-in-place rows. Only one active timer per user at a time.
- **Clients & projects** — organise work in a Client → Project → TimeEntry hierarchy with cascade-aware deletes.
- **Tags** — colour-coded tags for cross-cutting context (e.g. `meeting`, `bugfix`, `internal`).
- **Billable flag** — mark entries as billable/non-billable and filter reports accordingly.
- **Dashboard** — at-a-glance KPIs, weekly chart, and top-projects breakdown.
- **Reports** — Summary and Detailed views with filtering by date range, client, project, and tag.
- **REST API** — full API surface via [API Platform 3](https://api-platform.com/) for integrations and automation.
- **Authentication** — email/password login with optional 2FA (toggle in `platform.yaml`).
- **Modern UX** — Turbo navigation, Live Components, and Stimulus controllers for a SPA-feel without the SPA.

## Tech stack

- **Backend:** PHP 8.2+, Symfony 7.2, Doctrine ORM, API Platform 3
- **Frontend:** Webpack Encore, Stimulus, Symfony UX (Turbo, LiveComponent, Chart.js), Tabler / Bootstrap 5, Tom Select, Pickr
- **Database:** PostgreSQL 15 (default) — SQLite supported for tests
- **Tooling:** PHPUnit 11, PHPStan, Rector, ECS, Bun (package manager)

## Requirements

- PHP **8.2+** with `ext-ctype` and `ext-iconv`
- [Composer](https://getcomposer.org/) 2.x
- [Bun](https://bun.com) (used as the JS package manager / script runner — Webpack Encore is the actual bundler)
- PostgreSQL 15+ (or Docker, see below)
- A local checkout of [`solidworx/platform`](https://github.com/solidworx/platform) at `../platform` relative to this repo (resolved via Composer `path` repository)

## Getting started

### 1. Clone the repositories

```bash
git clone https://github.com/solidworx/platform.git
git clone https://github.com/solidworx/solidtrack.git
cd solidtrack
```

The expected layout is:

```
your-workspace/
├── platform/      # solidworx/platform
└── solidtrack/    # this repo
```

### 2. Install dependencies

```bash
composer install
bun install
```

### 3. Configure the database

The easiest way is to use the bundled Docker Compose stack:

```bash
docker compose up -d
```

This starts a PostgreSQL 15 container with defaults from `compose.yaml`. Symfony Flex will write a matching `DATABASE_URL` into your `.env.local` on first install — adjust it if you connect to your own database.

### 4. Run migrations and create a user

```bash
bin/console doctrine:migrations:migrate
bin/console app:create-user you@example.com 'a-strong-password'
```

### 5. Build assets and start the dev server

In one terminal:

```bash
bun run watch
```

In another:

```bash
symfony serve            # or: php -S 127.0.0.1:8000 -t public
```

Open <http://127.0.0.1:8000>, log in, and start tracking time.

## Common commands

### Backend (Symfony / PHP)

| Command | Purpose |
| --- | --- |
| `bin/console` | List all Symfony console commands |
| `bin/console doctrine:migrations:migrate` | Apply pending migrations |
| `bin/console platform:generate-schema` | Regenerate `platform-schema.json` (IDE autocomplete for `platform.yaml`) |
| `bin/console app:create-user <email> <password>` | Create an admin user |
| `vendor/bin/phpunit` | Run the test suite |
| `vendor/bin/phpstan analyse` | Static analysis |
| `vendor/bin/ecs check --fix` | Code style (required for the file header) |
| `vendor/bin/rector process --dry-run` | Preview automated refactors |

### Frontend (Webpack Encore via Bun)

| Command | Purpose |
| --- | --- |
| `bun run dev` | One-off development build |
| `bun run watch` | Rebuild on file changes |
| `bun run dev-server` | Encore dev server with HMR |
| `bun run build` | Production build |

> Bun is used only as the package manager and script runner. The bundler is **Webpack Encore** (`webpack.config.js`); do not substitute `bun build`. Tests run under **PHPUnit**, not `bun test`.

## Configuration

SolidTrack uses a unified configuration file, `platform.yaml`, parsed by the platform bundle. The most common things you'll change:

```yaml
platform:
  name: 'SolidTrack'
  version: '0.1.0-dev'

  security:
    two_factor:
      enabled: false   # flip to true to enable 2FA

  models:
    user: App\Entity\User

ui:
  icon_pack: tabler
  templates:
    base: 'layouts/base.html.twig'
    login: '@Ui/Security/login.html.twig'
```

Standard Symfony environment variables (database URL, mailer DSN, app secret, etc.) live in `.env` / `.env.local`. See the [Symfony configuration docs](https://symfony.com/doc/current/configuration.html) for the full picture.

## Project structure

```
src/
├── ApiResource/    # API Platform resource definitions
├── Command/        # Console commands (extend Platform\Console\Command)
├── Controller/     # HTTP controllers (extend Platform\Controller\BaseController)
├── Entity/         # Doctrine entities (Client, Project, TimeEntry, Tag, User)
├── Enum/           # PHP enums
├── Form/           # Symfony form types
├── Menu/           # KnpMenu builders
├── Repository/     # Doctrine repositories (extend Platform\Repository\EntityRepository)
└── Twig/           # Twig extensions and components

assets/             # Stimulus controllers and Encore entrypoints
config/             # Symfony bundle and service config
migrations/         # Doctrine migrations (schema-builder API — see CLAUDE.md)
templates/          # Twig templates (layouts/base.html.twig extends @Ui/Layout/base.html.twig)
tests/              # PHPUnit tests
```

See [`CLAUDE.md`](./CLAUDE.md) for the platform integration rules and migration conventions that contributors are expected to follow.

## Testing

```bash
vendor/bin/phpunit
vendor/bin/phpunit --filter SomeTest
vendor/bin/phpunit tests/Path/To/SomeTest.php
```

Tests run inside a rolled-back transaction via [`dama/doctrine-test-bundle`](https://github.com/dmaicher/doctrine-test-bundle), so each test sees a clean database. New test classes must declare `#[CoversClass]` (enforced by `requireCoverageMetadata="true"` in `phpunit.xml.dist`).

## Contributing

Contributions are welcome! Please:

1. Fork the repo and create a feature branch.
2. Add tests for your change where reasonable.
3. Run `vendor/bin/ecs check --fix`, `vendor/bin/phpstan analyse`, and `vendor/bin/phpunit` before opening a PR.
4. Open a pull request describing the change and its motivation.

See [`.github/CONTRIBUTING.md`](./.github/CONTRIBUTING.md) and [`.github/CODE_OF_CONDUCT.md`](./.github/CODE_OF_CONDUCT.md) for the full guidelines.

## Security

If you discover a security vulnerability, please follow the responsible disclosure process in [`.github/SECURITY.md`](./.github/SECURITY.md) rather than opening a public issue.

## License

SolidTrack is released under the [MIT License](./LICENSE).
