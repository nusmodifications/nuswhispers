import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
  // Daterangepicker's CommonJS factory expects Moment itself, not its ESM namespace.
  resolve: { alias: [{ find: /^moment$/, replacement: 'moment/moment.js' }] },
  plugins: [
    laravel({
      input: [
        'resources/js/admin.js',
        'resources/js/confessions/index.js',
        'resources/js/confessions/edit.js',
        'resources/sass/admin.scss',
        'resources/sass/auth.scss',
      ],
      refresh: true,
    }),
  ],
  css: {
    preprocessorOptions: {
      scss: {
        silenceDeprecations: [
          'import',
          'global-builtin',
          'color-functions',
          'if-function',
          'abs-percent',
        ],
      },
    },
  },
});
