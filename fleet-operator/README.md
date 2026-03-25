# fleet-operator

**Composer package:** `dply/fleet-operator` · **PHP** `^8.4`

Shared **middleware**, **config**, and **OpenAPI** for applications that expose a Fleet Console–compatible **operator API**:

- `GET {prefix}/summary` — JSON snapshot (metrics, health hints, anything your alerts care about)
- `GET {prefix}/readme` — JSON with at least `content` (Markdown is typical); optional `format`, `title`

Fleet Console polls `summary` and may open `readme` in the UI. Set **`FLEET_OPERATOR_TOKEN`** on this app and store the **same secret** for that service in Fleet Console (**Console → Services** → operator token for that row).

## Should I use this package?

| Use it | Skip it |
|--------|---------|
| Many services, want one auth + contract | Single app, two routes already done |
| Want a published OpenAPI file per release | Only internal docs in a wiki |
| Semver when response/auth rules evolve | API never changes |

## Install

```bash
composer require dply/fleet-operator
```

Optional: publish bundled config and OpenAPI into your project when your stack supports tagged package publishing (tags `fleet-operator-config` and `fleet-operator-openapi`).

Set in `.env`:

```env
FLEET_OPERATOR_TOKEN=your-long-random-secret
```

## Wire HTTP routes (you choose the prefix)

Typical prefixes are `api/operator` or `api/v1/operator`.

Apply `AuthenticateFleetOperator` to the routes that serve your operator **summary** and **readme** endpoints. Example — adapt to your router and controllers:

```php
<?php

use Dply\FleetOperator\Http\Middleware\AuthenticateFleetOperator;

// Pseudocode: attach AuthenticateFleetOperator::class to GET .../summary and GET .../readme
```

Return JSON bodies (or your framework’s JSON response type) from those handlers.

### Readme JSON shape

Fleet’s README view expects JSON similar to:

```json
{
  "format": "markdown",
  "title": "My product",
  "content": "# Hello\n\n…"
}
```

`content` must be a string (can be empty).

## Versioning

Follow [SemVer](https://semver.org/). Bump **minor** when adding optional JSON fields; **major** if auth or required response shapes break consumers.

## Packagist

**Install name:** `dply/fleet-operator` — not `fleetphp/fleet-operator`.

If Packagist still shows the old vendor, the registry entry was never updated: Packagist package names come from **maintainer actions**, not from this repo alone.

1. Open [Submit package](https://packagist.org/packages/submit) and submit your Git repo if **`dply/fleet-operator`** is missing.
2. If **`fleetphp/fleet-operator`** is your package and pointed at this same repository, either:
   - **Abandon** it on Packagist (set replacement to `dply/fleet-operator`), then ensure `dply/fleet-operator` is submitted; or  
   - Contact Packagist support if you need the old name removed so the URL can attach to the new package name.
3. Ensure the GitHub/GitLab integration (webhook or “Update”) has run so Packagist reads the current `composer.json` (`name` must be `dply/fleet-operator`).

`composer.json` includes `"replace": { "fleetphp/fleet-operator": "*" }` so projects that still list the old name can resolve the replacement when both are visible to Composer (e.g. after you abandon with a replacement, or while migrating).

## Own Git repository

This folder is a **standalone Composer package** (everything here is the repo root after a split).

- **CI:** `.github/workflows/ci.yml` runs PHPUnit on PHP 8.4 and 8.5 once this directory is the root of a GitHub repository.
- **From a monorepo** that keeps this tree at `fleet-operator/`, you can extract history with:

  `git subtree split -P fleet-operator -b fleet-operator-release`

  Push branch `fleet-operator-release` to the new remote, set it as the default branch, then tag releases (e.g. `v1.0.0`).

- **Releases:** In this monorepo use tag `fleet-operator/v1.2.3` (workflow **Release fleet-operator (package)**). In a standalone clone of this package, use tag `v1.2.3` (workflow `.github/workflows/release.yml`). You can also run the workflow from the Actions tab with a semver **version** input.

- **Consume before Packagist:** in the host app `composer.json`, add a `repositories` entry with `"type": "vcs"` and the Git URL, then `composer require dply/fleet-operator:^1.0`.

## License

MIT
