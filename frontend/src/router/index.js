import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import AlertsView from '../views/AlertsView.vue'
import MaintenanceView from '../views/MaintenanceView.vue'
import GarageView from '../views/GarageView.vue'
import SettingsView from '../views/SettingsView.vue'

const routes = [
  { path: '/', alias: '/dashboard', name: 'home', component: HomeView, meta: { title: 'Dashboard' } },
  { path: '/alertas', name: 'alerts', component: AlertsView, meta: { title: 'Alertas' } },
  { path: '/manutencoes', alias: '/historico', name: 'maintenance', component: MaintenanceView, meta: { title: 'Manutenções' } },
  { path: '/garagem', name: 'garage', component: GarageView, meta: { title: 'Garagem' } },
  { path: '/configuracoes', name: 'settings', component: SettingsView, meta: { title: 'Configurações' } },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({ history: createWebHistory(), routes })
router.afterEach((to) => { document.title = to.meta.title + ' · MotoCare' })

export default router
