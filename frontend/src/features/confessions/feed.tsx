import { Button } from '@base-ui/react/button';
import { Link } from '@void/react';
import { Suspense, useCallback, useEffect, useRef, useState } from 'react';
import { useListView } from 'react-fate';
import { EmptyState } from '../../components/empty-state';
import { Icon } from '../../components/icon';
import { Loading } from '../../components/loading';
import { ConfessionConnection, ConfessionView } from '../../lib/api';
import type { FeedRoute } from '../../lib/routes';
import { useAppRequest } from '../../lib/use-app-request';
import { ConfessionCard } from './confession-card';
import { FeedHeading } from './feed-heading';

type FeedProps = { route: FeedRoute; timestamp: number };

function SingleConfession({ id }: { id: string }) {
  const data = useAppRequest({ confessions: { id, view: ConfessionView } });
  return (
    <>
      <h1 className="sr-only">Confession #{id}</h1>
      <Link className="mb-4 ml-1 inline-flex items-center gap-1 text-xs" href="/">
        <Icon name="arrow-left" /> Back to confessions
      </Link>
      <ConfessionCard confessionRef={data.confessions} detailed />
    </>
  );
}

function PaginatedFeed({ route, timestamp }: FeedProps) {
  const data = useAppRequest({
    confessions: {
      args: { kind: route.kind, value: route.value, timestamp, first: 5 },
      list: ConfessionConnection,
    },
  });
  const [items, loadNext] = useListView(ConfessionConnection, data.confessions);
  const [loadingMore, setLoadingMore] = useState(false);
  const [error, setError] = useState('');
  const sentinel = useRef<HTMLDivElement>(null);
  const pending = useRef(false);

  const loadMore = useCallback(async () => {
    if (!loadNext || pending.current) return;
    pending.current = true;
    setLoadingMore(true);
    setError('');
    try {
      await loadNext();
    } catch (error) {
      setError(error instanceof Error ? error.message : 'Could not load more confessions.');
    } finally {
      pending.current = false;
      setLoadingMore(false);
    }
  }, [loadNext]);

  useEffect(() => {
    const element = sentinel.current;
    if (!element || !loadNext || error) return;
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries.some((entry) => entry.isIntersecting)) void loadMore();
      },
      { rootMargin: '300px' },
    );
    observer.observe(element);
    return () => observer.disconnect();
  }, [loadNext, loadMore, error]);

  return (
    <div>
      {!items?.length && (
        <EmptyState
          title={route.kind === 'search' ? 'Nothing here just yet.' : 'A little quiet around here.'}
        >
          {route.kind === 'search'
            ? 'Try a different word or a confession number. That story might be hiding somewhere.'
            : 'No confessions to display.'}{' '}
          <Link href="/submit/">How about submitting your own?</Link>
        </EmptyState>
      )}
      {items?.map(
        (item) => item.node && <ConfessionCard key={item.node.id} confessionRef={item.node} />,
      )}
      {error && (
        <p className="mb-4 bg-red-50 p-5 text-sm text-red-800" role="alert">
          {error}
        </p>
      )}
      {loadingMore && <Loading count={1} />}
      {loadNext && (
        <div ref={sentinel} className="pt-4 pb-3 text-center text-sm">
          <Button
            className="inline-flex items-center gap-1.5 text-sm text-brand hover:underline"
            onClick={loadMore}
            disabled={loadingMore}
            aria-label={error ? 'Try again' : 'Load more confessions'}
          >
            {error ? 'Try again' : 'More confessions'} <Icon name="arrow-down" />
          </Button>
        </div>
      )}
    </div>
  );
}

export function Feed({ route, timestamp }: FeedProps) {
  return route.kind === 'single' ? (
    <SingleConfession id={route.value || ''} />
  ) : (
    <>
      <FeedHeading route={route} />
      <Suspense fallback={<Loading />}>
        <PaginatedFeed route={route} timestamp={timestamp} />
      </Suspense>
    </>
  );
}
