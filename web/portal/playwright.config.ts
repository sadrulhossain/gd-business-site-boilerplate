import { defineConfig } from '@playwright/test'

/** Home screenshots against the running stack (make up), run in the `e2e` compose service from web/portal. */
const origin = process.env.E2E_PORTAL_ORIGIN ?? 'http://gdsite.localhost:28080'
const host = new URL(origin).hostname

export default defineConfig({
  testDir: 'e2e',
  outputDir: 'e2e/.results',
  reporter: 'line',
  timeout: 90_000,
  expect: { timeout: 20_000 },
  use: {
    baseURL: origin,
    viewport: { width: 1280, height: 900 },
    launchOptions: { args: ['--no-sandbox', `--host-resolver-rules=MAP ${host} nginx:80, MAP api.${host} nginx:80`] },
  },
})
