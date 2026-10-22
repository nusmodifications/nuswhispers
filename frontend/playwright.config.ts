import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  timeout: 30_000,
  fullyParallel: true,
  use: { baseURL: 'http://127.0.0.1:4173', trace: 'retain-on-failure' },
  projects: [
    { name: 'desktop', use: { ...devices['Desktop Chrome'] } },
    {
      name: 'mobile',
      testIgnore: '**/admin.spec.ts',
      use: { ...devices['iPhone 13'], defaultBrowserType: 'chromium' },
    },
  ],
  webServer: {
    command:
      'VITE_RECAPTCHA_KEY=browser-test-site-key VITE_FINGERPRINT_API_KEY=browser-test-fingerprint VITE_FINGERPRINT_STORAGE_KEY=browser-test-fingerprint pnpm --workspace-root build && node tests/preview.mjs',
    url: 'http://127.0.0.1:4173',
    reuseExistingServer: false,
  },
});
