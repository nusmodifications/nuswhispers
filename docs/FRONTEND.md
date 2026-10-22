# Public frontend

The public website is a client-rendered React application built with Void's Pages Router and `output: 'static'`. It uses TypeScript, Tailwind, Base UI, and Fate. The PHP API, crawler previews, authentication, moderation, and database stay in Laravel. The admin stays Blade/jQuery/Bootstrap; Vite builds its existing assets into `public/build`. Public submissions contain text and categories; file uploads have been removed, including the legacy `/api/images` endpoint. Existing confession images continue to display.

## Data fetching

`frontend/src/api.ts` defines the views, typed roots, mutations, and custom Fate transport. It adapts the existing REST envelopes to Fate's normalized cache. No Fate server or generated backend module is required. Confession connections translate Fate's `after` cursor into Laravel's offset, retaining the same timestamp while loading subsequent pages. Submissions keep Laravel's form encoding, CAPTCHA validation, fingerprint field, and error messages.

User content renders as React text, with separate links for hashtags and HTTP(S) URLs. The client keeps the existing `nuswhispers.*` local-storage keys for submission limits and fingerprint tokens. The limit is decremented only after a successful submission.

Facebook comments and likes link to Facebook. Sharing uses Facebook/Twitter share URLs. The legacy Facebook login UI was already disabled; this migration does not enable it or change the session API. Favourites still require an existing Facebook session and show the API's error if none exists. Live Meta permissions and credentials need separate validation before re-enabling login.

## Build configuration

Set these public values before `pnpm build`; Vite embeds them in the static bundle. A later PHP environment change does not update an already-built frontend.

| Variable                       | Purpose / legacy build option                                                                                                |
| ------------------------------ | ---------------------------------------------------------------------------------------------------------------------------- |
| `VITE_API_URL`                 | REST base URL; defaults to `/api` (`API_URL`)                                                                                |
| `VITE_API_PROXY`               | Development proxy target only; defaults to `http://127.0.0.1:8000`                                                           |
| `VITE_FB_PAGE_ID`              | Facebook page ID for confession permalinks (`FB_PAGE_ID`)                                                                    |
| `VITE_RECAPTCHA_KEY`           | Public reCAPTCHA v2 site key (`RECAPTCHA_KEY`); submission is disabled without it                                            |
| `VITE_FINGERPRINT_API_KEY`     | Public request/response field name (`FINGERPRINT_API_KEY`); must match Laravel's `FINGERPRINT_KEY` or its configured default |
| `VITE_FINGERPRINT_STORAGE_KEY` | Preserve the previous public frontend value (`FINGERPRINT_STORAGE_KEY`) so existing browser tokens survive                   |

The Facebook JavaScript SDK and its `FB_APP_ID` build setting are no longer needed for the page widget or share links. The old Universal Analytics tracking tag is omitted; a replacement analytics integration is outside this migration. reCAPTCHA loads only on submission pages. Keep `RECAPTCHA_SECRET`, Facebook app secrets/page tokens, database passwords, and `APP_KEY` outside `VITE_*` variables.

Node is needed to build the release, including Void's prerender step. It is not a production web server requirement. Use the pinned Node version from `.node-version` and pnpm from `package.json`:

```sh
pnpm install --frozen-lockfile
pnpm check
pnpm test
pnpm build
```

## Hosting and deployment

The build publishes these outputs:

- `public/app.html`: root route shell.
- `public/app-route.html`: shared shell for all other public routes.
- `public/assets/web/`: hashed public JS, CSS, images, and fonts.
- `public/_void/`: static Void route data.
- `public/build/`: admin assets and Laravel Vite manifest.

Include all five outputs together in a new release. Do not copy `frontend/dist/ssr`, run a Node daemon, or overwrite shared storage/uploads. Build settings must be supplied before compiling. Existing Angular build arguments passed with `--env.KEY=...` have been replaced by the `VITE_*` environment variables above. A fresh release should not include old `mobile_submit.html` or Angular bundles.

Nginx keeps the current document root (`public`) and `try_files $uri $uri/ /index.php?$query_string`. It serves existing assets and JSON files directly. Laravel's `FrontendController` serves the root or catch-all HTML shell for other URLs, and a fixed catch-all JSON payload for dynamic Void navigation requests. The browser's actual URL determines the React screen; the static shell's prerender parameters are not used. No confession list is fetched during a build.

Direct reloads and client navigation support `/home/`, `/popular/`, `/latest/`, `/category/:id/`, `/tag/:tag/`, `/search/:query/`, `/confession/:id/`, `/favourites/`, `/submit/`, `/mobile_submit`, and `/policy/`. Old `#!` links are converted in the browser. The existing Laravel confession route still returns crawler metadata to Facebook/search bots. API and admin routes retain priority over the SPA fallback.

Deploy PHP routes/views and generated assets as one release, using the existing release symlink process. Before switching, check the build keys, asset responses, direct deep links, admin login/date pickers, and real reCAPTCHA against an isolated environment. This frontend change requires no database migration.

## Browser verification

```sh
pnpm --filter @nuswhispers/web exec playwright install chromium
pnpm test:browser
```

The browser suite builds with explicit test-only public configuration and serves the generated files on `127.0.0.1:4173`. It mocks REST calls, the Facebook widget, and reCAPTCHA. It checks desktop/mobile navigation, safe text, pagination/retries, slow requests, hash links, direct reloads, submission validation, fingerprint persistence, daily limits, and the existing admin date pickers against their Vite bundles. It does not submit live confessions or exercise real third-party credentials. Run `pnpm build` afterward to restore ordinary release configuration in the local generated output.
