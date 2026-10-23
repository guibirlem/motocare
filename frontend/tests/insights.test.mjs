import test from 'node:test'
import assert from 'node:assert/strict'
import { fuelPriceFromTotal, feedEntries } from '../src/services/insights.js'
import { expensePayload } from '../src/services/finance.js'
import { serviceTypes, alertTypes } from '../src/services/format.js'

test('inverse fuel calculation uses cents and exposes the reconciled total', () => {
  assert.deepEqual(fuelPriceFromTotal('87,50', '12,50'), { price: '7.00', total: '87.50' })
  assert.deepEqual(fuelPriceFromTotal('10,00', '3,00'), { price: '3.33', total: '9.99' })
  assert.equal(fuelPriceFromTotal('10', '0'), null)
  assert.equal(fuelPriceFromTotal('10', ''), null)
  assert.equal(fuelPriceFromTotal('1e2', '10'), null)
  assert.equal(fuelPriceFromTotal('10', '-1'), null)
})

test('new fuel fields are optional, zero mileage and partial tanks survive and type changes clear them', () => {
  const form = { tipo_despesa: 'combustivel', data: '2026-10-23', valor: '10,00', litros: '2,00', preco_litro: '5,00', posto_id: 4, combustivel: 'gasolina', km_abastecimento: 0, tanque_cheio: false }
  const body = expensePayload(form)
  assert.equal(body.km_abastecimento, 0)
  assert.equal(body.tanque_cheio, false)
  assert.equal(body.posto_id, 4)
  for (const key of ['posto_id', 'combustivel', 'km_abastecimento', 'tanque_cheio']) assert.equal(expensePayload({ ...form, tipo_despesa: 'impostos' })[key], null)
  assert.equal(expensePayload({ ...form, km_abastecimento: '' }).km_abastecimento, null)
})

test('unified history preserves equal IDs, pending entries and category/month filters', () => {
  const maintenance = [{ id: 1, data: '2026-10-22', status: 'pendente' }]
  const expenses = [{ id: 1, data: '2026-10-23', tipo_despesa: 'combustivel' }, { id: 2, data: '2026-09-20', tipo_despesa: 'impostos' }]
  const rows = feedEntries(maintenance, expenses)
  assert.equal(rows.length, 3)
  assert.equal(new Set(rows.map(row => row.key)).size, 3)
  assert.equal(rows[0].key, 'despesa-1')
  assert.equal(feedEntries(maintenance, expenses, 'manutencao', '2026-10')[0].status, 'pendente')
  assert.equal(feedEntries(maintenance, expenses, 'despesa', '2026-10').length, 0)
  assert.equal(feedEntries(maintenance, expenses, 'despesa')[0].tipo_despesa, 'impostos')
})

test('legacy maintenance labels remain and all motorcycle services can have reminders', () => {
  for (const key of ['troca_oleo', 'kit_relacao', 'pneus', 'freios', 'lubrificacao_corrente', 'ajuste_corrente', 'pneu_dianteiro', 'pneu_traseiro', 'freio_dianteiro', 'freio_traseiro', 'filtro_oleo', 'fluido_suspensao', 'liquido_arrefecimento', 'cabo_embreagem', 'cabo_acelerador', 'vela']) {
    assert.ok(serviceTypes[key]); assert.equal(alertTypes[key], serviceTypes[key])
  }
  assert.equal(alertTypes.revisao, 'Revisão')
})
