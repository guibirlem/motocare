import test from 'node:test'
import assert from 'node:assert/strict'
import { isPresentation, presentationAccess } from '../src/services/presentation.js'

test('public presentation preserves authenticated root and anonymous deep links to existing screens', () => {
  assert.equal(isPresentation({ path: '/', meta: {} }, null), true)
  assert.equal(isPresentation({ path: '/', meta: {} }, { id: 7 }), false)
  for (const path of ['/dashboard', '/gastos', '/manutencoes', '/alertas', '/relatorios', '/garagem', '/configuracoes']) {
    assert.equal(isPresentation({ path, meta: {} }, null), false, path)
    assert.equal(isPresentation({ path, meta: {} }, { id: 7 }), false, path)
  }
  assert.equal(isPresentation({ path: '/apresentacao', meta: { presentation: true } }, null), true)
  assert.equal(isPresentation({ path: '/apresentacao', meta: { presentation: true } }, { id: 7 }), true)
})

test('all acquisition CTAs reuse dashboard authentication and do not force registration on an authenticated account', () => {
  assert.deepEqual(presentationAccess(null), { path: '/dashboard' })
  assert.deepEqual(presentationAccess(null, true), { path: '/dashboard', query: { cadastro: '1' } })
  assert.deepEqual(presentationAccess({ id: 7 }, true), { path: '/dashboard' })
  assert.deepEqual(presentationAccess({ id: 7 }), { path: '/dashboard' })
})
