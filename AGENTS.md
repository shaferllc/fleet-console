## Learned User Preferences

- Prefer database-backed Fleet Console settings (auth, alerts, metrics rules, targets) over env vars such as `FLEET_CONSOLE_PASSWORD` and redundant static config that duplicates what the DB already stores.
- Configure Fleet IDP without publishing the package’s full `fleet_idp.php`: `FleetIdpCustomization::merge()` in `AppServiceProvider` plus app-owned `config/fleet_idp_overrides.php` (not the published vendor file).
- Use the `fleet-idp-client` package for Fleet IDP client behavior rather than in-repo copies of that logic.
- Operator API access should use one bearer token per target application or service, not a single shared global operator token.
- For the `fleetphp/fleet-operator` package, do not describe or market it as Laravel-only in README or Composer description; it may run on Laravel but should read as framework-agnostic.
- The `fleet-operator` package is expected to require PHP 8.4 or newer.

## Learned Workspace Facts

- Missing-table errors on the dashboard (for example `fleet_alert_events` on SQLite) usually mean migrations are pending; run `php artisan migrate` after pulling schema changes.
- Production MySQL deployments using the database session driver need the `sessions` table present; missing `sessions` indicates session migrations were not applied.
- The Fleet Console UI should stay available when remote targets are unreachable; the app should not fail the whole console with 503 solely because upstream poll or summary requests fail.
- Fleet targets are managed through the Console admin backed by the database, not only through static config files.
- The operator package in this repo is `dply/fleet-operator` (path `./fleet-operator`); source is `https://github.com/shaferllc/fleet-operator`. Composer `replace` covers the legacy name `fleetphp/fleet-operator`.
