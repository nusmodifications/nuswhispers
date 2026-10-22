# NUSWhispers

[![CI](https://github.com/nusmodifications/nuswhispers/actions/workflows/ci.yml/badge.svg)](https://github.com/nusmodifications/nuswhispers/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/nusmodifications/nuswhispers/badge.svg)](https://coveralls.io/github/nusmodifications/nuswhispers)
[![StyleCI Status](https://styleci.io/repos/31862595/shield)](https://styleci.io/repos/31862595)

> Laravel 13 + AngularJS setup.

## Requirements

- PHP 8.5.12 or later in the 8.x series, including the CLI and web runtime
- Composer 2.2 or later
- PHP extensions: ctype, curl, dom, fileinfo, filter, hash, mbstring, openssl, PDO MySQL, session, tokenizer, and XML
- MySQL / MariaDB (verified with MariaDB 10.3)
- GD for image tests; PCOV or Xdebug for test coverage
- Redis, if configured for caching or queues
- Node.js 11.15.0 for the existing frontend build

Existing installations should follow the [PHP and Laravel upgrade notes](docs/UPGRADING.md). The PHP minimum includes the PHP2shell fixes. The older `docker-nuswhispers` environment must be updated before use with this branch.

## Installation

1. Rename `.env.example` to `.env`

2. Install PHP dependencies via composer:

```
cd /path/to/current/directory
composer install
```

3. Install JS dependencies via npm:

```
cd /path/to/current/directory
npm install
```

4. Run database migrations and seed data via command line:

```
cd /path/to/current/directory
php artisan migrate
php artisan db:seed
```

5. Compile front-end assets using the following command:

```
npm run development
```

## Checks

Use a separate MySQL/MariaDB database for tests. Set `DB_TEST_HOST`, `DB_TEST_DATABASE`, `DB_TEST_USERNAME`, and `DB_TEST_PASSWORD`, then run:

```sh
php artisan migrate --database=testing
composer test
composer analyse
composer audit --locked
npm run production
```

PHPStan runs at level 5 with Larastan, Mockery support, and deprecated API checks over `app/` and `tests/`. Coverage requires PCOV or Xdebug; use `composer test -- --no-coverage` when neither is installed. The live Imgur upload test remains skipped.

## Sponsors

Many thanks to those who have supported us in the operation of the platform:

[![Bugsnag](/bugsnag_logo_navy.svg)](https://www.bugsnag.com)


