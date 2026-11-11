import { defineConfig } from 'vite'

export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: Number(process.env.ADMIN_DEV_PORT || 28301),
    strictPort: true,
    allowedHosts: [`admin.${process.env.APP_DOMAIN}`],
    watch: { usePolling: true },
    hmr: { clientPort: Number(process.env.NGINX_HTTP_PORT || 80) },
  },
})
