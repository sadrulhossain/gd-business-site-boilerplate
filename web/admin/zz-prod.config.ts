import { defineConfig } from '@playwright/test'
export default defineConfig({
  testDir: 'zz-e2e',
  outputDir: 'zz-e2e/.results',
  reporter: 'line',
  timeout: 90_000,
  expect: { timeout: 20_000 },
  use: {
    baseURL: 'https://admin.prod.localhost',
    ignoreHTTPSErrors: true,
    launchOptions: { args: ['--no-sandbox', '--host-resolver-rules=MAP admin.prod.localhost nginx:443, MAP api.prod.localhost nginx:443, MAP prod.localhost nginx:443'] },
  },
})
