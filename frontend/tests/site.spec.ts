import { expect, test } from '@playwright/test';

const category = { confession_category_id: '7', confession_category: 'Life' };
const story = (id: number) => ({
  confession_id: String(id),
  content: `A story about #nus and <script>alert("xss")</script> https://example.com/`,
  categories: [category],
  status_updated_at_timestamp: 1792627200,
  fb_post_id: '123',
  favourites: [],
  facebook_information: { comments: { data: [{}] }, likes: { summary: { total_count: 12 } } },
});

test.beforeEach(async ({ page }) => {
  await page.route('https://www.facebook.com/**', (route) =>
    route.fulfill({ contentType: 'text/html', body: '<p>Facebook page</p>' }),
  );
  await page.route('**/api/**', (route) => {
    const url = new URL(route.request().url());
    if (url.pathname === '/api/categories')
      return route.fulfill({ json: { success: true, data: { categories: [category] } } });
    if (url.pathname === '/api/tags/top/5')
      return route.fulfill({
        json: {
          success: true,
          data: { tags: [{ confession_tag_id: '3', confession_tag: '#nus' }] },
        },
      });
    if (/^\/api\/confessions\/\d+$/.test(url.pathname))
      return route.fulfill({
        json: {
          success: true,
          data: { confession: story(Number(url.pathname.split('/').at(-1))) },
        },
      });
    if (url.pathname === '/api/confessions/favourites')
      return route.fulfill({ json: { success: false, errors: ['User not logged in.'] } });
    if (url.pathname.startsWith('/api/confessions')) {
      const offset = Number(url.searchParams.get('offset'));
      return route.fulfill({
        json: {
          data: {
            confessions:
              offset === 0
                ? Array.from({ length: 5 }, (_, index) => story(index + 1))
                : offset === 5
                  ? [story(6)]
                  : [],
          },
        },
      });
    }
    return route.fulfill({ status: 404, json: {} });
  });
});

test('renders safe content, shares, pagination, and native Void navigation', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', (error) => errors.push(error.message));
  await page.goto('/');
  await expect(page.getByRole('article').first()).toContainText('<script>alert("xss")</script>');
  await expect(
    page.getByRole('article').first().getByRole('link', { name: 'Share on Facebook' }),
  ).toHaveAttribute('href', /https%3A%2F%2Fnuswhispers.com/);
  await page.getByRole('button', { name: 'Load more confessions' }).scrollIntoViewIfNeeded();
  await expect(page.getByRole('article')).toHaveCount(6);
  await expect(page.getByRole('button', { name: 'Load more confessions' })).toHaveCount(0);
  await page.evaluate(() => {
    (window as unknown as { retainedMarker: string }).retainedMarker = 'same-document';
  });
  await page.getByRole('article').first().getByRole('link', { name: '#1', exact: true }).click();
  await expect(page).toHaveURL(/\/confession\/1\/?$/);
  expect(
    await page.evaluate(() => (window as unknown as { retainedMarker: string }).retainedMarker),
  ).toBe('same-document');
  await page.reload();
  await expect(page.getByRole('article')).toHaveCount(1);
  expect(errors).toEqual([]);
});

test('deep links, encoded search, old hash links, and history work', async ({
  page,
  isMobile,
}, testInfo) => {
  await page.goto('/category/7/');
  await expect(page.getByRole('article').first()).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Life', exact: true })).toBeVisible();
  if (isMobile) {
    await page.getByRole('button', { name: 'Open navigation' }).click();
    await page.getByRole('dialog').getByRole('link', { name: 'Search confessions' }).click();
    await expect(page.getByRole('dialog')).toHaveCount(0);
  } else {
    await page.getByRole('link', { name: 'Search confessions', exact: true }).click();
  }
  await expect(page.getByRole('searchbox')).toBeFocused();
  await expect(page.getByText('Start with a word, a phrase or a confession number.')).toBeVisible();
  await expect(page.getByRole('article')).toHaveCount(0);
  await page.getByRole('searchbox').fill('hello & #world');
  await page.getByRole('searchbox').press('Enter');
  await expect(page).toHaveURL(/\/search\/hello%20%26%20%23world\/?$/);
  await expect(page.getByRole('article').first()).toBeVisible();
  await page.goBack();
  await expect(page).toHaveURL(/\/search\/?$/);
  await page.goBack();
  await expect(page).toHaveURL(/\/category\/7\/?$/);
  await page.goto('/#!tag/nus');
  await expect(page).toHaveURL(/\/tag\/nus\/?$/);
  await expect(page.getByRole('article').first()).toBeVisible();
  await expect(page.getByRole('heading', { name: '#nus', exact: true })).toBeVisible();
  await page.goto('/mobile_submit');
  await expect(page.getByRole('heading', { name: 'Got something on your mind?' })).toBeVisible();
  await page.goto('/policy/');
  await expect(page.getByRole('heading', { name: 'Privacy Policy', exact: true })).toBeVisible();
  await page.goto('/search/');
  const searchbox = page.getByRole('searchbox');
  await expect(searchbox).toBeFocused();
  await expect(searchbox).toHaveCSS('outline-style', 'none');
  await expect(
    page.getByRole('navigation', { name: 'Main navigation', exact: true }).getByRole('link', {
      name: 'Featured',
      exact: true,
    }),
  ).toHaveAttribute('href', '/');
  await page.screenshot({ path: testInfo.outputPath('search.png'), fullPage: true });
});

test('navigation is accessible on the current viewport', async ({ page, isMobile }, testInfo) => {
  await page.goto('/');
  if (isMobile) {
    await page.getByRole('button', { name: 'Open navigation' }).click();
    await expect(page.getByRole('dialog')).toBeVisible();
    await page.getByRole('dialog').getByRole('link', { name: 'Popular', exact: true }).click();
    await expect(page.getByRole('dialog')).toHaveCount(0);
  } else {
    await page
      .getByRole('navigation', { name: 'Main navigation', exact: true })
      .getByRole('link', { name: 'Popular', exact: true })
      .click();
  }
  await expect(page).toHaveURL(/\/popular\/?$/);
  await expect(page.getByRole('article').first()).toBeVisible();
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(
    true,
  );
  await page.screenshot({ path: testInfo.outputPath('feed.png'), fullPage: true });
});

test('API errors are recoverable and do not block navigation', async ({ page }) => {
  await page.goto('/favourites/');
  await expect(page.getByRole('alert')).toContainText('User not logged in.');
  await page.getByRole('link', { name: 'Post Confession', exact: true }).last().click();
  await expect(page.getByRole('heading', { name: 'Got something on your mind?' })).toBeVisible();
});

test('submits through Fate, retries validation errors, and preserves the daily limit', async ({
  page,
}, testInfo) => {
  await page.route('https://www.google.com/recaptcha/api.js?**', (route) =>
    route.fulfill({
      contentType: 'text/javascript',
      body: `
    window.grecaptcha = {
      render(element, options) {
        const button = document.createElement('button');
        button.type = 'button'; button.textContent = 'Complete test CAPTCHA';
        button.onclick = () => options.callback('test-captcha-token');
        element.append(button); return 1;
      },
      reset() {}
    };
    window.nuswhispersCaptchaReady();
  `,
    }),
  );
  const submissions: URLSearchParams[] = [];
  await page.route('**/api/confessions', async (route) => {
    if (route.request().method() !== 'POST') return route.fallback();
    submissions.push(new URLSearchParams(route.request().postData() || ''));
    if (submissions.length === 1)
      return route.fulfill({
        status: 422,
        json: { errors: { captcha: ['Please try reCAPTCHA again.'] } },
      });
    return route.fulfill({ json: { success: true, 'browser-test-fingerprint': 'new-token' } });
  });
  await page.goto('/submit/');
  await page.getByLabel('Your story').fill('  nus whispers and nus mods #nus  ');
  await page.getByRole('checkbox', { name: 'Life' }).check();
  await page.getByRole('button', { name: 'Complete test CAPTCHA' }).click();
  await page.getByRole('button', { name: 'Send it our way', exact: true }).click();
  await expect(page.getByRole('alert')).toContainText('Please try reCAPTCHA again.');
  expect(await page.evaluate(() => localStorage.getItem('nuswhispers.confessionLimit.count'))).toBe(
    '3',
  );
  await expect(page.getByRole('button', { name: 'Send it our way', exact: true })).toBeDisabled();
  await page.getByRole('button', { name: 'Complete test CAPTCHA' }).click();
  await page.screenshot({ path: testInfo.outputPath('submit.png'), fullPage: true });
  await page.getByRole('button', { name: 'Send it our way', exact: true }).click();
  await expect(page.getByRole('status')).toContainText('Thanks for telling us your story!');
  expect(submissions[1].get('content')).toBe('NUSWhispers and NUSMods #nus');
  expect(submissions[1].get('categories[0]')).toBe('7');
  expect(submissions[1].get('captcha')).toBe('test-captcha-token');
  expect(await page.evaluate(() => localStorage.getItem('nuswhispers.confessionLimit.count'))).toBe(
    '2',
  );
  expect(
    await page.evaluate(() => localStorage.getItem('nuswhispers.browser-test-fingerprint')),
  ).toBe('"new-token"');
});

test('slow initial feeds keep the same pagination timestamp', async ({ page }, testInfo) => {
  const requests: string[] = [];
  await page.route('**/api/confessions?**', async (route) => {
    requests.push(route.request().url());
    await new Promise((resolve) => setTimeout(resolve, 1250));
    return route.fulfill({ json: { data: { confessions: [story(1)] } } });
  });
  await page.goto('/');
  const loading = page.getByRole('status').filter({ hasText: 'Loading confessions…' });
  await expect(loading).toBeVisible();
  await page.screenshot({ path: testInfo.outputPath('loading.png'), animations: 'disabled' });
  await expect(page.getByRole('article')).toHaveCount(1);
  await expect(loading).toHaveCount(0);
  expect(requests).toHaveLength(1);
});

test('category headings retain their layout while the title and feed load', async ({
  page,
}, testInfo) => {
  let releaseCategories: (() => void) | undefined;
  let releaseFeed: (() => void) | undefined;
  const categoriesPending = new Promise<void>((resolve) => {
    releaseCategories = resolve;
  });
  const feedPending = new Promise<void>((resolve) => {
    releaseFeed = resolve;
  });
  await page.route('**/api/categories', async (route) => {
    await categoriesPending;
    await route.fallback();
  });
  await page.route('**/api/confessions/category/7?**', async (route) => {
    await feedPending;
    await route.fulfill({ json: { data: { confessions: [story(1)] } } });
  });
  try {
    await page.goto('/category/7/');
    const main = page.getByRole('main');
    const loading = main.getByRole('status').filter({ hasText: 'Loading confessions…' });
    await expect(main.getByRole('heading', { name: 'Loading category…' })).toBeVisible();
    await expect(main.getByText('Stories from around campus.')).toBeVisible();
    await expect(loading).toBeVisible();
    const initialFeedTop = (await loading.boundingBox())?.y;
    await page.screenshot({
      path: testInfo.outputPath('category-title-loading.png'),
      animations: 'disabled',
    });

    releaseCategories?.();
    await expect(main.getByRole('heading', { name: 'Life', exact: true })).toBeVisible();
    await expect(loading).toBeVisible();
    expect((await loading.boundingBox())?.y).toBe(initialFeedTop);
    await page.screenshot({
      path: testInfo.outputPath('category-loading.png'),
      animations: 'disabled',
    });

    releaseFeed?.();
    await expect(main.getByRole('article')).toHaveCount(1);
    await expect(loading).toHaveCount(0);
    expect((await main.getByRole('article').boundingBox())?.y).toBe(initialFeedTop);
    await expect(main.getByRole('heading', { name: 'Life', exact: true })).toHaveCount(1);
  } finally {
    releaseCategories?.();
    releaseFeed?.();
  }
});

test('a failed later page retains the feed and can be retried', async ({ page }) => {
  let failed = false;
  await page.route('**/api/confessions?**', (route) => {
    const offset = new URL(route.request().url()).searchParams.get('offset');
    if (offset === '0') return route.fallback();
    if (!failed) {
      failed = true;
      return route.fulfill({ status: 503, json: { message: 'Temporarily unavailable.' } });
    }
    return route.fulfill({ json: { data: { confessions: [story(6)] } } });
  });
  await page.goto('/');
  await page.getByRole('button', { name: 'Load more confessions' }).scrollIntoViewIfNeeded();
  await expect(page.getByRole('alert')).toContainText('Temporarily unavailable.');
  await expect(page.getByRole('article')).toHaveCount(5);
  await page.getByRole('button', { name: 'Try again', exact: true }).click();
  await expect(page.getByRole('article')).toHaveCount(6);
});
