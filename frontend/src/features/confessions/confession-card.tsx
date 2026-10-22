import { Button } from '@base-ui/react/button';
import { Link } from '@void/react';
import { useState } from 'react';
import { useFateClient, useView, useViews, type ViewRef } from 'react-fate';
import { Icon } from '../../components/icon';
import { CategoryView, ConfessionView, type AppSchema } from '../../lib/api';
import { config } from '../../lib/config';
import { safeUrl } from '../../lib/routes';

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

export function ConfessionCard({
  confessionRef,
  detailed = false,
}: {
  confessionRef: ViewRef<'Confession'>;
  detailed?: boolean;
}) {
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
    <>
      <article
        className="mb-4 bg-white p-5 sm:p-6 lg:px-7"
        aria-label={`Confession ${confession.id}`}
      >
        <header className="flex items-center justify-between gap-3 text-xs">
          <div className="flex flex-wrap gap-2 text-gray-500">
            {categories.length
              ? categories.map((category) => (
                  <Link key={category.id} href={`/category/${category.id}/`}>
                    {category.name}
                  </Link>
                ))
              : 'No Category'}
          </div>
          <div className="flex shrink-0 items-center gap-2 text-lg text-gray-400 [&_a]:inline-flex [&_a]:min-h-8 [&_a]:items-center [&_a]:text-gray-400 [&_a:hover]:text-brand">
            <span className="hidden text-xs md:inline">SHARE</span>
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
        <div className="my-4 text-base leading-7 wrap-anywhere sm:my-5">
          <Link className="font-medium" href={`/confession/${confession.id}/`}>
            #{confession.id}
          </Link>
          :{' '}
          <span className="whitespace-pre-wrap wrap-anywhere">
            <Story content={confession.content} />
          </span>
          {image && (
            <img
              className="mt-3 max-h-176 max-w-full object-contain"
              src={image}
              alt={`Attachment to confession ${confession.id}`}
              loading="lazy"
            />
          )}
        </div>
        <footer className="flex items-center justify-between gap-3 text-xs text-gray-500">
          <span className="inline-flex min-w-0 items-center whitespace-nowrap">
            <time
              dateTime={
                confession.updatedAt
                  ? new Date(confession.updatedAt * 1000).toISOString()
                  : undefined
              }
            >
              {relativeDate(confession.updatedAt)}
            </time>
          </span>
          <div className="flex items-center gap-3 text-xs [&_.typcn]:text-xl">
            <a
              className="inline-flex min-h-9 items-center justify-center gap-1 text-gray-500 hover:text-brand hover:no-underline"
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
              className="inline-flex min-h-9 items-center justify-center gap-1 text-gray-500 hover:text-brand hover:no-underline"
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
              className="inline-flex min-h-9 items-center justify-center gap-1 text-gray-500 hover:text-brand"
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
          <p className="pt-2 text-sm text-red-700" role="alert">
            {favouriteError}
          </p>
        )}
      </article>
      {detailed && (
        <a
          className="mb-6 ml-1 inline-flex flex-wrap items-center gap-1.5 text-sm"
          href={facebook}
          target="_blank"
          rel="noopener noreferrer"
        >
          <Icon name="social-facebook" /> Join the conversation on Facebook{' '}
          <Icon name="arrow-right" />
        </a>
      )}
    </>
  );
}
