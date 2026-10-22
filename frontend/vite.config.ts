import tailwindcss from '@tailwindcss/vite';
import { voidReact } from '@void/react/plugin';
import { defineConfig, loadEnv } from 'vite';
import { voidPlugin } from 'void';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, '..', 'VITE_');
  const pages = voidPlugin().map((plugin) => {
    const configure = plugin.configureServer;
    if (plugin.name !== 'void:node-target' || typeof configure !== 'function') return plugin;
    return {
      ...plugin,
      configureServer(server) {
        // Let Vite serve modules, static files and the API proxy before Void's catch-all page.
        return async () => {
          const after = await configure.call(this, server);
          if (after) await after();
        };
      },
    } satisfies typeof plugin;
  });

  return {
    appType: 'custom',
    envDir: '..',
    publicDir: '../public',
    plugins: [pages, voidReact(), tailwindcss()],
    build: { assetsDir: 'assets/web', copyPublicDir: false },
    server: {
      port: 5173,
      strictPort: true,
      proxy: {
        '/api': {
          target: env.VITE_API_PROXY || 'http://127.0.0.1:8000',
          changeOrigin: true,
        },
      },
    },
  };
});
