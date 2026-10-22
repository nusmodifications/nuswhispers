import { describe, expect, it, vi } from 'vitest';
import {
  createAppClient,
  createLaravelTransport,
  ConfessionConnection,
  ConfessionView,
  type RawConfession,
} from './api';

const confession = (id: number): RawConfession => ({
  confession_id: id,
  content: `Story ${id}`,
  categories: [{ confession_category_id: 7, confession_category: 'Life' }],
});
const json = (data: unknown, status = 200) =>
  new Response(JSON.stringify(data), { status, headers: { 'Content-Type': 'application/json' } });

describe('Laravel Fate transport', () => {
  it('maps the REST envelope, paginates with a fixed timestamp and deduplicates requests', async () => {
    const fetcher = vi
      .fn<typeof fetch>()
      .mockResolvedValueOnce(json({ data: { confessions: [confession(1), confession(2)] } }));
    const client = createAppClient(createLaravelTransport('/api', fetcher));
    const request = {
      confessions: {
        args: { kind: 'search', value: 'hello & #world', timestamp: 123, first: 2 },
        list: ConfessionConnection,
      },
    } as const;
    const [first, same] = await Promise.all([client.request(request), client.request(request)]);
    expect(fetcher).toHaveBeenCalledTimes(1);
    expect(first.confessions.items.map((item) => item.node.id)).toEqual(['1', '2']);
    expect(same.confessions.items).toHaveLength(2);
    const url = new URL(String(fetcher.mock.calls[0][0]), 'http://localhost');
    expect(url.pathname).toBe('/api/confessions/search/hello%20%26%20%23world');
    expect(Object.fromEntries(url.searchParams)).toEqual({
      timestamp: '123',
      offset: '0',
      count: '2',
    });
    const result = await createLaravelTransport(
      '/api',
      vi.fn<typeof fetch>().mockResolvedValue(json({ data: { confessions: [] } })),
    ).fetchList?.('confessions', [], {
      kind: 'search',
      value: 'hello',
      timestamp: 123,
      first: 2,
      after: '2',
    });
    expect(result?.pagination.hasNext).toBe(false);
  });

  it('normalizes single confessions and category records in Fate', async () => {
    const fetcher = vi
      .fn<typeof fetch>()
      .mockResolvedValue(json({ success: true, data: { confession: confession(42) } }));
    const client = createAppClient(createLaravelTransport('/api', fetcher));
    const result = await client.request({ confessions: { id: '42', view: ConfessionView } });
    expect(result.confessions.id).toBe('42');
    expect(fetcher.mock.calls[0][1]).toMatchObject({
      credentials: 'same-origin',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
  });

  it('encodes a submission using the existing Laravel form contract', async () => {
    const fetcher = vi.fn<typeof fetch>().mockResolvedValue(json({ success: true }));
    const client = createAppClient(createLaravelTransport('/api', fetcher));
    await client.mutations.submit({
      input: {
        content: 'Hello & goodbye',
        captcha: 'captcha',
        categories: ['2', '7'],
      },
    });
    expect(fetcher.mock.calls[0][0]).toBe('/api/confessions');
    const options = fetcher.mock.calls[0][1];
    expect(options?.method).toBe('POST');
    expect(Object.fromEntries(options?.body as URLSearchParams)).toEqual({
      content: 'Hello & goodbye',
      captcha: 'captcha',
      'categories[0]': '2',
      'categories[1]': '7',
    });
  });

  it.each([
    [
      422,
      {
        message: 'Invalid data.',
        errors: { content: ['Please enter a story.'], captcha: ['Complete CAPTCHA.'] },
      },
      'Please enter a story. Complete CAPTCHA.',
    ],
    [200, { success: false, errors: ['User not logged in.'] }, 'User not logged in.'],
    [429, {}, 'Too many requests.'],
  ])('surfaces HTTP and business failures (%s)', async (status, body, message) => {
    const transport = createLaravelTransport(
      '/api',
      vi.fn<typeof fetch>().mockResolvedValue(json(body, status)),
    );
    await expect(transport.fetchById('Confession', ['1'], [])).rejects.toThrow(message);
  });
});
