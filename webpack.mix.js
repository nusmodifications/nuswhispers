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
    .extract(['jquery', 'bootstrap-sass', 'moment'])
    .sass('resources/assets/sass/admin.scss', 'public/css')
    .autoload({
        jquery: ['$', 'jQuery', 'jquery'],
        moment: 'moment',
    })
    .webpackConfig({
        plugins: [new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/)],
    })
    .version();
