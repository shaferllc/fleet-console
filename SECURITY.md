# Security

## Supported versions

Security fixes are applied to the default branch (`master` / `main`) as needed. Use the latest release or commit when deploying.

## Reporting a vulnerability

Please **do not** open a public GitHub issue for security reports.

Instead, contact the maintainers privately (e.g. GitHub Security Advisories for this repository, or email the repo owner). Include:

- Description of the issue and impact
- Steps to reproduce (or a proof of concept)
- Affected versions or commit, if known

We aim to acknowledge reports within a few business days.

## Deployment notes

Fleet Console is designed for **trusted networks**: it uses a **shared dashboard password** (session-based), optional **IP allowlisting** (`FLEET_CONSOLE_TRUSTED_IPS`), and separate **bearer tokens** for operator polling and read-only JSON APIs. Harden production with HTTPS, secure cookies, and network controls appropriate to your environment.
