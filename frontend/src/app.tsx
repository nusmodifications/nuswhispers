import { Link, useRouter } from '@void/react';
import { lazy, Suspense, useEffect, useState } from 'react';
import { FateClient } from 'react-fate';
import { EmptyState } from './components/empty-state';
import { ErrorBoundary } from './components/error-boundary';
import { Loading } from './components/loading';
import { Feed } from './features/confessions/feed';
import { Search } from './features/confessions/search';
import { SiteHeader } from './layout/site-header';
import { SiteSidebar } from './layout/site-sidebar';
import { createAppClient } from './lib/api';
import { legacyPath, parseRoute, type PageRoute } from './lib/routes';
import 'typicons.font/src/font/typicons.css';
import './styles.css';

const Submit = lazy(() =>
  import('./features/submissions/submit').then(({ Submit }) => ({ default: Submit })),
);
const Policy = lazy(() =>
  import('./features/privacy/policy').then(({ Policy }) => ({ default: Policy })),
);

function Screen({ route }: { route: PageRoute }) {
  // This state commits with the loading fallback and survives slow initial requests.
  const [timestamp] = useState(() => Math.floor(Date.now() / 1000));
  return (
    <ErrorBoundary>
      <Suspense
        fallback={
          <Loading
            variant={route.kind === 'submit' ? 'form' : route.kind === 'policy' ? 'page' : 'feed'}
          />
        }
      >
        {route.kind === 'submit' ? (
          <Submit />
        ) : route.kind === 'policy' ? (
          <Policy />
        ) : route.kind === 'search' ? (
          <Search route={route} timestamp={timestamp} />
        ) : route.kind === 'not-found' ? (
          <EmptyState title="Took a wrong turn?">
            This page could not be found. <Link href="/">Read the latest confessions.</Link>
          </EmptyState>
        ) : (
          <Feed route={route} timestamp={timestamp} />
        )}
      </Suspense>
    </ErrorBoundary>
  );
}

function Site() {
  const router = useRouter();
  // The shared static shell contains its prerender URL. The browser owns the actual deep link.
  const route = parseRoute(window.location.href);
  const routeKey = JSON.stringify(route);
  useEffect(() => {
    const normalize = () => {
      const path = legacyPath(window.location.hash);
      if (path) void router.visit(path, { replace: true });
    };
    normalize();
    window.addEventListener('hashchange', normalize);
    return () => window.removeEventListener('hashchange', normalize);
  }, [router]);
  useEffect(() => {
    document.title =
      route.kind === 'submit'
        ? 'Submit a Confession – NUSWhispers'
        : route.kind === 'policy'
          ? 'Privacy Policy – NUSWhispers'
          : route.kind === 'search'
            ? 'Search – NUSWhispers'
            : 'NUSWhispers';
  }, [route.kind]);

  return (
    <div className="min-h-dvh bg-gray-50 font-sans text-sm leading-relaxed text-gray-700 antialiased">
      <a
        className="sr-only focus:not-sr-only focus:fixed focus:top-2.5 focus:left-2.5 focus:z-50 focus:bg-white focus:p-2.5"
        href="#content"
      >
        Skip to content
      </a>
      <SiteHeader route={route} />
      <div className="mx-auto mt-5 mb-12 grid w-full max-w-5xl gap-8 px-3 md:mt-7 md:grid-cols-3 md:px-4 lg:gap-9 lg:px-3">
        <main id="content" className="min-w-0 md:col-span-2" tabIndex={-1}>
          <Screen key={routeKey} route={route} />
        </main>
        <SiteSidebar route={route} />
      </div>
    </div>
  );
}

export function App() {
  const [client] = useState(() => createAppClient());
  return (
    <FateClient client={client}>
      <Site />
    </FateClient>
  );
}
