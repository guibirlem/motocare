import { expenseTypes, fuelTypes, serviceTypes } from './format.js'

export function historyTitle(row) {
  if (row.category === 'manutencao') return serviceTypes[row.tipo_servico] || row.tipo_servico || 'Manutenção'
  if (row.tipo_despesa === 'combustivel') return row.combustivel === 'gasolina' ? 'Gasolina comum' : fuelTypes[row.combustivel] || 'Abastecimento'
  return expenseTypes[row.tipo_despesa] || 'Despesa'
}

export function historyIcon(row) {
  return row.category === 'manutencao' ? 'service' : row.tipo_despesa === 'combustivel' ? 'fuel' : 'wallet'
}

export const historyQuantity = value => new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 }).format(Number(value))
export const historyMonthLabel = month => new Intl.DateTimeFormat('pt-BR', { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(month + '-15T12:00:00Z'))
export const historyShortDate = date => new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: 'short', timeZone: 'UTC' }).format(new Date(date.slice(0, 10) + 'T12:00:00Z'))

function previousMonth(month) {
  const [year, number] = month.split('-').map(Number)
  return number === 1 ? `${String(year - 1).padStart(4, '0')}-12` : `${year}-${String(number - 1).padStart(2, '0')}`
}

export function historyMonths(rows, cycles = []) {
  // Associa pelo registro final, nunca só pela data (pode haver vários cheios no mesmo dia).
  const cycleById = new Map(cycles.filter(cycle => cycle.abastecimento_id != null).map(cycle => [Number(cycle.abastecimento_id), cycle]))
  const months = new Map()
  const sorted = [...rows].sort((a, b) => b.data.localeCompare(a.data) || b.id - a.id || String(a.key).localeCompare(String(b.key)))
  for (const row of sorted) {
    const key = row.data.slice(0, 7)
    if (!months.has(key)) months.set(key, { key, entries: [], totalCents: 0, pendingCents: 0, distance: 0, liters: 0, average: null, comparison: null })
    const group = months.get(key), cents = Math.round(Number(row.valor || 0) * 100)
    if (row.category === 'manutencao' && row.status !== 'concluido') group.pendingCents += cents
    else group.totalCents += cents
    const cycle = row.category !== 'manutencao' && row.tipo_despesa === 'combustivel' ? cycleById.get(Number(row.id)) : null
    const valid = cycle && cycle.data === row.data && Number(cycle.km) > 0 && Number(cycle.litros) > 0 && Number(cycle.km_l) > 0
    if (valid) { group.distance += Number(cycle.km); group.liters += Math.round(Number(cycle.litros) * 100) }
    group.entries.push({ row, title: historyTitle(row), icon: historyIcon(row), consumption: valid ? Number(cycle.km_l) : null })
  }
  for (const group of months.values()) group.average = group.liters > 0 ? group.distance * 100 / group.liters : null
  for (const group of months.values()) {
    const previous = months.get(previousMonth(group.key))
    if (group.average > 0 && previous?.average > 0) {
      group.comparison = { month: previous.key, percent: Math.round((group.average / previous.average - 1) * 1000) / 10 }
    }
  }
  return [...months.values()]
}

export function visibleHistoryMonths(groups, limit) {
  let remaining = Number.isFinite(limit) ? Math.max(0, Math.floor(limit)) : Infinity
  return groups.flatMap(group => {
    const entries = group.entries.slice(0, remaining)
    remaining -= entries.length
    // Totais são do mês inteiro no filtro, não apenas dos itens já expandidos.
    return entries.length ? [{ ...group, entries, recordCount: group.entries.length }] : []
  })
}
