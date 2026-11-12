import { expect, test } from '@playwright/test'

const email = process.env.E2E_EMAIL ?? ''
const password = process.env.E2E_PASSWORD ?? ''

test('sign in through the API login page and land on the dashboard', async ({ page }) => {
  test.skip(!email || !password, 'set E2E_EMAIL and E2E_PASSWORD')

  await page.goto('/')

  // The guard sent us to the API's login page (PKCE authorize -> login).
  await page.waitForURL(/\/login/, { timeout: 30_000 })
  await expect(page.getByRole('heading', { name: 'Sign in' })).toBeVisible({ timeout: 15_000 })
  await page.screenshot({ path: 'e2e/screenshots/login.png' })

  await page.getByLabel('Email').fill(email)
  await page.getByLabel('Password').fill(password)
  await page.getByRole('button', { name: 'Sign in' }).click()

  await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible()
  await expect(page.getByTestId('count-faqs')).toBeVisible()
  await expect(page.getByRole('navigation', { name: 'Main navigation' })).toBeVisible()
  await page.screenshot({ path: 'e2e/screenshots/dashboard.png' })
})
