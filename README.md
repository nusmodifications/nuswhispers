# NUSWhispers

[![CI](https://github.com/nusmodifications/nuswhispers/actions/workflows/ci.yml/badge.svg)](https://github.com/nusmodifications/nuswhispers/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/nusmodifications/nuswhispers/badge.svg)](https://coveralls.io/github/nusmodifications/nuswhispers)
[![StyleCI Status](https://styleci.io/repos/31862595/shield)](https://styleci.io/repos/31862595)

> Laravel 13 API and admin, with a static Void + React public frontend.

## Requirements

- PHP 8.5.12 or later in the 8.x series, including the CLI and web runtime
- Composer 2.2 or later
- PHP extensions: ctype, curl, dom, fileinfo, filter, hash, mbstring, openssl, PDO MySQL, session, tokenizer, and XML
- MySQL / MariaDB (verified with MariaDB 10.3)
- GD for image tests; PCOV or Xdebug for test coverage
- Redis, if configured for caching or queues
- Node.js 24.22.1 (see `.node-version`) and pnpm 12.14.0 for frontend development/builds

Existing installations should follow the [PHP and Laravel upgrade notes](docs/UPGRADING.md). The PHP minimum includes the PHP2shell fixes. The older `docker-nuswhispers` environment must be updated before use with this branch.

## Installation

1. Rename `.env.example` to `.env`

2. Install PHP dependencies via composer:

```
cd /path/to/current/directory
composer install
```

3. Install JavaScript dependencies with pnpm:

```
cd /path/to/current/directory
pnpm install --frozen-lockfile
```

4. Run database migrations and seed data via command line:

```
cd /path/to/current/directory
php artisan migrate
php artisan db:seed
```

5. Compile front-end assets using the following command:

```
pnpm build
```

## Frontend development

The public website uses Void 1.2 in static mode, React 19, TypeScript, Tailwind 4, Base UI, and Fate. Fate talks to the existing Laravel REST endpoints through a custom transport; no Fate server, GraphQL endpoint, or additional database is required. The admin remains Blade/jQuery/Bootstrap, with its assets built by Vite.

Copy the public build settings from `.env.example` into your local environment. Only `VITE_*` variables are exposed to the browser. See [frontend hosting and configuration](docs/FRONTEND.md) before building a release.

Run the Laravel backend and public frontend in separate terminals:

```sh
php artisan serve --host=127.0.0.1 --port=8000
pnpm dev
```

Open the public frontend at [localhost:5173](http://localhost:5173). Requests to `/api` are proxied to the local Laravel server. Use `pnpm dev:admin` when working on the admin assets, and visit the Laravel URL for admin pages.

## Checks

Use a separate MySQL/MariaDB database for tests. Set `DB_TEST_HOST`, `DB_TEST_DATABASE`, `DB_TEST_USERNAME`, and `DB_TEST_PASSWORD`, then run:

```sh
php artisan migrate --database=testing
composer test
composer analyse
composer audit --locked
pnpm check
pnpm test
pnpm build
```

Oxlint, Oxfmt, TypeScript, Vitest transport tests, and both production builds run in CI. Browser tests are available separately: run `pnpm --filter @nuswhispers/web exec playwright install chromium`, then `pnpm test:browser`. They use local API and CAPTCHA fixtures, with desktop and mobile viewports.

PHPStan runs at level 5 with Larastan, Mockery support, and deprecated API checks over `app/` and `tests/`. Coverage requires PCOV or Xdebug; use `composer test -- --no-coverage` when neither is installed.

## Sponsors

Many thanks to those who have supported us in the operation of the platform:

[![Bugsnag](/bugsnag_logo_navy.svg)](https://www.bugsnag.com)
