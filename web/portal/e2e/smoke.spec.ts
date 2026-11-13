import { expect, test } from '@playwright/test'

// Smoke tests against the running stack (no screenshots). The contact test leaves one message with the subject below.
const SUBJECT = 'e2e-smoke'

test('home loads with a heading and navigation', async ({ page }) => {
  await page.goto('/')
  await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  await expect(page.getByRole('navigation', { name: 'Main navigation' })).toBeVisible()
  await expect(page.locator('link[rel="canonical"]')).toHaveCount(1)
  await expect(page.locator('script[type="application/ld+json"]').first()).toBeAttached()
})

test('language switch', async ({ page }) => {
  await page.goto('/')
  const link = page.getByRole('list', { name: 'Language' }).getByRole('link').last()
  const lang = await link.getAttribute('lang')
  test.skip(!lang, 'only one locale is enabled')

  await link.click()
  await expect(page).toHaveURL(new RegExp(`/${lang}$`))
  await expect(page.locator('html')).toHaveAttribute('lang', lang!)
})

test('contact form submits', async ({ page }) => {
  await page.goto('/contact')
  await expect(page.getByRole('heading', { level: 1 })).toBeVisible()

  const label = page.locator('label[for="contact-captcha"]')
  await expect(page.locator('#contact-captcha')).toBeEnabled()
  const [a, b] = ((await label.textContent()) ?? '').match(/\d+/g)!.map(Number)

  await page.fill('#contact-name', 'E2E Smoke')
  await page.fill('#contact-email', 'e2e-smoke@example.com')
  await page.fill('#contact-subject', SUBJECT)
  await page.fill('#contact-message', 'Automated smoke test message.')
  await page.fill('#contact-captcha', String(a + b))
  await page.locator('form button[type="submit"]').click()

  await expect(page.getByRole('status')).toBeVisible()
})

test('blog detail', async ({ page }) => {
  await page.goto('/blog')
  await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  const first = page.locator('main a[href*="/blog/"]').first()
  test.skip((await first.count()) === 0, 'no published posts')

  await first.click()
  await expect(page).toHaveURL(/\/blog\/[a-z0-9-]+$/)
  await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  await expect(page.locator('script[type="application/ld+json"]')).toContainText('"Article"')
})
