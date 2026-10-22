import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue(), {
    name: 'motocare-private-paths',
    configurePreviewServer(server) {
      server.middlewares.use((request, response, next) => {
        let path
        try { path = decodeURIComponent(new URL(request.url, 'http://localhost').pathname) }
        catch { response.writeHead(400); response.end(); return }
        if (/(^|\/)\./.test(path) || /^\/(backend|storage|vendor|node_modules|@fs|src|tmp|entrega)(\/|$)/.test(path)) {
          response.writeHead(404, { 'Content-Type': 'application/json', 'Cache-Control': 'no-store', 'X-Content-Type-Options': 'nosniff' })
          response.end(JSON.stringify({ message: 'Recurso não encontrado.' }))
          return
        }
        next()
      })
    },
  }],
  preview: {
    proxy: { '/api': { target: 'http://127.0.0.1:8000' } },
    headers: {
      'X-Content-Type-Options': 'nosniff',
      'Referrer-Policy': 'same-origin',
      'X-Frame-Options': 'DENY',
      'Content-Security-Policy': "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'; worker-src 'self'; manifest-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'",
    },
  },
  server: { proxy: { '/api': { target: 'http://127.0.0.1:8000' } } },
})
