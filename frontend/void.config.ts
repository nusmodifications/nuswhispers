import { defineConfig } from 'void/config';

export default defineConfig({
  target: 'node',
  output: 'static',
  head: {
    htmlAttrs: { lang: 'en' },
    title: 'NUSWhispers',
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      {
        name: 'description',
        content:
          'Have an interesting story to share or just need to get something off your chest? Tell us your story here at NUSWhispers! No one will know it was you.',
      },
      { name: 'theme-color', content: '#2e8fc2' },
      { property: 'og:title', content: 'NUSWhispers' },
      { property: 'og:image', content: 'https://nuswhispers.com/favicon-512x512.png' },
    ],
    link: [
      { rel: 'icon', href: '/favicon.ico', sizes: '16x16 32x32 48x48' },
      { rel: 'icon', href: '/favicon.svg', type: 'image/svg+xml', sizes: 'any' },
      { rel: 'manifest', href: '/manifest.json' },
      { rel: 'apple-touch-icon', href: '/apple-icon-180x180.png', sizes: '180x180' },
    ],
  },
});
