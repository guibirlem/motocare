import test from 'node:test'
import assert from 'node:assert/strict'
import { expensePayload, reportEntries, reportCsv } from '../src/services/finance.js'
import { navigationItems, routeEnabled } from '../src/services/navigation.js'

test('financial navigation and direct routes follow the authenticated API feature flag', () => {
  const enabled = navigationItems({ financeiro: true }).map(item => item.path)
  const disabled = navigationItems({ financeiro: false }).map(item => item.path)
  assert.ok(enabled.includes('/gastos') && enabled.includes('/relatorios'))
  assert.ok(!disabled.includes('/gastos') && !disabled.includes('/relatorios'))
  assert.ok(disabled.includes('/manutencoes') && disabled.includes('/alertas'))
  assert.equal(routeEnabled({ meta: { financeiro: true } }, { financeiro: false }), false)
  assert.equal(routeEnabled({ meta: { financeiro: true } }, { financeiro: true }), true)
  assert.equal(routeEnabled({ meta: {} }, { financeiro: false }), true)
})

test('expense form converts Brazilian decimals and clears irrelevant or empty optional fields', () => {
  const form = { tipo_despesa: 'combustivel', data: '2026-10-23', litros: '12,50', preco_litro: '7,00', valor: '87,50', posto: '  Centro  ', descricao: '   ', motocicleta_id: 99 }
  assert.deepEqual(expensePayload(form), { tipo_despesa: 'combustivel', data: '2026-10-23', litros: '12.50', preco_litro: '7.00', valor: '87.50', posto: 'Centro', descricao: null })
  const repair = expensePayload({ ...form, tipo_despesa: 'reparo', descricao: ' Espelho ', valor: '12,34' })
  assert.deepEqual([repair.litros, repair.preco_litro, repair.posto, repair.valor, repair.descricao], [null, null, null, '12.34', 'Espelho'])
})

const report = {
  motocicletas: [{ id: 7, marca: 'Honda', modelo: 'CG', placa: 'ABC1D23' }],
  despesas: [{ id: 1, motocicleta_id: 7, data: '2026-10-23', tipo_despesa: 'combustivel', descricao: '=SUM(1;2)', valor: '87.50' }, { id: 2, motocicleta_id: 7, data: '2026-10-22', tipo_despesa: 'reparo', descricao: 'Espelho; "novo"\nconferido', valor: '12.34' }],
  manutencoes: [{ id: 1, motocicleta_id: 7, data: '2026-10-23', tipo_servico: 'freios', status: 'pendente', valor: '500.00' }, { id: 2, motocicleta_id: 7, data: '2026-10-21', tipo_servico: 'troca_oleo', status: 'concluido', valor: '100.01' }],
}

test('financial report preserves pending status and does not merge equal IDs across tables', () => {
  const rows = reportEntries(report)
  assert.equal(rows.length, 4)
  assert.equal(new Set(rows.map(row => row.key)).size, 4)
  assert.equal(rows.find(row => row.key === 'manutencao-1').status, 'Pendente')
  assert.equal(rows.find(row => row.key === 'despesa-1').status, 'Pago')
  assert.equal(rows.at(-1).category, 'Troca de óleo')
  assert.equal(report.manutencoes[0].status, 'pendente')
})

test('downloaded report CSV uses Brazilian decimals, UTF-8 BOM and protects user text', () => {
  const csv = reportCsv(report)
  assert.ok(csv.startsWith('\uFEFF"Data";"Moto"'))
  assert.ok(csv.includes('"23/10/2026";"Honda CG · ABC1D23"'))
  assert.ok(csv.includes('"\'=SUM(1;2)";"87,50";"Pago"'))
  assert.ok(csv.includes('"Espelho; ""novo""\nconferido";"12,34"'))
  assert.ok(csv.includes('"500,00";"Pendente"'))
  assert.ok(!csv.includes('87.50'))
})
