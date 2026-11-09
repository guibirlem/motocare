import { decimal, formatDate, serviceTypes, expenseTypes } from './format.js'
import { buildCsv } from './csv.js'

export function expensePayload(form) {
  const isFuel = form.tipo_despesa === 'combustivel'
  return {
    tipo_despesa: form.tipo_despesa,
    data: form.data,
    descricao: form.descricao?.trim() || null,
    valor: decimal(form.valor),
    litros: isFuel ? decimal(form.litros) : null,
    preco_litro: isFuel ? decimal(form.preco_litro) : null,
    posto: isFuel ? form.posto?.trim() || null : null,
    ...('posto_id' in form ? { posto_id: isFuel ? form.posto_id || null : null } : {}),
    ...('combustivel' in form ? { combustivel: isFuel ? form.combustivel || null : null } : {}),
    ...('km_abastecimento' in form ? { km_abastecimento: isFuel && form.km_abastecimento !== '' ? form.km_abastecimento ?? null : null } : {}),
    ...('tanque_cheio' in form ? { tanque_cheio: isFuel ? form.tanque_cheio ?? null : null } : {}),
  }
}

export function reportEntries(data) {
  return [
    ...data.despesas.map(row => ({ ...row, key: `despesa-${row.id}`, category: expenseTypes[row.tipo_despesa] || row.tipo_despesa, status: 'Pago' })),
    ...data.manutencoes.map(row => ({ ...row, key: `manutencao-${row.id}`, category: serviceTypes[row.tipo_servico], status: row.status === 'concluido' ? 'Feito' : 'Pendente' })),
  ].sort((a, b) => b.data.localeCompare(a.data) || b.id - a.id || a.key.localeCompare(b.key))
}

export function reportCsv(data) {
  const motoName = id => {
    const moto = data.motocicletas.find(item => item.id === id)
    return moto ? `${moto.marca} ${moto.modelo} · ${moto.placa}` : ''
  }
  return buildCsv([
    ['Data', 'Moto', 'Tipo', 'Descrição', 'Valor (R$)', 'Status'],
    ...reportEntries(data).map(row => [formatDate(row.data), motoName(row.motocicleta_id), row.category, row.descricao || row.posto || '', String(row.valor).replace('.', ','), row.status]),
  ])
}
