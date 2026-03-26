# Contributing

Thanks for helping improve Fleet Console.

## Development

1. PHP **8.4+**, Composer, Node.js for the Vite asset build.
2. Copy `.env.example` to `.env`, run `php artisan key:generate`, then `php artisan migrate`.
3. Set `FLEET_CONSOLE_PASSWORD_HASH` so you can sign in at `/login` (or configure Fleet IdP password grant / OAuth).
4. `composer install` and `npm install`.
5. `php artisan test` and `./vendor/bin/pint` before opening a pull request.

## Pull requests

- Keep changes focused; include or update tests when behavior changes.
- Use clear commit messages (imperative mood).

## Releases

Maintainers: **Fleet Console** — GitHub Actions **Release Fleet Console** or push `v*.*.*` tags (see [README § Releasing](README.md#releasing)).

**`dply/fleet-operator`** is developed in its own repository: **[github.com/shaferllc/fleet-operator](https://github.com/shaferllc/fleet-operator)**. Open issues and pull requests there for middleware, OpenAPI, or package `composer.json` changes. Release with `v*.*.*` tags on that repo (Packagist follows that remote). This monorepo may still contain a **`fleet-operator/`** mirror for local Composer `path` installs; keep it in sync when you vendor changes from the split repo.

## Security

Please report security issues privately — see [SECURITY.md](SECURITY.md).
