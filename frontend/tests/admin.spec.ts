import { expect, test } from '@playwright/test';

test('Vite admin date range assets initialize, apply, and clear', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', (error) => errors.push(error.message));
  await page.goto('/__test__/admin');
  await expect(page.locator('.date-picker .label')).toContainText('Jan 2024');
  await page.getByRole('link', { name: 'Clear' }).click();
  await expect(page.locator('input[name="start"]')).toHaveValue('');
  await expect(page.locator('input[name="end"]')).toHaveValue('');
  await page.locator('.date-picker').click();
  await expect(page.locator('.daterangepicker')).toBeVisible();
  await page.getByRole('button', { name: 'Apply', exact: true }).click();
  await expect(page.locator('input[name="start"]')).toHaveValue(/\d+/);
  await expect(page.getByRole('link', { name: 'Clear' })).toBeVisible();
  expect(errors).toEqual([]);
});

test('Vite confession editor initializes the schedule picker', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', (error) => errors.push(error.message));
  await page.goto('/__test__/admin?edit');
  await expect(page.locator('.scheduled-form-group')).toBeHidden();
  await page.locator('select[name="status"]').selectOption('Featured');
  await expect(page.locator('.scheduled-form-group')).toBeVisible();
  await page.locator('.date-picker').click();
  await expect(page.locator('.daterangepicker')).toBeVisible();
  await page.getByRole('button', { name: 'Apply', exact: true }).click();
  await expect(page.locator('input[name="schedule"]')).toHaveValue(/\d+/);
  await page.locator('select[name="status"]').selectOption('Pending');
  await expect(page.locator('.scheduled-form-group')).toBeHidden();
  expect(errors).toEqual([]);
});
