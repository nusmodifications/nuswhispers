import { useRouter } from '@void/react';
import { Suspense, useState, type FormEvent } from 'react';
import { ErrorBoundary } from '../../components/error-boundary';
import { Icon } from '../../components/icon';
import { Loading } from '../../components/loading';
import type { FeedRoute } from '../../lib/routes';
import { Feed } from './feed';
import { SearchEmptyState } from './search-empty-state';

export function Search({ route, timestamp }: { route: FeedRoute; timestamp: number }) {
  const router = useRouter();
  const [query, setQuery] = useState(route.value || '');
  function search(event: FormEvent) {
    event.preventDefault();
    if (query.trim()) void router.visit(`/search/${encodeURIComponent(query.trim())}/`);
  }
  return (
    <>
      <h1 className="mb-3 px-1 text-2xl leading-tight font-normal sm:px-0">
        Looking for something?
      </h1>
      <search>
        <form className="mt-5 mb-6 flex items-center gap-2.5 bg-white px-4 py-3" onSubmit={search}>
          <span className="text-xl text-gray-400">
            <Icon name="zoom" />
          </span>
          <label htmlFor="search" className="sr-only">
            Search confessions
          </label>
          <input
            id="search"
            className="min-w-0 flex-1 border-0 bg-transparent text-base focus-visible:outline-none"
            type="search"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="A word, a story, a confession number…"
            // eslint-disable-next-line jsx-a11y/no-autofocus -- Search is the primary task on this page.
            autoFocus
            required
          />
          <button className="py-1 text-sm text-brand" type="submit">
            Search
          </button>
        </form>
      </search>
      {route.value ? (
        <>
          <p className="mb-4 px-1 text-xs text-gray-500 sm:px-0">
            Confessions about “{route.value}”
          </p>
          <ErrorBoundary>
            <Suspense fallback={<Loading />}>
              <Feed route={route} timestamp={timestamp} />
            </Suspense>
          </ErrorBoundary>
        </>
      ) : (
        <SearchEmptyState />
      )}
    </>
  );
}
