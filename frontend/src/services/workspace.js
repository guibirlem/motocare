import { reactive } from 'vue'
import { api, prepareSession } from './api'
import { resumePush, stopPushSession, detachPushBrowser } from './push'
let sessionVersion = 0, motorcyclesVersion = 0
export const workspace = reactive({ user: null, motos: [], motoId: '', ready: false, sessionNotice: '', offline: !navigator.onLine })
export async function refreshMotos(expectedVersion = sessionVersion) {
  const ownerId = workspace.user?.id, requestVersion = ++motorcyclesVersion
  if (!ownerId || expectedVersion !== sessionVersion) return
  const { data } = await api.get('/motocicletas')
  if (expectedVersion !== sessionVersion || ownerId !== workspace.user?.id || requestVersion !== motorcyclesVersion) return
  workspace.motos = data
  if (!data.some(m => m.id === Number(workspace.motoId))) workspace.motoId = data[0]?.id || ''
}
export function clearSession(message = '') { sessionVersion++; stopPushSession(); void detachPushBrowser().catch(() => {}); delete api.defaults.headers.common['X-CSRF-TOKEN']; workspace.sessionNotice = message; workspace.user = null; workspace.motos = []; workspace.motoId = ''; workspace.ready = true }
async function resumeNotifications() {
  try { await resumePush(workspace.user.id) } catch {}
}
export async function startSession() {
  const attempt = ++sessionVersion, current = () => attempt === sessionVersion
  try {
    await prepareSession(current)
    if (!current()) return
    const { data } = await api.get('/user')
    if (!current()) return
    workspace.user = data
    await refreshMotos(attempt)
    if (current()) await resumeNotifications()
  }
  catch (e) { if (!current()) return; if (e.response?.status !== 401) throw e; clearSession() }
  finally { if (current()) workspace.ready = true }
}
export async function signIn(register, credentials) {
  const attempt = ++sessionVersion, current = () => attempt === sessionVersion
  await prepareSession(current)
  if (!current()) return
  const { data } = await api.post(register ? '/register' : '/login', credentials)
  if (!current()) return
  workspace.user = data
  workspace.sessionNotice = ''
  await prepareSession(current)
  if (!current()) return
  await refreshMotos(attempt)
  if (current()) await resumeNotifications()
}
export async function signOut() { const attempt = ++sessionVersion; stopPushSession(); await detachPushBrowser().catch(() => {}); if (attempt !== sessionVersion) return; await api.post('/logout'); if (attempt !== sessionVersion) return; try { localStorage.removeItem('motocare-push-user') } catch {} clearSession(); const cleared = sessionVersion; await prepareSession(() => cleared === sessionVersion) }
export function syncMaintenanceMileage(record) {
  if (record.status !== 'concluido') return
  const motorcycle = workspace.motos.find(m => m.id === Number(record.motocicleta_id))
  if (motorcycle) motorcycle.km_atual = Math.max(Number(motorcycle.km_atual), Number(record.km_servico))
}
api.interceptors.request.use(config => { config.motocareSessionVersion = sessionVersion; return config })
api.interceptors.response.use(response => {
  if (response.config?.motocareSessionVersion !== undefined && response.config.motocareSessionVersion !== sessionVersion) {
    throw new Error('A sessão mudou. Atualize a página para conferir seus dados.')
  }
  return response
}, error => {
  if ([401,419].includes(error.response?.status) && error.config?.motocareSessionVersion === sessionVersion) clearSession(workspace.user ? 'Sua sessão expirou. Entre novamente.' : '')
  return Promise.reject(error)
})
window.addEventListener('offline', () => { workspace.offline = true })
window.addEventListener('online', () => { workspace.offline = false })
