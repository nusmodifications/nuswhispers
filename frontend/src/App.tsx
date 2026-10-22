import { Dialog } from '@base-ui/react/dialog';
import { Link, useRouter } from '@void/react';
import { lazy, Suspense, useEffect, useState, type FormEvent } from 'react';
import { FateClient, useViews } from 'react-fate';
import { CategoryView, createAppClient, TagView } from './api';
import logo from './assets/logo.png';
import { ErrorBoundary, Icon, Loading } from './components';
import Feed from './Feed';
import { legacyPath, parseRoute, type PageRoute } from './routes';
import { useAppRequest } from './useAppRequest';
import 'typicons.font/src/font/typicons.css';
import './styles.css';

const Submit = lazy(() => import('./Submit'));
const Policy = lazy(() => import('./Policy'));
const navigation = [
  ['/submit/', 'heart-full-outline', 'Post Confession', 'submit'],
  ['/home/', 'pin', 'Featured', 'featured'],
  ['/popular/', 'chart-line', 'Popular', 'popular'],
  ['/latest/', 'starburst', 'Latest', 'recent'],
] as const;

function Directory({ close, route }: { close?: () => void; route: PageRoute }) {
  const data = useAppRequest({ categories: { list: CategoryView }, tags: { list: TagView } });
  const categories = useViews(CategoryView, data.categories);
  const tags = useViews(TagView, data.tags);
  return (
    <>
      <hr />
      <h2 className="nav-section-label">Categories</h2>
      <ul className="directory">
        {categories.map((category) => (
          <li key={category.id}>
            <Link
              href={`/category/${category.id}/`}
              onClick={close}
              aria-current={
                route.kind === 'category' && route.value === category.id ? 'page' : undefined
              }
            >
              {category.name}
            </Link>
          </li>
        ))}
      </ul>
      <hr />
      <h2 className="nav-section-label">Popular Tags</h2>
      <ul className="directory">
        {tags.map((tag) => (
          <li key={tag.id}>
            <Link
              href={`/tag/${encodeURIComponent(tag.name.replace(/^#/, ''))}/`}
              title={tag.name}
              onClick={close}
              aria-current={
                route.kind === 'tag' && route.value === tag.name.replace(/^#/, '')
                  ? 'page'
                  : undefined
              }
            >
              {tag.name.length > 20 ? `${tag.name.slice(0, 17)}…` : tag.name}
            </Link>
          </li>
        ))}
      </ul>
    </>
  );
}

function Navigation({ close, route }: { close?: () => void; route: PageRoute }) {
  return (
    <>
      <Link className="brand" href="/home/" onClick={close}>
        <img src={logo} alt="" width={30} height={30} />
        NUS<span>Whispers</span>
      </Link>
      <hr />
      <ul className="main-nav">
        {navigation.map(([href, icon, label, kind]) => (
          <li key={href}>
            <Link
              href={href}
              aria-current={route.kind === kind ? 'page' : undefined}
              onClick={close}
            >
              <Icon name={icon} /> {label}
            </Link>
          </li>
        ))}
      </ul>
      <ErrorBoundary>
        <Suspense fallback={<Loading />}>
          <Directory close={close} route={route} />
        </Suspense>
      </ErrorBoundary>
    </>
  );
}

function Screen({ route }: { route: PageRoute }) {
  // This state commits with the loading fallback and survives slow initial requests.
  const [timestamp] = useState(() => Math.floor(Date.now() / 1000));
  return (
    <ErrorBoundary>
      <Suspense fallback={<Loading />}>
        {route.kind === 'submit' ? (
          <Submit />
        ) : route.kind === 'policy' ? (
          <Policy />
        ) : route.kind === 'not-found' ? (
          <p className="notice">
            This page could not be found. <Link href="/home/">Read the latest confessions.</Link>
          </p>
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
  const [menuOpen, setMenuOpen] = useState(false);
  const [query, setQuery] = useState('');
  const [year] = useState(() => new Date().getFullYear());
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
          : 'NUSWhispers';
  }, [route.kind]);

  function search(event: FormEvent) {
    event.preventDefault();
    if (query.trim()) void router.visit(`/search/${encodeURIComponent(query.trim())}/`);
  }

  return (
    <>
      <a className="skip-link" href="#content">
        Skip to content
      </a>
      <nav className="sidebar desktop-sidebar" aria-label="Main navigation">
        <Navigation route={route} />
      </nav>
      <div className="site-main">
        <header className="topbar">
          <Dialog.Root open={menuOpen} onOpenChange={setMenuOpen}>
            <Dialog.Trigger className="menu-toggle button" aria-label="Open navigation">
              <Icon name="th-menu" />
            </Dialog.Trigger>
            <Dialog.Portal>
              <Dialog.Backdrop className="menu-backdrop" />
              <Dialog.Popup className="sidebar mobile-sidebar">
                <Dialog.Title className="sr-only">Navigation</Dialog.Title>
                <Dialog.Close className="menu-close" aria-label="Close navigation">
                  ×
                </Dialog.Close>
                <nav aria-label="Mobile navigation">
                  <Navigation route={route} close={() => setMenuOpen(false)} />
                </nav>
              </Dialog.Popup>
            </Dialog.Portal>
          </Dialog.Root>
          <search className="search-form">
            <form onSubmit={search} className="flex w-full items-center gap-1">
              <label htmlFor="search" className="sr-only">
                Search confessions
              </label>
              <Icon name="zoom" />
              <input
                id="search"
                placeholder="Search"
                value={query}
                onChange={(event) => setQuery(event.target.value)}
                type="search"
              />
            </form>
          </search>
          <div className="topbar-actions">
            <a
              className="github-link"
              href="https://github.com/nusmodifications/nuswhispers"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="NUSWhispers on GitHub"
            >
              <Icon name="social-github" />
            </a>
            <Link className="button button-accent" href="/submit/">
              Post Confession <Icon name="heart-full-outline" />
            </Link>
          </div>
        </header>
        <div className="content-grid">
          <main id="content" className="min-w-0" tabIndex={-1}>
            <Screen key={routeKey} route={route} />
          </main>
          <aside className="likebox">
            <iframe
              title="NUSWhispers on Facebook"
              src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Fnuswhispers%2F&tabs=&width=300&height=130&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true"
              width="300"
              height="130"
              className="mx-auto max-w-full border-0"
              loading="lazy"
              allow="encrypted-media; picture-in-picture"
            />
            <footer className="site-footer">
              NUSWhispers © 2015 – {year} • <Link href="/policy/">Privacy Policy</Link> •{' '}
              <a href="mailto:admin@whispers.sg">Feedback</a>
            </footer>
          </aside>
        </div>
      </div>
    </>
  );
}

export default function App() {
  const [client] = useState(() => createAppClient());
  return (
    <FateClient client={client}>
      <Site />
    </FateClient>
  );
}
