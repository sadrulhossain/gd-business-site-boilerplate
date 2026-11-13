import { expect, test } from '@playwright/test'

test('home in the default language', async ({ page }) => {
  await page.goto('/')
  await expect(page.locator('html')).toHaveAttribute('dir', 'ltr')
  await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  await expect(page.getByRole('navigation', { name: 'Main navigation' })).toBeVisible()
  await page.screenshot({ path: 'e2e/screenshots/home-default.png', fullPage: true })
})

test('home in the second language', async ({ page }) => {
  await page.goto('/')
  const link = page.getByRole('list', { name: 'Language' }).getByRole('link').last()
  const lang = await link.getAttribute('lang')
  test.skip(!lang, 'only one locale is enabled')

  await link.click()
  await expect(page).toHaveURL(new RegExp(`/${lang}$`))
  await expect(page.locator('html')).toHaveAttribute('lang', lang!)
  await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  await page.screenshot({ path: 'e2e/screenshots/home-second.png', fullPage: true })
})
