import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  server: {
    host: '0.0.0.0',
    port: Number(process.env.ADMIN_DEV_PORT || 28301),
    strictPort: true,
    allowedHosts: [`admin.${process.env.APP_DOMAIN}`],
    watch: { usePolling: true },
    hmr: { clientPort: Number(process.env.NGINX_HTTP_PORT || 80) },
  },
})