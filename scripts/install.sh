#!/usr/bin/env bash

cd $1/../
composer install

npm install -g npm
npm install

php artisan migrate