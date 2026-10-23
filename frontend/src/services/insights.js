import { decimal, fuelTotal } from './format.js'

export function fuelPriceFromTotal(total, liters) {
  const cents = value => {
    const text = decimal(value)
    if (!/^\d+(\.\d{0,2})?$/.test(text)) return null
    const [whole, fraction = ''] = text.split('.')
    return BigInt(whole) * 100n + BigInt(fraction.padEnd(2, '0'))
  }
  const cost = cents(total), volume = cents(liters)
  if (cost === null || volume === null || volume <= 0n) return null
  const result = (cost * 100n + volume / 2n) / volume
  const price = `${result / 100n}.${String(result % 100n).padStart(2, '0')}`
  return { price, total: fuelTotal(liters, price) }
}

export function feedEntries(maintenance, expenses, category = '', month = '') {
  const rows = [
    ...maintenance.map(row => ({ ...row, key: `manutencao-${row.id}`, category: 'manutencao' })),
    ...expenses.map(row => ({ ...row, key: `despesa-${row.id}`, category: row.tipo_despesa === 'combustivel' ? 'combustivel' : 'despesa' })),
  ]
  return rows.filter(row => (!category || row.category === category) && (!month || row.data.slice(0, 7) === month))
    .sort((a, b) => b.data.localeCompare(a.data) || b.id - a.id || a.key.localeCompare(b.key))
}

export const spendingLabels = { combustivel: 'Combustível', preventiva: 'Manutenção preventiva', corretiva: 'Manutenção corretiva e reparos', documentacao: 'Documentação', impostos: 'Impostos', outros: 'Outros', manutencao_sem_classificacao: 'Manutenção sem classificação' }
