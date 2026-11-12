import { defineConfig } from '@playwright/test'

/**
 * One screenshot spec against the running stack (make up). Runs in the `e2e` compose service (the official
 * Playwright image, browsers included) and resolves the *.localhost names to the nginx container.
 */
const origin = process.env.E2E_ADMIN_ORIGIN ?? 'http://admin.gdsite.localhost:28080'
const host = new URL(origin).hostname
const domain = host.replace(/^admin\./, '')

export default defineConfig({
  testDir: 'e2e',
  outputDir: 'e2e/.results',
  reporter: 'line',
  // The dev server compiles on first hit, so the first navigation is slow.
  timeout: 90_000,
  expect: { timeout: 20_000 },
  use: {
    baseURL: origin,
    viewport: { width: 1280, height: 720 },
    launchOptions: {
      args: ['--no-sandbox', `--host-resolver-rules=MAP ${host} nginx:80, MAP api.${domain} nginx:80`],
    },
  },
})
