import express from 'express';
import next from 'next';
import { join } from 'path';
import favicon from 'serve-favicon';

const port = parseInt(process.env.PORT || '', 10) || 3000;
const app = next({ dev: process.env.NODE_ENV !== 'production' });
const handle = app.getRequestHandler();

app.prepare().then(() => {
  const server = express();

  server.listen(port, (err: Error) => {
    if (err) {
      throw err;
    }

    server.use(favicon(join(__dirname, 'static/icons', 'favicon.ico')));

    server.use(express.static('static'));

    server.get('/', (req, res) => app.render(req, res, '/featured'));

    server.get('/category/:id', (req, res) =>
      app.render(req, res, '/category', { id: req.params.id }),
    );

    server.get('/tag/:id', (req, res) =>
      app.render(req, res, '/tag', { id: req.params.id }),
    );

    server.get('/confession/:id', (req, res) =>
      app.render(req, res, '/confession', { id: req.params.id }),
    );

    server.get('*', (req, res) => handle(req, res));

    // tslint:disable-next-line
    console.log(`> Ready on http://localhost:${port}`);
  });
});
