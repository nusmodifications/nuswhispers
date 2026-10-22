import { Dialog } from '@base-ui/react/dialog';
import { Link } from '@void/react';
import { Suspense, useState } from 'react';
import { useViews } from 'react-fate';
import brandIcon from '../assets/brand-icon.svg';
import brandWordmark from '../assets/brand-wordmark.svg';
import { ErrorBoundary } from '../components/error-boundary';
import { Icon } from '../components/icon';
import { Loading } from '../components/loading';
import { CategoryView } from '../lib/api';
import type { PageRoute } from '../lib/routes';
import { useAppRequest } from '../lib/use-app-request';

const navigation = [
  ['/', 'Featured', 'featured'],
  ['/latest/', 'Latest', 'recent'],
  ['/popular/', 'Popular', 'popular'],
] as const;
const navigationLinkClasses =
  'flex min-h-11 shrink-0 items-center px-3 text-xs font-medium text-gray-500 uppercase first:pl-0 hover:text-brand hover:no-underline aria-[current=page]:text-brand md:min-h-12';
const menuLinkClasses =
  'block py-2 text-sm text-gray-600 hover:text-brand aria-[current=page]:text-brand';
type NavigationProps = { close?: () => void; route: PageRoute; className: string };

function Brand({ close }: { close?: () => void }) {
  return (
    <Link
      className="inline-flex shrink-0 items-center gap-1.5 md:gap-2"
      href="/"
      onClick={close}
      aria-label="NUSWhispers home"
    >
      <img className="size-7 md:size-8" src={brandIcon} width={32} height={32} alt="" />
      <img
        className="h-7 w-24 md:w-28"
        src={brandWordmark}
        width={110}
        height={28}
        alt="NUSWhispers"
      />
    </Link>
  );
}

function CategoryLinks({ close, route, className }: NavigationProps) {
  const data = useAppRequest({ categories: { list: CategoryView } });
  const categories = useViews(CategoryView, data.categories);
  return categories.map((category) => (
    <Link
      key={category.id}
      className={className}
      href={`/category/${category.id}/`}
      onClick={close}
      aria-current={route.kind === 'category' && route.value === category.id ? 'page' : undefined}
    >
      {category.name}
    </Link>
  ));
}

function FeedLinks({ close, route, className }: NavigationProps) {
  return navigation.map(([href, label, kind]) => (
    <Link
      key={href}
      className={className}
      href={href}
      onClick={close}
      aria-current={route.kind === kind ? 'page' : undefined}
    >
      {label}
    </Link>
  ));
}

export function SiteHeader({ route }: { route: PageRoute }) {
  const [menuOpen, setMenuOpen] = useState(false);
  const closeMenu = () => setMenuOpen(false);
  return (
    <>
      <header className="bg-white">
        <div className="mx-auto flex min-h-16 w-full max-w-5xl items-center justify-between gap-2 px-3 sm:px-4 md:min-h-18 lg:px-3">
          <div className="flex items-center gap-2 md:gap-2.5">
            <Dialog.Root open={menuOpen} onOpenChange={setMenuOpen}>
              <Dialog.Trigger
                className="inline-flex size-8 items-center justify-center text-2xl text-gray-400 hover:text-brand md:hidden"
                aria-label="Open navigation"
              >
                <Icon name="th-menu" />
              </Dialog.Trigger>
              <Dialog.Portal>
                <Dialog.Backdrop className="fixed inset-0 z-20 bg-gray-800/25 opacity-100 transition-opacity duration-200 ease-out data-[ending-style]:opacity-0 data-[starting-style]:opacity-0 motion-reduce:transition-none" />
                <Dialog.Popup className="fixed inset-y-0 left-0 z-30 w-72 max-w-full translate-x-0 overflow-y-auto bg-white p-6 font-sans text-sm leading-relaxed text-gray-700 antialiased transition-transform duration-200 ease-out data-[ending-style]:-translate-x-full data-[starting-style]:-translate-x-full motion-reduce:transition-none">
                  <Dialog.Close
                    className="absolute top-4 right-4 inline-flex size-10 items-center justify-center text-2xl text-gray-400 hover:text-brand"
                    aria-label="Close navigation"
                  >
                    <Icon name="times" />
                  </Dialog.Close>
                  <Brand close={closeMenu} />
                  <Dialog.Title className="mt-7 mb-3 text-lg font-normal">
                    Have a wander
                  </Dialog.Title>
                  <nav className="space-y-5" aria-label="Mobile navigation">
                    <div className="space-y-0.5">
                      <FeedLinks className={menuLinkClasses} close={closeMenu} route={route} />
                      <Link className={menuLinkClasses} href="/search/" onClick={closeMenu}>
                        Search confessions
                      </Link>
                    </div>
                    <div className="space-y-0.5">
                      <h3 className="mb-2 text-xs tracking-wide text-gray-400 uppercase">
                        Categories
                      </h3>
                      <ErrorBoundary>
                        <Suspense fallback={<Loading variant="compact" />}>
                          <CategoryLinks
                            className={menuLinkClasses}
                            close={closeMenu}
                            route={route}
                          />
                        </Suspense>
                      </ErrorBoundary>
                    </div>
                    <div className="space-y-0.5">
                      <Link className={menuLinkClasses} href="/submit/" onClick={closeMenu}>
                        Post a confession
                      </Link>
                      <Link className={menuLinkClasses} href="/policy/" onClick={closeMenu}>
                        Privacy Policy
                      </Link>
                    </div>
                  </nav>
                </Dialog.Popup>
              </Dialog.Portal>
            </Dialog.Root>
            <Brand />
          </div>
          <div className="flex items-center gap-2 md:gap-6">
            <a
              className="hidden size-9 items-center justify-center text-2xl text-gray-400 hover:text-brand hover:no-underline md:inline-flex"
              href="https://github.com/nusmodifications/nuswhispers"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="NUSWhispers on GitHub"
            >
              <Icon name="social-github" />
            </a>
            <Link
              className="inline-flex size-8 items-center justify-center text-2xl text-gray-400 hover:text-brand hover:no-underline md:size-9"
              href="/search/"
              aria-label="Search confessions"
            >
              <Icon name="zoom" />
            </Link>
            <Link
              className="inline-flex min-h-9 shrink-0 items-center justify-center gap-1 rounded-full bg-brand px-3 py-2 text-xs leading-tight font-medium text-white uppercase hover:bg-sky-500 hover:no-underline md:px-5"
              href="/submit/"
              aria-label="Post Confession"
            >
              <span className="text-base md:hidden">
                <Icon name="pencil" />
              </span>
              <span className="hidden md:inline">Post Confession</span>
              <span className="md:hidden">Post</span>
            </Link>
          </div>
        </div>
      </header>
      <nav
        className="border-y border-t-gray-100 border-b-gray-200 bg-white"
        aria-label="Main navigation"
      >
        <div className="mx-auto flex w-full max-w-5xl items-center overflow-x-auto px-4 whitespace-nowrap [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:px-3">
          <FeedLinks className={navigationLinkClasses} route={route} />
          <ErrorBoundary>
            <Suspense fallback={null}>
              <CategoryLinks className={navigationLinkClasses} route={route} />
            </Suspense>
          </ErrorBoundary>
        </div>
      </nav>
    </>
  );
}
