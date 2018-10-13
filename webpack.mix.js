const mix = require('laravel-mix');

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
  })
  .js('resources/js/admin.js', 'public/js')
  .extract(['bootstrap', 'jquery', 'flatpickr'])
  .sass('resources/sass/admin.scss', 'public/css')
  .styles(
    [
      'node_modules/typicons.font/src/font/typicons.css',
      'public/css/admin.css',
    ],
    'public/css/admin.css'
  );

if (mix.inProduction()) {
  mix.sourceMaps().version();
}
