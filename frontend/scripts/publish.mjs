import { cp, mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';

const built = fileURLToPath(new URL('../dist/client/', import.meta.url));
const destination = fileURLToPath(new URL('../../public/', import.meta.url));

// Replace only the public frontend output; preserve admin bundles, uploads, and PHP.
await rm(`${destination}assets/web`, { recursive: true, force: true });
await rm(`${destination}_void/pages`, { recursive: true, force: true });
await mkdir(`${destination}assets`, { recursive: true });
await cp(`${built}assets/web`, `${destination}assets/web`, { recursive: true });
await cp(`${built}_void`, `${destination}_void`, { recursive: true });
await writeFile(`${destination}app.html`, await readFile(`${built}index.html`));
await writeFile(`${destination}app-route.html`, await readFile(`${built}home.html`));
