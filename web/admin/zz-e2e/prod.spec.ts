import { expect, test } from '@playwright/test'

test('admin sign-in over TLS and dashboard', async ({ page }) => {
  const problems: string[] = []
  page.on('console', (m) => { if (/Content Security|Refused|violat/i.test(m.text())) problems.push(m.text().slice(0, 200)) })
  await page.goto('/')
  await page.waitForURL(/api\.prod\.localhost\/login/, { timeout: 30_000 })
  await page.getByLabel('Email').fill('admin@prod.localhost')
  await page.getByLabel('Password').fill('ProdTestAdmin1!x')
  await page.getByRole('button', { name: 'Sign in' }).click()
  await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible()
  await expect(page.getByRole('navigation', { name: 'Main navigation' })).toBeVisible()
  expect(problems).toEqual([])
})

test('portal over TLS: home, language, contact submit', async ({ page }) => {
  await page.goto('https://prod.localhost/')
  await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  await page.goto('https://prod.localhost/contact')
  await expect(page.locator('#contact-captcha')).toBeEnabled({ timeout: 30_000 })
  const [a, b] = ((await page.locator('label[for="contact-captcha"]').textContent()) ?? '').match(/\d+/g)!.map(Number)
  await page.fill('#contact-name', 'Prod Smoke')
  await page.fill('#contact-email', 'prod-smoke@example.com')
  await page.fill('#contact-subject', 'prod-smoke')
  await page.fill('#contact-message', 'Production stack smoke test.')
  await page.fill('#contact-captcha', String(a + b))
  await page.locator('form button[type="submit"]').click()
  await expect(page.getByRole('status')).toBeVisible()
})
