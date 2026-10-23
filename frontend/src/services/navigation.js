export function navigationItems(features) {
  return [
    { path: '/', label: 'Dashboard', icon: '◈' },
    { path: '/manutencoes', label: 'Manutenções', icon: '🔩' },
    ...(features.financeiro ? [
      { path: '/gastos', label: 'Gastos', icon: '💰' },
      { path: '/relatorios', label: 'Relatórios', icon: '📊' },
    ] : []),
    { path: '/garagem', label: 'Garagem', icon: '🏍' },
    { path: '/configuracoes', label: 'Configurações', icon: '⚙️' },
    { path: '/alertas', label: 'Alertas', icon: '⚠️' },
  ]
}

export function routeEnabled(route, features) {
  return !route.meta.financeiro || features.financeiro === true
}
