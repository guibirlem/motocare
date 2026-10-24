import { alertThresholdKm, formatKm, today } from './format.js'

export const dateReminderTypes = ['seguro', 'ipva', 'licenciamento']
export const isDateReminder = type => dateReminderTypes.includes(type)
export const reminderSuggestions = [
  { type: 'abastecimento', label: 'Próximo abastecimento', hint: 'Por distância ou data', icon: 'fuel' },
  { type: 'troca_oleo', label: 'Troca de óleo', hint: 'Por distância ou data', icon: 'service' },
  { type: 'revisao', label: 'Revisão', hint: 'Por distância ou data', icon: 'service' },
  { type: 'pneu_dianteiro', label: 'Pneu dianteiro', hint: 'Inspeção por km ou data', icon: 'service' },
  { type: 'pneu_traseiro', label: 'Pneu traseiro', hint: 'Inspeção por km ou data', icon: 'service' },
  { type: 'seguro', label: 'Seguro', hint: 'Data do próximo vencimento', icon: 'shield' },
  { type: 'ipva', label: 'Impostos (IPVA)', hint: 'Data do próximo vencimento', icon: 'wallet' },
  { type: 'licenciamento', label: 'Licenciamento', hint: 'Data do próximo vencimento', icon: 'wallet' },
]

function calendarDay(value) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) return null
  const stamp = Date.parse(value + 'T00:00:00Z')
  return Number.isFinite(stamp) && new Date(stamp).toISOString().slice(0, 10) === value ? stamp / 86400000 : null
}

export function reminderState(alert, currentKm, reference = today()) {
  const delta = Number(alert.km_limite) > 0 && Number.isFinite(Number(currentKm)) ? Number(alert.km_limite) - Number(currentKm) : null
  const deadline = calendarDay(alert.data_limite), currentDay = calendarDay(reference)
  const days = deadline !== null && currentDay !== null ? deadline - currentDay : null
  const overdue = (delta !== null && delta <= 0) || (days !== null && days <= 0)
  const attention = overdue || (delta !== null && Number(currentKm) >= alertThresholdKm(alert))
  const distance = delta === null ? '' : delta > 0 ? `Em ${formatKm(delta)} km` : delta === 0 ? 'Limite atingido' : `Passou ${formatKm(-delta)} km`
  const date = days === null ? '' : days === 0 ? 'Vence hoje' : `${days > 0 ? 'Em' : 'Há'} ${Math.abs(days)} ${Math.abs(days) === 1 ? 'dia' : 'dias'}`
  return { distance, date, tone: overdue ? 'overdue' : attention ? 'attention' : 'scheduled', label: alert.status === 'notificado' ? 'Notificado' : overdue ? 'Vencimento atingido' : attention ? 'Atenção' : 'Programado' }
}

export function activeReminders(rows, currentKm, reference = today()) {
  const priority = { overdue: 2, attention: 1, scheduled: 0 }
  return rows.filter(row => row.status !== 'concluido')
    .map(row => ({ ...row, state: reminderState(row, currentKm, reference) }))
    .sort((a, b) => priority[b.state.tone] - priority[a.state.tone])
}

export function suggestedReminders(rows) {
  // Um tipo salvo continua configurado mesmo após a conclusão do lembrete.
  const configured = new Set(rows.map(row => row.tipo_alerta))
  return reminderSuggestions.filter(item => !configured.has(item.type) && !(item.type.startsWith('pneu_') && configured.has('pneus')))
}

export function reminderDraft(type, currentKm = 0) {
  return { tipo_alerta: type, km_limite: '', km_base: isDateReminder(type) ? '' : currentKm, data_limite: '' }
}

export function reminderPayload(form) {
  const byKm = !isDateReminder(form.tipo_alerta) && Boolean(form.km_limite)
  return { tipo_alerta: form.tipo_alerta, km_limite: byKm ? form.km_limite : null,
    km_base: byKm ? form.km_base === '' ? null : form.km_base : null, data_limite: form.data_limite || null }
}
