import test from 'node:test'
import assert from 'node:assert/strict'
import { api } from '../src/services/api.js'
import { readResetLink, requestPasswordReset, resetPassword } from '../src/services/passwordRecovery.js'

test('reset links preserve encoded addresses and reject malformed or incomplete secrets', () => {
  const token = 'a'.repeat(64), email = 'Pilot+Moto@exemplo.com'
  assert.deepEqual(readResetLink('#' + new URLSearchParams({ token, email })), { token, email })
  for (const hash of ['', '#token=' + token, '#token=short&email=pilot%40example.com', '#token=' + token + '&email=invalid', '#token=' + token.toUpperCase() + '&email=pilot%40example.com']) {
    assert.equal(readResetLink(hash), null)
  }
})

test('recovery writes refresh CSRF first and submit no unrelated account data', async () => {
  const original = api.defaults.adapter, calls = []
  api.defaults.adapter = async config => {
    calls.push({ url: config.url, method: config.method, data: config.data && JSON.parse(config.data), csrf: config.headers.get('X-CSRF-TOKEN') })
    return { status: 200, statusText: 'OK', headers: {}, config, data: config.url === '/csrf' ? { token: 'csrf-fixture' } : { message: 'accepted' } }
  }
  const credentials = { email: 'pilot@example.test', token: 'b'.repeat(64), password: 'NewPassword123', password_confirmation: 'NewPassword123' }
  try {
    assert.equal(await requestPasswordReset(' pilot@example.test '), 'accepted')
    assert.equal(await resetPassword(credentials), 'accepted')
    assert.deepEqual(calls.map(c => [c.method, c.url]), [['get', '/csrf'], ['post', '/forgot-password'], ['get', '/csrf'], ['post', '/reset-password']])
    assert.deepEqual(calls[1].data, { email: 'pilot@example.test' })
    assert.deepEqual(calls[3].data, credentials)
    assert.equal(calls[1].csrf, 'csrf-fixture'); assert.equal(calls[3].csrf, 'csrf-fixture')
  } finally { api.defaults.adapter = original; delete api.defaults.headers.common['X-CSRF-TOKEN'] }
})

test('offline recovery neither sends nor queues a password change', async () => {
  const previous = Object.getOwnPropertyDescriptor(globalThis, 'navigator'), original = api.defaults.adapter
  const browser = { onLine: false }, calls = []
  Object.defineProperty(globalThis, 'navigator', { value: browser, configurable: true })
  api.defaults.adapter = async config => { calls.push(config); return { status: 200, config, data: {} } }
  try {
    await assert.rejects(resetPassword({ email: 'pilot@example.test' }), e => e.code === 'MOTOCARE_OFFLINE')
    browser.onLine = true
    await Promise.resolve()
    assert.deepEqual(calls, [])
  } finally {
    api.defaults.adapter = original
    if (previous) Object.defineProperty(globalThis, 'navigator', previous)
    else delete globalThis.navigator
  }
})
