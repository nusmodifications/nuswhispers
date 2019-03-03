const mix = require('laravel-mix');
const { IgnorePlugin } = require('webpack');

/**
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
  .autoload({
    jquery: ['$', 'window.jQuery'],
  })
  .options({
    hmrOptions: {
      host: 'localhost',
      port: 8085,
    },
    uglify: {
      extractComments: true,
    },
  })
  .webpackConfig({
    resolve: {
      alias: {
        jquery: 'jquery/dist/jquery.slim.js',
      },
    },
    plugins: [new IgnorePlugin(/^\.\/locale$/, /moment$/)],
  })
  .js(
    'resources/js/confessions/index.js',
    'public/assets/admin/confessions/index.js'
  )
  .js(
    'resources/js/confessions/edit.js',
    'public/assets/admin/confessions/edit.js'
  )
  .js('resources/js/admin.js', 'public/assets/admin')
  .extract([
    'bootstrap',
    'jquery',
    'moment',
    'daterangepicker',
    'typicons.font',
  ])
  .sass('resources/sass/auth.scss', 'public/assets/admin')
  .sass('resources/sass/admin.scss', 'public/assets/admin');

if (mix.inProduction()) {
  mix.sourceMaps().version();
}
