import { api } from './api'
import { waitForActiveWorker, applicationServerKey } from './pushLifecycle'
let pendingRequest, activeEndpoint, activeUserId
let generation = 0
let cleanup = Promise.resolve()
const marker = 'motocare-push-user'
export function stopPushSession() {
  generation++; pendingRequest?.abort(); pendingRequest = undefined; activeEndpoint = undefined; activeUserId = undefined
}
async function registration() {
  if (!navigator.serviceWorker) return
  return (await navigator.serviceWorker.getRegistrations()).find(r => new URL(r.scope).pathname === '/push/')
}
export function detachPushBrowser() {
  stopPushSession()
  try { localStorage.removeItem(marker) } catch {}
  cleanup = cleanup.catch(() => {}).then(async () => {
    const worker = await registration()
    if (!worker) return
    try { await (await worker.pushManager.getSubscription())?.unsubscribe() }
    finally { await worker.unregister() }
  })
  return cleanup
}
export async function enablePush(userId, loadedConfig) {
  stopPushSession()
  const attempt = generation
  const controller = new AbortController()
  pendingRequest = controller
  const current = () => { if (attempt !== generation) throw new Error('A sessão mudou. Ative as notificações novamente.') }
  if (!window.isSecureContext || !('Notification' in window) || !('PushManager' in window) || !navigator.serviceWorker) throw new Error('Este navegador não oferece notificações neste endereço.')
  if (loadedConfig && !loadedConfig.configured) throw new Error('As notificações ainda não estão disponíveis neste ambiente.')
  // Solicitar no clique, antes de aguardar rede ou instalação do worker.
  const permission = Notification.permission === 'granted' ? 'granted' : await Notification.requestPermission()
  current()
  if (permission !== 'granted') throw new Error('Permissão de notificações não concedida. Você pode alterá-la nas configurações do navegador.')
  const data = loadedConfig || (await api.get('/push-config', { signal: controller.signal })).data
  current()
  if (!data.configured) throw new Error('As notificações ainda não estão disponíveis neste ambiente.')
  await cleanup.catch(() => {})
  current()
  const worker = await navigator.serviceWorker.register('/push-worker.js', { scope: '/push/' })
  await waitForActiveWorker(worker)
  current()
  const key = applicationServerKey(data.publicKey)
  let subscription = await worker.pushManager.getSubscription()
  if (subscription) {
    const oldKey = new Uint8Array(subscription.options.applicationServerKey || [])
    if (oldKey.length !== key.length || oldKey.some((v, i) => v !== key[i])) {
      await subscription.unsubscribe(); subscription = null
    }
  }
  current()
  subscription ||= await worker.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: key })
  current()
  await api.put('/push-subscription', subscription.toJSON(), { signal: controller.signal })
  current()
  pendingRequest = undefined
  activeEndpoint = subscription.endpoint; activeUserId = userId
  try { localStorage.setItem(marker, String(userId)) } catch {}
}
export async function disablePush() {
  const subscription = await (await registration())?.pushManager.getSubscription()
  stopPushSession()
  try { if (subscription) await api.delete('/push-subscription', { data: { endpoint: subscription.endpoint } }) }
  finally { await detachPushBrowser() }
}
export async function resumePush(userId) {
  if (!('Notification' in window) || Notification.permission !== 'granted') return
  let saved
  try { saved = localStorage.getItem(marker) } catch { return }
  if (saved && saved !== String(userId)) { await detachPushBrowser(); return }
  if (saved === String(userId)) await enablePush(userId)
}
export async function testPush(userId) {
  if (!activeEndpoint || activeUserId !== userId) throw new Error('Ative este navegador antes de enviar a notificação de teste.')
  await api.post('/push-test', { endpoint: activeEndpoint })
}
navigator.serviceWorker?.addEventListener('message', event => {
  if (activeUserId && event.data?.type === 'motocare-notification') {
    window.dispatchEvent(new CustomEvent('motocare-notification', { detail: event.data.title || 'Novo alerta de manutenção' }))
  }
})
