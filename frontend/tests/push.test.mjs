import test from 'node:test'
import assert from 'node:assert/strict'
import { waitForActiveWorker, applicationServerKey } from '../src/services/pushLifecycle.js'
class Worker extends EventTarget {
  constructor(state) { super(); this.state = state }
  transition(state) { this.state = state; this.dispatchEvent(new Event('statechange')) }
}
test('an already activated worker resolves immediately', async () => {
  await waitForActiveWorker({ active: new Worker('activated') }, 100)
})
test('an installing worker is awaited before using an older active worker', async () => {
  const installing = new Worker('installing')
  let done = false
  const result = waitForActiveWorker({ installing, active: new Worker('activated') }, 100).then(() => { done = true })
  await Promise.resolve(); assert.equal(done, false)
  installing.transition('activated'); await result; assert.equal(done, true)
})
test('activation between registration and listener attachment is detected', async () => {
  const worker = new Worker('installed')
  const original = worker.addEventListener.bind(worker)
  worker.addEventListener = (...args) => { original(...args); worker.state = 'activated' }
  await waitForActiveWorker({ installing: worker }, 100)
})
test('a discarded worker produces an actionable failure', async () => {
  const worker = new Worker('installing')
  const result = waitForActiveWorker({ installing: worker }, 100)
  worker.transition('redundant')
  await assert.rejects(result, /Não foi possível ativar/)
})
test('a stuck worker times out without leaving the action pending', async () => {
  await assert.rejects(waitForActiveWorker({ installing: new Worker('installing') }, 5), /Tempo esgotado/)
})

import { readFile } from 'node:fs/promises'
import vm from 'node:vm'
test('VAPID public key is converted from base64url to 65 raw bytes', () => {
  const raw = Uint8Array.from({ length: 65 }, (_, i) => i === 0 ? 4 : i)
  assert.deepEqual(applicationServerKey(Buffer.from(raw).toString('base64url')), raw)
  assert.throws(() => applicationServerKey('AAAA'), /Chave/)
})
const pushWorker = await readFile(new URL('../public/push-worker.js', import.meta.url), 'utf8')
function nativeWorker(existing = []) {
  const handlers = {}, shown = [], opened = [], messages = []
  const self = { location: { origin: 'https://motocare.test' }, registration: { showNotification: async (...args) => shown.push(args) }, skipWaiting: async () => {}, clients: { claim: async () => {}, matchAll: async () => existing, openWindow: async url => opened.push(url) }, addEventListener: (name, fn) => handlers[name] = fn }
  vm.runInNewContext(pushWorker, { self, URL })
  return { handlers, shown, opened, messages }
}
test('native push displays a visible notification and informs open windows', async () => {
  const messages = [], w = nativeWorker([{ postMessage: value => messages.push(value) }]); let pending
  w.handlers.push({ data: { json: () => ({ title: 'Troca de óleo', body: '900 km', tag: 'alerta-1' }) }, waitUntil: p => pending = p })
  await pending
  assert.equal(w.shown[0][0], 'Troca de óleo')
  assert.equal(w.shown[0][1].body, '900 km')
  assert.equal(w.shown[0][1].data.url, '/alertas')
  assert.equal(messages[0].type, 'motocare-notification')
})
test('empty push still displays a notification', async () => {
  const w = nativeWorker(); let pending
  w.handlers.push({ waitUntil: p => pending = p }); await pending
  assert.equal(w.shown[0][0], 'MotoCare: alerta de manutenção')
})
test('notification click opens the alerts page and ignores external destinations', async () => {
  const w = nativeWorker(); let pending, closed = false
  w.handlers.notificationclick({ notification: { data: { url: 'https://evil.test' }, close: () => closed = true }, waitUntil: p => pending = p })
  await pending; assert.equal(closed, true); assert.deepEqual(w.opened, ['https://motocare.test/alertas'])
})
test('notification click focuses and navigates an existing application window', async () => {
  const visited = [], client = { url: 'https://motocare.test/garagem', navigate: async url => visited.push(url), focus: async () => visited.push('focus') }
  const w = nativeWorker([client]); let pending
  w.handlers.notificationclick({ notification: { close() {} }, waitUntil: p => pending = p }); await pending
  assert.deepEqual(visited, ['https://motocare.test/alertas', 'focus']); assert.equal(w.opened.length, 0)
})
const pushClient = (await readFile(new URL('../src/services/push.js', import.meta.url), 'utf8')).replace(/^import .*$/gm, '').replaceAll('export ', '')
function nativeClient({ subscription, savedUser = '7' } = {}) {
  const calls = [], storage = new Map([['motocare-push-user', savedUser]])
  const key = Uint8Array.from({ length: 65 }, (_, i) => i === 0 ? 4 : i)
  const active = subscription || { endpoint: 'https://push.test/id', options: { applicationServerKey: key.buffer }, toJSON: () => ({ endpoint: 'https://push.test/id', keys: {} }), unsubscribe: async () => calls.push('unsubscribe') }
  const worker = { scope: 'https://motocare.test/push/', pushManager: { getSubscription: async () => active, subscribe: async () => active }, unregister: async () => calls.push('unregister') }
  const api = { get: async () => { calls.push('config'); return { data: { configured: true, publicKey: Buffer.from(key).toString('base64url') } } }, put: async () => calls.push('put'), post: async (path, data) => calls.push([path, data.endpoint]), delete: async () => calls.push('delete') }
  const Notification = { permission: 'default', requestPermission: () => { calls.push('permission'); return Promise.resolve('granted') } }
  const window = { isSecureContext: true, Notification, PushManager: {}, dispatchEvent() {} }
  const navigator = { serviceWorker: { getRegistrations: async () => [worker], register: async () => { calls.push('register'); return worker }, addEventListener() {} } }
  const context = { api, window, navigator, Notification, localStorage: { getItem: k => storage.get(k), setItem: (k,v) => storage.set(k,v), removeItem: k => storage.delete(k) }, URL, Uint8Array, AbortController, applicationServerKey, waitForActiveWorker: async () => {}, CustomEvent: class {} }
  vm.runInNewContext(pushClient, context)
  return { context, calls, storage, Notification }
}
test('native client requests permission during the click and binds the browser before testing', async () => {
  const c = nativeClient(); const pending = c.context.enablePush(7)
  assert.equal(c.calls[0], 'permission'); await pending
  await c.context.testPush(7)
  assert.deepEqual(c.calls.slice(-2), ['put', ['/push-test', 'https://push.test/id']])
  await assert.rejects(c.context.testPush(8), /Ative este navegador/)
})
test('logout detaches the local subscription and blocks subsequent tests', async () => {
  const c = nativeClient(); await c.context.enablePush(7); await c.context.detachPushBrowser()
  assert.deepEqual(c.calls.slice(-2), ['unsubscribe', 'unregister'])
  assert.equal(c.storage.has('motocare-push-user'), false)
  await assert.rejects(c.context.testPush(7), /Ative este navegador/)
})
test('automatic resume never asks for notification permission', async () => {
  const c = nativeClient(); await c.context.resumePush(7)
  assert.deepEqual(c.calls, [])
})
test('a session change while asking permission prevents registration to the former account', async () => {
  const c = nativeClient(); let permit
  c.Notification.requestPermission = () => new Promise(resolve => permit = resolve)
  const pending = c.context.enablePush(7); c.context.stopPushSession(); permit('granted')
  await assert.rejects(pending, /sessão mudou/)
  assert.equal(c.calls.includes('put'), false)
})
