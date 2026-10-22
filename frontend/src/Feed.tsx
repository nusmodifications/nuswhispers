import { Button } from '@base-ui/react/button';
import { Link } from '@void/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useFateClient, useListView, useView, useViews, type ViewRef } from 'react-fate';
import { ConfessionConnection, ConfessionView, type AppSchema } from './api';
import { Icon, Loading } from './components';
import { config } from './config';
import { safeUrl, type FeedRoute } from './routes';
import { useAppRequest } from './useAppRequest';
import { CategoryView } from './api';

function Story({ content }: { content: string }) {
  const tokens = content.split(/(https?:\/\/[^\s<>]+|#\w+)/g);
  return (
    <>
      {tokens.map((token, index) => {
        const key = `${index}:${token}`;
        if (/^https?:\/\//i.test(token))
          return (
            <a
              key={key}
              href={safeUrl(token) || undefined}
              target="_blank"
              rel="noopener noreferrer"
            >
              {token}
            </a>
          );
        if (/^#\w+$/.test(token))
          return (
            <Link key={key} href={`/tag/${encodeURIComponent(token.slice(1))}/`}>
              {token}
            </Link>
          );
        return <span key={key}>{token}</span>;
      })}
    </>
  );
}

function relativeDate(timestamp: number) {
  if (!timestamp) return '';
  const elapsed = Math.max(0, Math.floor(Date.now() / 1000 - timestamp));
  const format = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
  for (const [unit, seconds] of [
    ['year', 31536000],
    ['month', 2592000],
    ['day', 86400],
    ['hour', 3600],
    ['minute', 60],
  ] as const) {
    if (elapsed >= seconds) return format.format(-Math.floor(elapsed / seconds), unit);
  }
  return 'just now';
}

export function ConfessionCard({ confessionRef }: { confessionRef: ViewRef<'Confession'> }) {
  const confession = useView(ConfessionView, confessionRef);
  const categories = useViews(CategoryView, confession.categories);
  const client = useFateClient<AppSchema>();
  const [favouriteError, setFavouriteError] = useState('');
  const [saving, setSaving] = useState(false);
  const permalink = `https://nuswhispers.com/confession/${confession.id}/`;
  const facebook = confession.fbPostId
    ? `https://www.facebook.com/permalink.php?${new URLSearchParams({ story_fbid: confession.fbPostId, id: config.facebookPageId })}`
    : `https://www.facebook.com/nuswhispers/`;
  const image = safeUrl(confession.image);

  async function favourite() {
    setSaving(true);
    setFavouriteError('');
    try {
      await client.mutations.favourite({ input: { id: String(confession.id), remove: false } });
      await client.request(
        { confessions: { id: confession.id, view: ConfessionView } },
        { mode: 'network-only' },
      );
    } catch (error) {
      setFavouriteError(error instanceof Error ? error.message : 'Could not save this favourite.');
    } finally {
      setSaving(false);
    }
  }

  return (
    <article className="post" aria-label={`Confession ${confession.id}`}>
      <header className="post-header">
        <span>
          {categories.length
            ? categories.map((category, index) => (
                <span key={category.id}>
                  {index > 0 && ', '}
                  <Link href={`/category/${category.id}/`}>{category.name}</Link>
                </span>
              ))
            : 'No Category'}
        </span>
        <div className="share-links">
          <span>SHARE:</span>
          <a
            href={`https://twitter.com/intent/tweet?${new URLSearchParams({ url: permalink })}`}
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Share on Twitter"
          >
            <Icon name="social-twitter" />
          </a>
          <a
            href={`https://www.facebook.com/sharer/sharer.php?${new URLSearchParams({ u: permalink })}`}
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Share on Facebook"
          >
            <Icon name="social-facebook" />
          </a>
        </div>
      </header>
      <div className="post-content">
        <Link href={`/confession/${confession.id}/`}>#{confession.id}</Link>:{' '}
        <span className="whitespace-pre-wrap break-words">
          <Story content={confession.content} />
        </span>
        {image && (
          <img
            className="mt-3 max-h-[700px] max-w-full object-contain"
            src={image}
            alt={`Attachment to confession ${confession.id}`}
            loading="lazy"
          />
        )}
        <div className="mt-3 text-right">
          <a href={facebook} target="_blank" rel="noopener noreferrer">
            View on Facebook
          </a>
        </div>
      </div>
      <footer className="post-footer">
        <span className="post-time">
          <Icon name="watch" />
          <time
            dateTime={
              confession.updatedAt ? new Date(confession.updatedAt * 1000).toISOString() : undefined
            }
          >
            {relativeDate(confession.updatedAt)}
          </time>
        </span>
        <div className="post-actions">
          <a
            href={facebook}
            target="_blank"
            rel="noopener noreferrer"
            aria-label={`${confession.comments} comments on Facebook`}
            title="View comments on Facebook"
          >
            <Icon name="messages" />
            {confession.comments}
          </a>
          <a
            href={facebook}
            target="_blank"
            rel="noopener noreferrer"
            aria-label={`${confession.likes} likes on Facebook`}
            title="Like on Facebook"
          >
            <Icon name="thumbs-up" />
            {confession.likes}
          </a>
          <Button
            className="favourite-button"
            disabled={saving}
            onClick={favourite}
            aria-label="Favourite confession"
            title="Favourite (requires a Facebook session)"
          >
            <Icon name="star-outline" />
            {confession.favouriteIds.length}
          </Button>
        </div>
      </footer>
      {favouriteError && (
        <p className="pb-3 text-red-700" role="alert">
          {favouriteError}
        </p>
      )}
    </article>
  );
}

export default function Feed({ route, timestamp }: { route: FeedRoute; timestamp: number }) {
  const request =
    route.kind === 'single'
      ? { confessions: { id: route.value || '', view: ConfessionView } }
      : {
          confessions: {
            args: { kind: route.kind, value: route.value, timestamp, first: 5 },
            list: ConfessionConnection,
          },
        };
  const data = useAppRequest(request);
  // Hooks remain unconditional; a single confession has no connection to paginate.
  const connection =
    'items' in (data.confessions || {})
      ? (data.confessions as import('react-fate').ConnectionRef<'Confession'>)
      : undefined;
  const [items, loadNext] = useListView(ConfessionConnection, connection);
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

  if (route.kind === 'single')
    return <ConfessionCard confessionRef={data.confessions as ViewRef<'Confession'>} />;
  return (
    <div>
      {route.kind === 'search' && <h1 className="sr-only">Search results for {route.value}</h1>}
      {!items?.length && (
        <p className="notice">
          No confessions to display. <Link href="/submit/">How about submitting your own?</Link>
        </p>
      )}
      {items?.map(
        (item) => item.node && <ConfessionCard key={item.node.id} confessionRef={item.node} />,
      )}
      {error && (
        <p className="notice error" role="alert">
          {error}
        </p>
      )}
      {loadingMore && <Loading />}
      {loadNext && (
        <div ref={sentinel} className="pb-5 text-center">
          <Button className="button" onClick={loadMore} disabled={loadingMore}>
            {error ? 'Try again' : 'Load more confessions'}
          </Button>
        </div>
      )}
    </div>
  );
}
