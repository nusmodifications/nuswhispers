const mix = require('laravel-mix');
const webpack = require('webpack');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application.
 |
 */
mix.js('resources/assets/js/admin.js', 'public/js')
    .extract(['jquery', 'bootstrap-sass'])
    .sass('resources/assets/sass/admin.scss', 'public/css')
    .autoload({
        jquery: ['$', 'jQuery', 'jquery'],
    });
