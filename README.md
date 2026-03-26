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

Optional companion Composer package **`dply/fleet-operator`** provides middleware and OpenAPI for apps that expose the operator surface. **Source of truth:** [github.com/shaferllc/fleet-operator](https://github.com/shaferllc/fleet-operator). This repository keeps a **`fleet-operator/`** directory as a checkout mirror so Composer can resolve the package via a local `path` repository (see root `composer.json`); you can instead point Composer at the GitHub repo with a `vcs` repository entry if you do not need an embedded copy.

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
| **Browser UI** | **Fleet Auth** via **`shaferllc/fleet-idp-client`**: OAuth (authorization code) and/or **email + password** using the password grant (`FLEET_IDP_PASSWORD_CLIENT_*`), which syncs users into the local `users` table. The sign-in form always requires **email** and **password**. A legacy **shared console password** (`FLEET_CONSOLE_PASSWORD*`) still works when the password grant is not configured; email is required on the form but only the password is checked. Session flag `fleet_console_ok` (+ optional `fleet_idp_user`). See **Optional: Fleet Auth** below. |
| **`/api/fleet/*` JSON** | Bearer token (`FLEET_CONSOLE_API_TOKEN`); separate from the dashboard password. |
| **Target operator APIs** | Each app uses its own secret (often `FLEET_OPERATOR_TOKEN` on that app); Fleet stores the matching token per service. |

This keeps the OSS project small: the console gate is session-based, not a full local user directory, unless you point Socialite / password grant at Fleet Auth and Eloquent users (advanced).

### Optional: Fleet Auth (OAuth login)

You can sign in with **[Fleet Auth](https://github.com/shaferllc/fleet-auth)** instead of (or alongside) the shared console password via **`shaferllc/fleet-idp-client`** ([Packagist](https://packagist.org/packages/shaferllc/fleet-idp-client)). The package registers **`GET /oauth/fleet-auth`** → IdP and **`GET /auth/callback`** (when `FLEET_IDP_REDIRECT_PATH=/auth/callback`) for the return URL; use **`FLEET_IDP_WEB_MODE=session`** and **`FLEET_IDP_WEB_MIDDLEWARE=web,fleet.trusted_ip`** so OAuth endpoints respect the same IP allowlist as `/login`. The login Blade template includes **`x-fleet-idp::oauth-button variant="console"`**.

Register an authorization-code client in Fleet Auth with redirect **`{APP_URL}/auth/callback`**, then set in `.env` (see `.env.example`):

| Variable | Purpose |
|----------|---------|
| `FLEET_IDP_URL` | Fleet Auth root URL only (not `/auth/callback`) |
| `FLEET_IDP_CLIENT_ID` / `FLEET_IDP_CLIENT_SECRET` | OAuth client from Fleet Auth seeder |
| `FLEET_IDP_REDIRECT_PATH` | `/auth/callback` (must match Passport) |
| `FLEET_IDP_WEB_MODE` | `session` |
| `FLEET_IDP_WEB_MIDDLEWARE` | `web,fleet.trusted_ip` when using trusted IPs |
| `FLEET_IDP_PASSWORD_CLIENT_ID` / `FLEET_IDP_PASSWORD_CLIENT_SECRET` | Optional **password grant** client — when set, `/login` shows **email + password** (Fleet Auth credentials); users are mirrored into the local `users` table (`provider` / `provider_id`). |


## Releasing

### Fleet Console (this repository)

GitHub Actions create a **GitHub Release** when you publish a semver tag or run the workflow manually.

| Component | Tag format | Workflow |
|-----------|------------|----------|
| **Fleet Console** (this app) | `v1.2.3` | [Release Fleet Console](.github/workflows/release-console.yml) |
| **`fleet-operator/`** (embedded package) | auto `v…` patch on **shaferllc/fleet-operator** | [Tag fleet-operator](.github/workflows/tag-fleet-operator.yml) — runs on push to `master` when `fleet-operator/**` changes |

```bash
git tag -a v1.2.3 -m "Release v1.2.3"
git push origin v1.2.3
```

**Actions → “Release Fleet Console”:** enter `version` (no `v`), optional prerelease flag.

### `dply/fleet-operator` (split package)

The package lives in **[github.com/shaferllc/fleet-operator](https://github.com/shaferllc/fleet-operator)**. Release it there with tags **`v1.2.3`** (not `fleet-operator/v…`). [Packagist](https://packagist.org) (if the package is linked) updates from that repository.

If you still maintain an embedded **`fleet-operator/`** copy inside this monorepo and want a release from here, you can use tag **`fleet-operator/v1.2.3`** and [Release fleet-operator (package)](.github/workflows/release-fleet-operator.yml) — that is optional; prefer tagging on **shaferllc/fleet-operator** for the canonical history and Packagist integration.

**Automated package tags:** When `fleet-operator/**` changes on **`master`**, [Tag fleet-operator](.github/workflows/tag-fleet-operator.yml) runs `git subtree push` to **shaferllc/fleet-operator** `main`, then pushes the next **patch** tag (`v1.2.3` → `v1.2.4`). Add this repository secret: **`FLEET_OPERATOR_PACKAGE_TOKEN`** — a PAT with push access to **shaferllc/fleet-operator**. Use **Actions → Tag fleet-operator** to run manually. For **minor/major** bumps, create the tag on the package repo yourself (or delete/adjust remote tags before re-running).

## License

MIT — see [LICENSE](LICENSE).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

See [SECURITY.md](SECURITY.md).
