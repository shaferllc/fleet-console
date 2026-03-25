# fleet-operator

**Repository:** [github.com/shaferllc/fleet-operator](https://github.com/shaferllc/fleet-operator)  
**Composer package:** `dply/fleet-operator` · **PHP** `^8.4`

Shared **middleware**, **config**, and **OpenAPI** for applications that expose a Fleet Console–compatible **operator API**:

- `GET {prefix}/summary` — JSON snapshot (see **Summary fields** below); extra keys are stored and can drive `FLEET_ALERT_METRIC_RULES`
- `GET {prefix}/readme` — JSON with required `content` (Markdown is typical); optional `format`, `title`, `subtitle`

Fleet Console polls `summary` and may open `readme` in the UI. Set **`FLEET_OPERATOR_TOKEN`** on this app and store the **same secret** for that service in Fleet Console (**Console → Services** → operator token for that row).

## Should I use this package?

| Use it | Skip it |
|--------|---------|
| Many services, want one auth + contract | Single app, two routes already done |
| Want a published OpenAPI file per release | Only internal docs in a wiki |
| Semver when response/auth rules evolve | API never changes |

## GitHub Actions

- **On [shaferllc/fleet-operator](https://github.com/shaferllc/fleet-operator):** workflow **CI** lives at `.github/workflows/ci.yml` on the default branch (`main`). Open **Actions** → **CI** — you should see runs on each push/PR. Use **Run workflow** if you add `workflow_dispatch` (included in this repo) to run without a new commit.
- **Release:** `.github/workflows/release.yml` creates a GitHub Release when you push a tag `v1.2.3` or run the workflow with a version. That file must exist on the package repo (push it if you only see CI in the Actions sidebar).
- **Fleet Console monorepo:** GitHub **does not** run YAML under `fleet-operator/.github/` for the parent repo. Use the monorepo root workflow `package-fleet-operator.yml` (path filter `fleet-operator/**`) — or **Actions → Run workflow** after enabling `workflow_dispatch` on that file.

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

### Summary fields (all optional)

Fleet Console **renders** these when present (still accepts any other JSON):

| Field | Type | Purpose |
|--------|------|--------|
| `app` / `service` | string | Service id on the card |
| `version` | string / number | Release label |
| `git_sha` / `commit` | string | VCS revision (short hash shown) |
| `runtime` / `php_version` | string | Runtime line |
| `uptime_seconds` | int | Uptime (formatted) |
| `region` | string or string[] | Region label(s) |
| `deployed_at` / `build_at` | string | Deploy / build time |
| `environment`, `generated_at`, `users`, `organizations` | various | Existing dashboard tiles |
| `notes` / `status_message` | string | Highlighted status strip |
| `links` | object | Map of label → **https** URL (link chips) |
| `dependencies` | array | `{ name, ok?, healthy?, detail? }` rows |
| `metrics` | object | Extra counters (key/value grid) |

### Readme JSON shape

Fleet’s README view expects JSON similar to:

```json
{
  "format": "markdown",
  "title": "My product",
  "subtitle": "Internal runbook",
  "content": "# Hello\n\n…"
}
```

`content` must be a string (can be empty). `title` / `subtitle` override or augment the README page header in Fleet Console.

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

## Repository layout

The **canonical Git remote** is **[github.com/shaferllc/fleet-operator](https://github.com/shaferllc/fleet-operator)** (this tree is the package root there).

- **CI:** `.github/workflows/ci.yml` runs PHPUnit on PHP 8.4 and 8.5 on pushes and PRs to that repository.
- **Releases:** Tag **`v1.2.3`** on **shaferllc/fleet-operator** (workflow **Release** in this package repo, or push the tag manually). Packagist reads from that Git URL.

**Fleet Console** (the dashboard app monorepo, if you use one) may still vendor a copy under `fleet-operator/` with a Composer **`path`** repository for local development. Sync that directory from this repo when needed, or depend on **`dply/fleet-operator`** via VCS:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/shaferllc/fleet-operator" }
],
"require": {
    "dply/fleet-operator": "^1.0"
}
```

## License

MIT
