import { Link } from '@void/react';
import { Suspense, useState } from 'react';
import { useViews } from 'react-fate';
import { ErrorBoundary } from '../components/error-boundary';
import { Icon } from '../components/icon';
import { Loading } from '../components/loading';
import { TagView } from '../lib/api';
import type { PageRoute } from '../lib/routes';
import { useAppRequest } from '../lib/use-app-request';

function TagLinks({ route }: { route: PageRoute }) {
  const data = useAppRequest({ tags: { list: TagView } });
  const tags = useViews(TagView, data.tags);
  return (
    <div className="flex flex-wrap gap-x-3 gap-y-1.5 text-sm leading-7 [&_a]:max-w-full [&_a]:wrap-anywhere">
      {tags.map((tag) => (
        <Link
          key={tag.id}
          href={`/tag/${encodeURIComponent(tag.name.replace(/^#/, ''))}/`}
          aria-current={
            route.kind === 'tag' && route.value === tag.name.replace(/^#/, '') ? 'page' : undefined
          }
        >
          {tag.name.startsWith('#') ? tag.name : `#${tag.name}`}
        </Link>
      ))}
    </div>
  );
}

export function SiteSidebar({ route }: { route: PageRoute }) {
  const [year] = useState(() => new Date().getFullYear());
  return (
    <aside className="px-2 pt-2.5 md:pt-1 md:pr-0 md:pl-1" aria-label="Around NUSWhispers">
      <section className="mb-7 hidden md:block">
        <h2 className="mb-2 text-sm font-medium">What's on your mind?</h2>
        <p className="text-sm leading-7 text-gray-500 [&_a]:mt-2 [&_a]:inline-block">
          A little campus life, a little oversharing.
          <br />
          Everyone has a story.
          <br />
          <Link href="/submit/">
            Tell us yours <Icon name="arrow-right" />
          </Link>
        </p>
      </section>
      <section className="mb-7">
        <h2 className="mb-2 text-sm font-medium">Wander around</h2>
        <ErrorBoundary>
          <Suspense fallback={<Loading variant="compact" />}>
            <TagLinks route={route} />
          </Suspense>
        </ErrorBoundary>
      </section>
      <section className="mb-7">
        <h2 className="mb-2 text-sm font-medium">Find us on Facebook</h2>
        <p className="text-sm leading-7 text-gray-500 [&_a]:mt-2 [&_a]:inline-block">
          More stories. More comments.
          <br />
          The same NUSWhispers.
          <br />
          <a href="https://www.facebook.com/nuswhispers" target="_blank" rel="noopener noreferrer">
            Come say hi <Icon name="social-facebook" />
          </a>
        </p>
      </section>
      <footer className="mt-8 text-xs leading-loose text-gray-500">
        <p>© 2015 – {year} NUSWhispers</p>
        <div className="mt-1 flex flex-wrap gap-3 [&_a]:text-gray-500">
          <Link href="/policy/">Privacy Policy</Link>
          <a href="mailto:admin@whispers.sg">Contact</a>
          <a
            href="https://github.com/nusmodifications/nuswhispers"
            target="_blank"
            rel="noopener noreferrer"
          >
            GitHub
          </a>
        </div>
      </footer>
    </aside>
  );
}
