import test from 'node:test'
import assert from 'node:assert/strict'
import { historyMonths, visibleHistoryMonths, historyTitle, historyMonthLabel, historyShortDate } from '../src/services/history.js'

const fuel = (id, data, valor, extra = {}) => ({ id, key: `despesa-${id}`, category: 'combustivel', tipo_despesa: 'combustivel', combustivel: 'gasolina', data, valor, ...extra })
const service = (id, data, valor, status = 'concluido') => ({ id, key: `manutencao-${id}`, category: 'manutencao', tipo_servico: 'troca_oleo', data, valor, status })

test('timeline sorts and groups mixed IDs without mutation, and pending maintenance is outside monthly totals', () => {
  const rows = [service(1, '2026-10-03', '0.20'), fuel(1, '2026-10-02', '0.10'), service(2, '2026-10-04', '50.00', 'pendente'), fuel(2, '2026-09-15', '10.00')]
  const copy = structuredClone(rows), groups = historyMonths(rows)
  assert.deepEqual(groups.map(group => group.key), ['2026-10', '2026-09'])
  assert.deepEqual(groups[0].entries.map(entry => entry.row.key), ['manutencao-2', 'manutencao-1', 'despesa-1'])
  assert.equal(groups[0].totalCents, 30); assert.equal(groups[0].pendingCents, 5000)
  assert.equal(groups[1].totalCents, 1000); assert.deepEqual(rows, copy)
})

test('consumption attaches to exact final fuel ID rather than date, partials or maintenance with matching ID', () => {
  const date = '2026-10-02'
  const groups = historyMonths([fuel(1, date, '10'), fuel(2, date, '20'), service(2, date, '30')], [{ abastecimento_id: 2, data: date, km: 100, litros: '5.00', km_l: 20 }])
  assert.equal(groups[0].entries.find(entry => entry.row.key === 'despesa-2').consumption, 20)
  assert.equal(groups[0].entries.find(entry => entry.row.key === 'despesa-1').consumption, null)
  assert.equal(groups[0].entries.find(entry => entry.row.key === 'manutencao-2').consumption, null)
  assert.equal(historyMonths([fuel(1, date, '10')], [{ data: date, km: 100, litros: '5', km_l: 20 }])[0].average, null)
})

test('monthly average is weighted by complete-cycle distance and liters, never a simple mean', () => {
  const rows = [fuel(1, '2026-10-01', '10'), fuel(2, '2026-10-05', '50')]
  const cycles = [{ abastecimento_id: 1, data: '2026-10-01', km: 100, litros: '10.00', km_l: 10 }, { abastecimento_id: 2, data: '2026-10-05', km: 900, litros: '30.00', km_l: 30 }]
  assert.equal(historyMonths(rows, cycles)[0].average, 25)
  assert.equal(historyMonths(rows.filter(row => row.id === 1), cycles)[0].average, 10)
})

test('comparison requires the immediately preceding calendar month with valid data in the same filter', () => {
  const rows = [fuel(1, '2025-12-10', '10'), fuel(2, '2026-01-10', '10'), fuel(3, '2026-03-10', '10')]
  const cycles = rows.map(row => ({ abastecimento_id: row.id, data: row.data, km: row.id === 1 ? 200 : 300, litros: '10.00', km_l: row.id === 1 ? 20 : 30 }))
  const groups = historyMonths(rows, cycles)
  assert.equal(groups[0].comparison, null)
  assert.deepEqual(groups[1].comparison, { month: '2025-12', percent: 50 })
  assert.equal(groups[2].comparison, null)
  assert.equal(historyMonths([rows[1]], cycles)[0].comparison, null)
})

test('expanding history preserves full filtered monthly totals even when only some entries are visible', () => {
  const groups = historyMonths([fuel(1, '2026-10-01', '10'), fuel(2, '2026-10-02', '20'), fuel(3, '2026-09-01', '30')])
  const first = visibleHistoryMonths(groups, 1)
  assert.equal(first.length, 1); assert.equal(first[0].entries.length, 1)
  assert.equal(first[0].totalCents, 3000); assert.equal(first[0].recordCount, 2)
  assert.equal(visibleHistoryMonths(groups, 3).length, 2)
  assert.equal(visibleHistoryMonths(groups, 0).length, 0)
  assert.equal(groups[0].entries.length, 2)
})

test('labels use real categories and UTC calendar formatting without inventing fuel variants', () => {
  assert.equal(historyTitle(fuel(1, '2026-10-01', '10')), 'Gasolina comum')
  assert.equal(historyTitle(fuel(1, '2026-10-01', '10', { combustivel: null })), 'Abastecimento')
  assert.equal(historyMonthLabel('2026-07'), 'julho de 2026')
  assert.equal(historyShortDate('2026-08-16'), '16 de ago.')
  assert.equal(historyTitle(service(1, '2026-10-01', '10')), 'Troca de óleo')
})
