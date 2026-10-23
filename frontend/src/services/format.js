export const formatCurrency = value => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value || 0))
export const formatKm = value => new Intl.NumberFormat('pt-BR').format(value)
export const formatDate = value => value ? value.slice(0,10).split('-').reverse().join('/') : '—'
export const today = (date = new Date()) => {
  const parts = Object.fromEntries(new Intl.DateTimeFormat('en', {timeZone:'America/Sao_Paulo',year:'numeric',month:'2-digit',day:'2-digit'}).formatToParts(date).map(part => [part.type,part.value]))
  return `${parts.year}-${parts.month}-${parts.day}`
}
export const decimal = value => String(value ?? '').replace(',', '.')
export const serviceTypes = { troca_oleo: 'Troca de óleo', kit_relacao: 'Kit relação', pneus: 'Pneus', freios: 'Freios', lubrificacao_corrente: 'Lubrificação da corrente', ajuste_corrente: 'Ajuste de tensão da corrente', freio_dianteiro: 'Pastilhas/lonas dianteiras', freio_traseiro: 'Pastilhas/lonas traseiras', pneu_dianteiro: 'Pneu dianteiro', pneu_traseiro: 'Pneu traseiro', filtro_oleo: 'Filtro de óleo', fluido_suspensao: 'Fluido da suspensão (bengala)', liquido_arrefecimento: 'Líquido de arrefecimento', cabo_embreagem: 'Cabo de embreagem', cabo_acelerador: 'Cabo de acelerador', vela: 'Vela' }
export const alertTypes = { ...serviceTypes, revisao: 'Revisão' }
export const fuelTypes = { gasolina: 'Gasolina', gasolina_aditivada: 'Gasolina aditivada', etanol: 'Etanol', diesel: 'Diesel' }
export const expenseTypes = { combustivel: 'Combustível', reparo: 'Reparo', documentacao: 'Documentação', impostos: 'Impostos', outros: 'Outros' }
export function alertThresholdKm(alert) {
  const base = Number(alert.km_base ?? 0)
  return base + Math.ceil((Number(alert.km_limite) - base) * 9 / 10)
}
export function alertProgress(alert, currentKm) {
  const base = Number(alert.km_base ?? 0), interval = Number(alert.km_limite) - base
  return interval > 0 ? Math.max(0, Math.min(100, (Number(currentKm) - base) * 100 / interval)) : 0
}
export function fuelTotal(liters, price) {
  const cents = value => { const s = decimal(value); if (!/^\d+(\.\d{0,2})?$/.test(s)) return 0n; const [i, f=''] = s.split('.'); return BigInt(i)*100n + BigInt(f.padEnd(2,'0')) }
  const total = (cents(liters)*cents(price)+50n)/100n
  return (total/100n).toString()+'.'+(total%100n).toString().padStart(2,'0')
}
export function reportParams(start, end, moto = '') { return { inicio: start, fim: end, ...(moto ? { motocicleta_id: moto } : {}) } }
