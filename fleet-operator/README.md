# fleet-operator

**Composer package:** `dply/fleet-operator` · **PHP** `^8.4`

Shared **middleware**, **config**, and **OpenAPI** for Laravel apps that expose a Fleet Console–compatible **operator API**:

- `GET {prefix}/summary` — JSON snapshot (metrics, health hints, anything your alerts care about)
- `GET {prefix}/readme` — JSON with at least `content` (Markdown is typical); optional `format`, `title`

Fleet Console polls `summary` and may open `readme` in the UI. Use the **same** `FLEET_OPERATOR_TOKEN` on the app and on Fleet (or a per-target token in the console).

## Should I use this package?

| Use it | Skip it |
|--------|---------|
| Many Laravel apps, want one auth + contract | Single app, two routes already done |
| Want a published OpenAPI file per release | Only internal docs in a wiki |
| Semver when response/auth rules evolve | API never changes |

## Install

```bash
composer require dply/fleet-operator
```

Publish config (optional):

```bash
php artisan vendor:publish --tag=fleet-operator-config
```

Publish OpenAPI into your repo/docs (optional):

```bash
php artisan vendor:publish --tag=fleet-operator-openapi
```

Set in `.env`:

```env
FLEET_OPERATOR_TOKEN=your-long-random-secret
```

## Wire routes (you choose the prefix)

Most apps use `api/operator`. Dply-style stacks often use `api/v1/operator`.

```php
<?php

use Dply\FleetOperator\Http\Middleware\AuthenticateFleetOperator;
use Illuminate\Support\Facades\Route;

Route::prefix('api/operator')
    ->middleware([AuthenticateFleetOperator::class])
    ->group(function (): void {
        Route::get('/summary', [App\Http\Controllers\Operator\OperatorController::class, 'summary']);
        Route::get('/readme', [App\Http\Controllers\Operator\OperatorController::class, 'readme']);
    });
```

Implement controllers (or closures) that return JSON arrays / `JsonResponse` as needed.

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

After pushing to GitHub/GitLab, submit the repository URL on [packagist.org](https://packagist.org/packages/submit).

## Own Git repository

This folder is a **standalone Composer package** (everything here is the repo root after a split).

- **CI:** `.github/workflows/ci.yml` runs PHPUnit on PHP 8.4 and 8.5 once this directory is the root of a GitHub repository.
- **From a monorepo** that keeps this tree at `fleet-operator/`, you can extract history with:

  `git subtree split -P fleet-operator -b fleet-operator-release`

  Push branch `fleet-operator-release` to the new remote, set it as the default branch, then tag releases (e.g. `v1.0.0`).

- **Consume before Packagist:** in the host app `composer.json`, add a `repositories` entry with `"type": "vcs"` and the Git URL, then `composer require dply/fleet-operator:^1.0`.

## License

MIT
