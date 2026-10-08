export const formatKm = (value) => new Intl.NumberFormat('pt-BR').format(value)
export const formatCurrency = (value) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)

export const motorcycles = [
  { id: 1, name: 'Honda CB 500F', plate: 'ABC-1D23', year: 2021, currentKm: 28400, accent: true },
  { id: 2, name: 'Yamaha MT-07', plate: 'XYZ-9K01', year: 2019, currentKm: 41200, accent: false },
]

export const maintenanceRecords = [
  { id: 1, date: '10/08/2026', type: 'Troca de óleo', description: 'Óleo Motul 10W-40', value: 142, km: 28400, motorcycle: 'Honda CB 500F', status: 'Feito' },
  { id: 2, date: '02/07/2026', type: 'Filtro de ar', description: 'Filtro original Honda', value: 65, km: 27100, motorcycle: 'Honda CB 500F', status: 'Feito' },
  { id: 3, date: '15/06/2026', type: 'Corrente + Relação', description: 'DID 520', value: 210, km: 26500, motorcycle: 'Yamaha MT-07', status: 'Feito' },
  { id: 4, date: '10/05/2026', type: 'Pneu dianteiro', description: 'Pirelli Angel GT', value: 290, km: 25800, motorcycle: 'Honda CB 500F', status: 'Feito' },
  { id: 5, date: '20/03/2026', type: 'Vela ignição', description: 'NGK CR8E', value: 45, km: 24200, motorcycle: 'Yamaha MT-07', status: 'Feito' },
  { id: 6, date: '20/09/2026', type: 'Revisão 30.000km', description: 'Revisão completa', value: 450, km: null, motorcycle: 'Honda CB 500F', status: 'Pendente' },
]

export const fuelRecords = [
  { id: 1, date: '12/08/2026', liters: '12,5 L', price: 7, value: 87.5, station: 'Shell Centro' },
  { id: 2, date: '28/07/2026', liters: '13,0 L', price: 7, value: 91, station: 'Petrobras BR' },
  { id: 3, date: '14/07/2026', liters: '11,8 L', price: 6.99, value: 82.52, station: 'Ipiranga Norte' },
  { id: 4, date: '30/06/2026', liters: '12,2 L', price: 6.95, value: 84.79, station: 'Shell Centro' },
]

export const otherExpenses = [
  { id: 1, date: '05/08/2026', description: 'Óleo Motor Motul 10W-40', value: 142 },
  { id: 2, date: '20/07/2026', description: 'Pneu Traseiro Pirelli', value: 380 },
  { id: 3, date: '10/06/2026', description: 'Corrente DID', value: 210 },
]

export const dashboardExpenses = [
  { date: '12/08/2026', type: 'Abastecimento', value: 87.5 },
  { date: '05/08/2026', type: 'Óleo Motor', value: 142 },
  { date: '28/07/2026', type: 'Abastecimento', value: 91 },
  { date: '20/07/2026', type: 'Pneu Traseiro', value: 380 },
]

export const dashboardAlerts = [
  { id: 'oil', icon: '🛢', title: 'Troca de Óleo', description: 'Honda CB 500F — Vence em 200 km', urgent: true },
  { id: 'tires', icon: '💨', title: 'Calibragem dos Pneus', description: 'Última calibragem há 45 dias', urgent: false },
]

export const maintenanceAlerts = [
  { id: 'oil', icon: '🛢', title: 'Troca de Óleo', motorcycle: 'Honda CB 500F', currentKm: 28400, limitKm: 28600, tone: 'amber', status: 'Próximo do limite' },
  { id: 'revision', icon: '🔧', title: 'Revisão Periódica', motorcycle: 'Yamaha MT-07', currentKm: 41200, limitKm: 41000, tone: 'red', status: 'Limite ultrapassado' },
]

export const navigationItems = [
  { path: '/', label: 'Dashboard', icon: '◈' },
  { path: '/manutencoes', label: 'Manutenções', icon: '🔩' },
  { path: '/gastos', label: 'Gastos', icon: '💰' },
  { path: '/relatorios', label: 'Relatórios', icon: '📄' },
  { path: '/garagem', label: 'Garagem', icon: '🏍' },
  { path: '/configuracoes', label: 'Configurações', icon: '⚙️' },
  { path: '/alertas', label: 'Alertas', icon: '⚠️' },
]
