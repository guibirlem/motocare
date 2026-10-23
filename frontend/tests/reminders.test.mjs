import test from 'node:test'
import assert from 'node:assert/strict'
import { activeReminders, reminderDraft, reminderPayload, reminderState, suggestedReminders } from '../src/services/reminders.js'
import { alertTypes, serviceTypes } from '../src/services/format.js'

test('reminders show remaining mileage and calendar days independently, including today and overdue', () => {
  const reference = '2026-10-23'
  const future = reminderState({ km_limite: 66718, km_base: 66000, data_limite: '2026-10-25' }, 66488, reference)
  assert.equal(future.distance, 'Em 230 km'); assert.equal(future.date, 'Em 2 dias'); assert.equal(future.tone, 'scheduled')
  const past = reminderState({ km_limite: 66718, km_base: 66000, data_limite: '2026-08-28' }, 66488, reference)
  assert.equal(past.date, 'Há 56 dias'); assert.equal(past.tone, 'overdue')
  assert.equal(reminderState({ data_limite: reference }, 0, reference).date, 'Vence hoje')
  assert.equal(reminderState({ km_limite: 10, km_base: 0 }, 10, reference).distance, 'Limite atingido')
  assert.equal(reminderState({ km_limite: 10, km_base: 0 }, 11, reference).distance, 'Passou 1 km')
})

test('early warning preserves ninety percent of the actual service interval at high odometers', () => {
  const row = { km_limite: 71779, km_base: 66779 }
  assert.equal(reminderState(row, 66779).tone, 'scheduled')
  assert.equal(reminderState(row, 71278).tone, 'scheduled')
  assert.equal(reminderState(row, 71279).tone, 'attention')
  assert.equal(reminderState(row, 71279).distance, 'Em 500 km')
  assert.equal(reminderState(row, 71779).tone, 'overdue')
})

test('calendar arithmetic handles leap days and month boundaries without timezone offsets or invalid dates', () => {
  assert.equal(reminderState({ data_limite: '2028-03-01' }, 0, '2028-02-28').date, 'Em 2 dias')
  assert.equal(reminderState({ data_limite: '2026-11-01' }, 0, '2026-10-31').date, 'Em 1 dia')
  assert.equal(reminderState({ data_limite: '2026-10-30' }, 0, '2026-10-31').date, 'Há 1 dia')
  for (const date of ['invalid', '2026-02-30', null]) assert.equal(reminderState({ data_limite: date }, 0).date, '')
})

test('active reminders prioritize due items, preserve notified reminders and never mutate source data', () => {
  const rows = [{ id: 1, status: 'pendente', data_limite: '2026-12-01' }, { id: 2, status: 'notificado', data_limite: '2026-10-23' }, { id: 3, status: 'concluido', data_limite: '2020-01-01' }]
  const before = structuredClone(rows), active = activeReminders(rows, 0, '2026-10-23')
  assert.deepEqual(active.map(row => row.id), [2, 1]); assert.equal(active[0].state.label, 'Notificado')
  assert.deepEqual(rows, before)
})

test('suggestions exclude active categories, recognize legacy tires and return after explicit completion', () => {
  const rows = [{ tipo_alerta: 'seguro', status: 'notificado' }, { tipo_alerta: 'pneus', status: 'pendente' }, { tipo_alerta: 'ipva', status: 'concluido' }]
  const keys = suggestedReminders(rows).map(item => item.type)
  assert.equal(keys.includes('seguro'), false); assert.equal(keys.includes('pneu_dianteiro'), false); assert.equal(keys.includes('pneu_traseiro'), false)
  assert.equal(keys.includes('ipva'), true)
  assert.equal(suggestedReminders([{ tipo_alerta: 'seguro', status: 'concluido' }]).some(item => item.type === 'seguro'), true)
})

test('calendar drafts never send leftover mileage and new types remain separate from maintenance services', () => {
  for (const type of ['seguro', 'ipva', 'licenciamento']) {
    assert.ok(alertTypes[type]); assert.equal(serviceTypes[type], undefined)
    assert.equal(reminderDraft(type, 1000).km_base, '')
    assert.deepEqual(reminderPayload({ ...reminderDraft(type), km_limite: 2000, km_base: 1000, data_limite: '2027-01-20' }), { tipo_alerta: type, km_limite: null, km_base: null, data_limite: '2027-01-20' })
  }
  assert.deepEqual(reminderPayload({ ...reminderDraft('abastecimento', 0), km_limite: 500 }), { tipo_alerta: 'abastecimento', km_limite: 500, km_base: 0, data_limite: null })
})
