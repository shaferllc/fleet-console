# Fleet Console

Web dashboard that **polls each service’s operator HTTP API** (summary + readme JSON), shows health at a glance, and can **alert** when things go wrong. Optional **read-only JSON APIs** support integrations without using the browser UI.

Built with [Laravel](https://laravel.com) and PHP **8.4+**.

## Features

- Password-protected **console** (session) — one shared secret for small teams; optional **bcrypt hash** in `.env` instead of a plain password.
- **Services** (targets) with base URL, operator path prefix, per-service tokens, sort order, and enable/disable.
- **Dashboard** with comparison, per-service cards, sparklines, and alert timeline.
- **Read-only HTTP API** under `/api/fleet/*` when `FLEET_CONSOLE_API_TOKEN` is set (bearer or `X-Fleet-Api-Token`).
- Optional **trusted IP** restriction for the UI and protected API routes (`FLEET_CONSOLE_TRUSTED_IPS`).
- Optional **background polling** via the scheduler (`FLEET_BACKGROUND_POLL_ENABLED`).

Optional companion Composer package **`dply/fleet-operator`** provides middleware and OpenAPI for apps that expose the operator surface (also vendored under `fleet-operator/` in this repo).

## Requirements

- PHP **8.4+**, Composer, Node.js (for building front-end assets).
- SQLite (default), or another Laravel-supported database.

## Quick start

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if using sqlite
php artisan migrate
```

Configure at minimum:

| Variable | Purpose |
|----------|---------|
| `FLEET_CONSOLE_PASSWORD_HASH` or `FLEET_CONSOLE_PASSWORD` | Sign in to the web UI |

After first login, add each monitored app under **Console → Services** and set an **operator token** per service (same value as `FLEET_OPERATOR_TOKEN` on that app). Tokens are not configured via Fleet’s `.env`.

Generate a bcrypt hash (recommended):

```bash
php -r "echo password_hash('your-long-random-secret', PASSWORD_BCRYPT), PHP_EOL;"
```

Put the result in `FLEET_CONSOLE_PASSWORD_HASH=` in `.env`.

Then:

```bash
composer run setup   # or: composer install && npm install && npm run build
php artisan serve
```

Open `/login`, then add or import services under **Console → Services**. Example seed rows live in `config/fleet_targets.php`; once the database has targets, the UI uses stored rows instead of the file list.

See `.env.example` for polling, alerts, SLO, CORS, health checks, and HTTP verify options.

## Authentication model

| Surface | Mechanism |
|---------|-----------|
| **Browser UI** | Shared password → session flag (`fleet.console` middleware). Not multi-user; use SSO or a reverse proxy in front if you need identities. |
| **`/api/fleet/*` JSON** | Bearer token (`FLEET_CONSOLE_API_TOKEN`); separate from the dashboard password. |
| **Target operator APIs** | Each app uses its own secret (often `FLEET_OPERATOR_TOKEN` on that app); Fleet stores the matching token per service. |

This keeps the OSS project small. A full user table and Laravel Breeze/Fortify would be a larger follow-up if you want native multi-account auth.

## Releasing

GitHub Actions create a **GitHub Release** with auto-generated notes when you publish a semver tag (or when you run the workflow manually).

| Component | Tag format (monorepo) | Workflow |
|-----------|----------------------|----------|
| **Fleet Console** (this app) | `v1.2.3` | [Release Fleet Console](.github/workflows/release-console.yml) |
| **`dply/fleet-operator`** | `fleet-operator/v1.2.3` | [Release fleet-operator (package)](.github/workflows/release-fleet-operator.yml) |

**Option A — tag from git:**

```bash
git tag -a v1.2.3 -m "Release v1.2.3"
git push origin v1.2.3

git tag -a fleet-operator/v1.0.1 -m "fleet-operator v1.0.1"
git push origin fleet-operator/v1.0.1
```

**Option B — Actions → workflow “Run workflow”:** enter `version` (no `v`), optional prerelease flag. That creates the tag and release in one step (no extra push needed).

After a **`fleet-operator/v*`** or standalone **`v*`** tag on the package repo, [Packagist](https://packagist.org) (if linked) picks up **`dply/fleet-operator`** automatically.

## License

MIT — see [LICENSE](LICENSE).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

See [SECURITY.md](SECURITY.md).
