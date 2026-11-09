import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import AlertsView from '../views/AlertsView.vue'
import MaintenanceView from '../views/MaintenanceView.vue'
import GarageView from '../views/GarageView.vue'
import SettingsView from '../views/SettingsView.vue'
import ExpensesView from '../views/ExpensesView.vue'
import ReportsView from '../views/ReportsView.vue'
import { workspace } from '../services/workspace'
import { routeEnabled } from '../services/navigation'

const routes = [
  { path: '/esqueci-senha', name: 'forgot-password', component: () => import('../views/PasswordRecoveryView.vue'), meta: { title: 'Recuperar senha', passwordRecovery: true } },
  { path: '/redefinir-senha', name: 'reset-password', component: () => import('../views/PasswordRecoveryView.vue'), meta: { title: 'Nova senha', passwordRecovery: true } },
  { path: '/apresentacao', name: 'presentation', component: () => import('../views/LandingView.vue'), meta: { title: 'MotoCare', presentation: true } },
  { path: '/', name: 'home', component: HomeView, meta: { title: 'Dashboard' } },
  { path: '/dashboard', name: 'dashboard', component: HomeView, meta: { title: 'Dashboard' } },
  { path: '/alertas', name: 'alerts', component: AlertsView, meta: { title: 'Alertas' } },
  { path: '/manutencoes', alias: '/historico', name: 'maintenance', component: MaintenanceView, meta: { title: 'Manutenções' } },
  { path: '/gastos', name: 'expenses', component: ExpensesView, meta: { title: 'Gastos', financeiro: true } },
  { path: '/relatorios', name: 'reports', component: ReportsView, meta: { title: 'Relatórios', financeiro: true } },
  { path: '/garagem', name: 'garage', component: GarageView, meta: { title: 'Garagem', motorcycleScoped: false } },
  { path: '/configuracoes', name: 'settings', component: SettingsView, meta: { title: 'Configurações', motorcycleScoped: false } },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({ history: createWebHistory(), routes })
router.beforeEach(to => {
  if (workspace.user && !routeEnabled(to, workspace.features)) return '/'
})
router.afterEach((to) => { document.title = to.meta.title + ' · MotoCare' })

export default router
