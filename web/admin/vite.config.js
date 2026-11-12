import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  // Public PKCE client id, passed in by compose (OAUTH_ADMIN_CLIENT_ID).
  define: { __OAUTH_CLIENT_ID__: JSON.stringify(process.env.OAUTH_ADMIN_CLIENT_ID ?? '') },
  server: {
    host: '0.0.0.0',
    port: Number(process.env.ADMIN_DEV_PORT || 28301),
    strictPort: true,
    allowedHosts: [`admin.${process.env.APP_DOMAIN}`],
    watch: { usePolling: true },
    hmr: { clientPort: Number(process.env.NGINX_HTTP_PORT || 80) },
  },
  test: {
    environment: 'jsdom',
    include: ['src/**/*.test.{ts,tsx}'],
    restoreMocks: true,
  },
})
