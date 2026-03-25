# Contributing

Thanks for helping improve Fleet Console.

## Development

1. PHP **8.4+**, Composer, Node.js for the Vite asset build.
2. Copy `.env.example` to `.env`, run `php artisan key:generate`, then `php artisan migrate`.
3. Set `FLEET_CONSOLE_PASSWORD` or `FLEET_CONSOLE_PASSWORD_HASH` so you can sign in at `/login`.
4. `composer install` and `npm install`.
5. `php artisan test` and `./vendor/bin/pint` before opening a pull request.

## Pull requests

- Keep changes focused; include or update tests when behavior changes.
- Use clear commit messages (imperative mood).

## Releases

Maintainers: use GitHub Actions **Release Fleet Console** and **Release fleet-operator (package)** (or push semver tags — see [README § Releasing](README.md#releasing)).

## Security

Please report security issues privately — see [SECURITY.md](SECURITY.md).
