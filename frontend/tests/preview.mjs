import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { extname, resolve } from 'node:path';

// Local verification of the Nginx + Laravel static-shell contract. No live API access.
const root = fileURLToPath(new URL('../../public/', import.meta.url));
const contentTypes = {
  '.html': 'text/html',
  '.json': 'application/json',
  '.js': 'text/javascript',
  '.css': 'text/css',
  '.png': 'image/png',
  '.gif': 'image/gif',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
};
createServer(async (request, response) => {
  try {
    const url = new URL(request.url, 'http://localhost');
    const path = decodeURIComponent(url.pathname);
    if (path === '/__test__/admin') {
      const manifest = JSON.parse(await readFile(`${root}build/manifest.json`, 'utf8'));
      const editing = url.searchParams.has('edit');
      const entry = manifest[`resources/js/confessions/${editing ? 'edit' : 'index'}.js`].file;
      const css = manifest['resources/sass/admin.scss'].file;
      const fields = editing
        ? '<select name="status"><option>Pending</option><option>Featured</option></select><div class="scheduled-form-group d-none"><div class="date-picker form-control custom-select"><div class="label">Select Date</div><input name="schedule" type="hidden"></div></div>'
        : '<div class="date-picker"><span class="label"></span><input name="start" type="hidden" value="1704067200"><input name="end" type="hidden" value="1704153600"></div><a href="#" class="clear">Clear</a>';
      response.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      response.end(
        `<!doctype html><html lang="en"><head><meta charset="utf-8"><link rel="stylesheet" href="/build/${css}"><script type="module" src="/build/${entry}"></script></head><body><main class="p-4">${fields}</main></body></html>`,
      );
      return;
    }
    if (path.startsWith('/api/')) {
      response.writeHead(404, { 'Content-Type': 'application/json' });
      response.end('{"success":false}');
      return;
    }
    let file = resolve(root, `.${path}`);
    if (!file.startsWith(root)) file = `${root}app.html`;
    const exists = await stat(file)
      .then((info) => info.isFile() && Boolean(contentTypes[extname(file)]))
      .catch(() => false);
    if (!exists) {
      if (path.startsWith('/_void/pages/') && path.endsWith('.json'))
        file = `${root}_void/pages/home.json`;
      else if (path.startsWith('/assets/') || path.startsWith('/build/')) {
        response.writeHead(404);
        response.end();
        return;
      } else file = `${root}${path === '/' ? 'app.html' : 'app-route.html'}`;
    }
    response.writeHead(200, {
      'Content-Type': contentTypes[extname(file)] || 'application/octet-stream',
    });
    response.end(await readFile(file));
  } catch (error) {
    response.writeHead(500);
    response.end(String(error));
  }
}).listen(4173, '127.0.0.1', () =>
  process.stdout.write('Static frontend preview: http://127.0.0.1:4173\n'),
);
