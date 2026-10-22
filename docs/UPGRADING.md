# PHP 8.5 and Laravel 13 upgrade

This branch requires PHP **8.5.12 or later in the 8.x series** and upgrades Laravel from **6.20.12 to 13.38.0**. The PHP constraint, `.php-version`, Composer platform setting, and CI target the patched 8.5 release line. PHP2shell is fixed in the PHP runtime; deploying application code alone does not patch a server. See the [PHP security advisory](https://www.php.net/archive/2026.php#2026-10-21-5).

## Application changes

- Replaced the PHP 7 only Facebook SDK with `joelbutcher/facebook-graph-sdk` 6.1.2, adapted response and exception handling, and explicitly configured its HTTP client so posting requests retain their content type.
- Added `laravel/ui` to retain the existing login and password reset controllers and routes. Existing user and password reset tables remain in use.
- Retained the application's kernels, routes, controller namespace, language files, database schema, and API date format.
- Converted factories and seeders to current Laravel APIs and namespaces, and upgraded the test suite to PHPUnit 12.
- Updated mail configuration and added Symfony's Mailgun transport. Existing `MAIL_DRIVER` values remain a fallback for `MAIL_MAILER`. SMTP, sendmail, Mailgun, log, and array transports are configured.
- Cached API responses now contain plain arrays and reject PHP object unserialization. **Existing application caches must be cleared during deployment.**
- Replaced direct environment reads in Facebook connection and moderation code with configuration reads, so `config:cache` preserves those settings.
- Replaced the obsolete Travis configuration with GitHub Actions for PHP tests, PHPStan/Larastan level 5, dependency auditing, and the existing frontend production build. The frontend dependencies and Node 11 build remain unchanged.

## Validation

Run Composer checks and the tests on PHP 8.5.12 or later:

```sh
composer install --prefer-dist --no-interaction
composer validate --strict
composer audit --locked
php artisan migrate --database=testing --force
composer analyse
composer test
pnpm install --frozen-lockfile
pnpm build
```

Use an isolated database for testing. Fresh migrations were verified on MariaDB 10.3, and the upgraded application was also checked against a copy of the local Laravel 6 schema and seed data. This is not verification against a production database dump. No database migrations were added or changed for this upgrade.

Tests cover API date serialization and file caching, existing authentication and password resets, moderation and scheduling, and Facebook SDK posting, batch responses, and error handling using simulated HTTP responses. Live Facebook, reCAPTCHA, and mail delivery still require staging verification with appropriate credentials.

## Existing deployment

1. Keep the site offline while the host is being upgraded. The existing Ubuntu 16.04 / PHP 7.2 deployment requires separate host-level work before it can run this branch.
2. Preserve `APP_KEY`, environment configuration, database contents, uploads, and other persistent storage. Take and verify a database backup and test the release against an isolated copy before switching production.
3. Install patched PHP for both CLI and PHP-FPM and verify their actual versions. Update the Nginx FastCGI socket and any cron or queue-worker PHP paths to the new runtime.
4. Install production dependencies in the new release directory:

   ```sh
   composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
   composer check-platform-reqs --no-dev
   php artisan optimize:clear
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. Validate application routes, admin login, background commands, integrations, and persistent uploads before restoring public traffic. Account for the existing Nginx/PHP service masks and Monit configuration when bringing the site back. Keep the old PHP 7.2 service disabled.

The repository upgrade does not update, restart, or unmask production services. Do not run `migrate:fresh` or the development seeders against production.
