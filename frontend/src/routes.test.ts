import { describe, expect, it } from 'vitest';
import { feedEndpoint, legacyPath, parseRoute, safeUrl } from './routes';

describe('public URLs', () => {
  it.each([
    ['/', 'featured'],
    ['/home/', 'featured'],
    ['/latest/', 'recent'],
    ['/popular/', 'popular'],
    ['/mobile_submit', 'submit'],
    ['/submit/', 'submit'],
    ['/policy/', 'policy'],
    ['/confession/123/', 'single'],
    ['/favourites/', 'favourites'],
    ['/unknown/', 'not-found'],
  ])('keeps %s working', (url, kind) => expect(parseRoute(url).kind).toBe(kind));
  it('preserves encoded search terms and legacy hash links', () => {
    const route = parseRoute('/search/hello%20%26%20%23world/');
    expect(route).toEqual({ kind: 'search', value: 'hello & #world' });
    expect(feedEndpoint({ kind: 'search', value: 'hello & #world' })).toBe(
      '/confessions/search/hello%20%26%20%23world',
    );
    expect(legacyPath('#!tag/nus')).toBe('/tag/nus');
    expect(legacyPath('#!/submit')).toBe('/submit');
    expect(legacyPath('#story')).toBeNull();
  });
  it('only renders safe external image and link protocols', () => {
    expect(safeUrl('javascript:alert(1)')).toBeNull();
    expect(safeUrl('data:text/html,test')).toBeNull();
    expect(safeUrl('https://example.com/a?b=c')).toBe('https://example.com/a?b=c');
  });
});
