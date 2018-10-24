import { createServer } from 'http';
import * as next from 'next';

const port = parseInt(process.env.PORT || '', 10) || 3000;
const app = next({ dev: process.env.NODE_ENV !== 'production' });
const handle = app.getRequestHandler();

app.prepare().then(() => {
  createServer(handle).listen(port, (err: Error) => {
    if (err) {
      throw err;
    }

    // tslint:disable-next-line
    console.log(`> Ready on http://127.0.0.1:${port}`);
  });
});
