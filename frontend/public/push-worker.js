self.addEventListener('install', () => self.skipWaiting())
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()))
self.addEventListener('push', event => {
  event.waitUntil((async () => {
    let payload = {}
    try { payload = event.data?.json() || {} } catch {}
    const title = payload.title || 'MotoCare: alerta de manutenção'
    await self.registration.showNotification(title, {
      body: payload.body || 'Confira seus lembretes de manutenção.',
      tag: payload.tag || 'motocare-alerta', icon: '/icons/icon-192.png',
      data: { url: '/alertas' }
    })
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
    for (const client of clients) client.postMessage({ type: 'motocare-notification', title })
  })())
})
self.addEventListener('notificationclick', event => {
  event.notification.close()
  event.waitUntil((async () => {
    const target = new URL('/alertas', self.location.origin)
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
    for (const client of clients) {
      if (new URL(client.url).origin === target.origin) {
        if ('navigate' in client) await client.navigate(target.href)
        return client.focus()
      }
    }
    return self.clients.openWindow(target.href)
  })())
})
