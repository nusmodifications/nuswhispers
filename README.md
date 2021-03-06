# NUSWhispers

[![Build Status](https://travis-ci.org/nusmodifications/nuswhispers.svg)](https://travis-ci.org/nusmodifications/nuswhispers)
[![Coverage Status](https://coveralls.io/repos/github/nusmodifications/nuswhispers/badge.svg)](https://coveralls.io/github/nusmodifications/nuswhispers)
[![StyleCI Status](https://styleci.io/repos/31862595/shield)](https://styleci.io/repos/31862595)

> Laravel 5 + AngularJS setup.

## Requirements

- Web server with PHP support
- MySQL / MariaDB
- Redis

For a development environment, using [docker-nuswhispers](https://github.com/nusmodifications/docker-nuswhispers) is highly recommended.

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

## Sponsors

Many thanks to those who have supported us in the operation of the platform:

[![Bugsnag](/bugsnag_logo_navy.svg)](https://www.bugsnag.com)


