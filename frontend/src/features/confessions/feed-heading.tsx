import { Suspense, type ReactNode } from 'react';
import { useViews } from 'react-fate';
import { CategoryView } from '../../lib/api';
import type { FeedRoute } from '../../lib/routes';
import { useAppRequest } from '../../lib/use-app-request';

function ListingHeading({ children, subtitle }: { children: ReactNode; subtitle: string }) {
  return (
    <header className="mb-5 px-1 sm:px-0">
      <h1 className="text-2xl leading-tight font-normal">{children}</h1>
      <p className="mt-1.5 text-sm text-gray-500">{subtitle}</p>
    </header>
  );
}

function CategoryTitle({ id }: { id: string }) {
  const data = useAppRequest({ categories: { list: CategoryView } });
  const categories = useViews(CategoryView, data.categories);
  return categories.find((category) => category.id === id)?.name || 'Confessions';
}

function CategoryTitleSkeleton() {
  return (
    <>
      <span className="sr-only">Loading category…</span>
      <span
        className="block h-7.5 w-48 max-w-full animate-pulse rounded bg-gray-200 motion-reduce:animate-none"
        aria-hidden="true"
      />
    </>
  );
}

export function FeedHeading({ route }: { route: FeedRoute }) {
  if (route.kind === 'category')
    return (
      <ListingHeading subtitle="Stories from around campus.">
        <Suspense fallback={<CategoryTitleSkeleton />}>
          <CategoryTitle id={route.value || ''} />
        </Suspense>
      </ListingHeading>
    );
  if (route.kind === 'tag')
    return (
      <ListingHeading subtitle="A little corner of the conversation.">
        #{route.value}
      </ListingHeading>
    );
  if (route.kind === 'favourites')
    return (
      <ListingHeading subtitle="Stories worth coming back to.">Your favourites</ListingHeading>
    );
  if (route.kind === 'search') return null;
  return (
    <h1 className="sr-only">
      {route.kind === 'popular' ? 'Popular' : route.kind === 'recent' ? 'Latest' : 'Featured'}{' '}
      confessions
    </h1>
  );
}
