export type FeedRoute = {
  kind: 'featured' | 'popular' | 'recent' | 'category' | 'tag' | 'search' | 'single' | 'favourites';
  value?: string;
};

export type PageRoute = FeedRoute | { kind: 'submit' } | { kind: 'policy' } | { kind: 'not-found' };

function decode(value: string) {
  try {
    return decodeURIComponent(value);
  } catch {
    return value;
  }
}

export function legacyPath(hash: string): string | null {
  if (!hash.startsWith('#!')) return null;
  const path = hash.slice(2).replace(/^\/+/, '');
  return path ? `/${path}` : '/home/';
}

export function parseRoute(url: string | URL): PageRoute {
  const pathname = new URL(url, 'http://localhost').pathname.replace(/\/+$/, '') || '/';
  const names: Record<string, PageRoute> = {
    '/': { kind: 'featured' },
    '/home': { kind: 'featured' },
    '/popular': { kind: 'popular' },
    '/latest': { kind: 'recent' },
    '/submit': { kind: 'submit' },
    '/mobile_submit': { kind: 'submit' },
    '/policy': { kind: 'policy' },
    '/favourites': { kind: 'favourites' },
  };
  if (names[pathname]) return names[pathname];
  const match = /^\/(category|tag|search|confession)\/(.+)$/.exec(pathname);
  if (!match) return { kind: 'not-found' };
  const value = decode(match[2]);
  return { kind: match[1] === 'confession' ? 'single' : (match[1] as FeedRoute['kind']), value };
}

export function feedEndpoint(route: FeedRoute) {
  if (route.kind === 'featured') return '/confessions';
  if (route.kind === 'single') return `/confessions/${encodeURIComponent(route.value || '')}`;
  const suffix = ['category', 'tag', 'search'].includes(route.kind)
    ? `/${encodeURIComponent(route.value || '')}`
    : '';
  return `/confessions/${route.kind}${suffix}`;
}

export function safeUrl(value: string | null | undefined): string | null {
  if (!value) return null;
  try {
    const url = new URL(value);
    return ['https:', 'http:'].includes(url.protocol) ? url.href : null;
  } catch {
    return null;
  }
}
