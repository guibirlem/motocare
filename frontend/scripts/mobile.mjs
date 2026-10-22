import { preview } from 'vite'
import config from '../vite.config.js'

// Serve somente o build compilado e a API pelo túnel temporário de testes.
const mobileHost = process.env.MOTOCARE_MOBILE_HOST
if (!mobileHost || !/^[a-z0-9]+(?:-[a-z0-9]+)*\.trycloudflare\.com$/.test(mobileHost)) {
  throw new Error('Defina MOTOCARE_MOBILE_HOST com o hostname exato do túnel trycloudflare.com, sem https://.')
}
const server = await preview({
  ...config,
  configFile: false,
  preview: {
    host: '127.0.0.1',
    port: 5180,
    strictPort: true,
    allowedHosts: [mobileHost],
    headers: config.preview.headers,
    proxy: { '/api': { target: 'http://127.0.0.1:8001' } },
  },
})
server.printUrls()
console.log(`Acesso móvel: https://${mobileHost}`)
