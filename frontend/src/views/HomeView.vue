<script setup>
import SectionTitle from '../components/SectionTitle.vue'
import MetricCard from '../components/MetricCard.vue'
import StatusBadge from '../components/StatusBadge.vue'
import DataTable from '../components/DataTable.vue'
import { dashboardAlerts, dashboardExpenses, formatCurrency, maintenanceRecords } from '../data/demo'

const latestMaintenance = [maintenanceRecords[0], maintenanceRecords[1], maintenanceRecords[2], maintenanceRecords[5]]
const expenseHeaders = [{ key: 'date', label: 'Data' }, { key: 'type', label: 'Tipo' }, { key: 'value', label: 'Valor' }]
const maintenanceHeaders = [{ key: 'date', label: 'Data' }, { key: 'type', label: 'Tipo' }, { key: 'status', label: 'Status' }]
</script>

<template>
  <div class="page-stack">
    <section aria-labelledby="overview-title">
      <SectionTitle id="overview-title">Visão Geral</SectionTitle>
      <div class="metrics-grid metrics-four">
        <MetricCard icon="🏍" label="Motos cadastradas" :value="2" accent />
        <MetricCard icon="🔩" label="Manutenções" :value="6" sub="este ano" />
        <MetricCard icon="💰" label="Gasto total" value="R$ 1.152" sub="2026" />
        <RouterLink class="metric-link" to="/alertas"><MetricCard icon="⚠️" label="Alertas" :value="2" sub="pendentes" /></RouterLink>
      </div>
    </section>
    <section aria-labelledby="pending-alerts-title">
      <SectionTitle id="pending-alerts-title">Alertas Pendentes</SectionTitle>
      <div class="dashboard-alerts">
        <article v-for="alert in dashboardAlerts" :key="alert.id" class="dashboard-alert" :class="{ 'dashboard-alert-urgent': alert.urgent }">
          <span class="dashboard-alert-icon" aria-hidden="true">{{ alert.icon }}</span>
          <div class="dashboard-alert-copy"><h3>{{ alert.title }}</h3><p>{{ alert.description }}</p></div>
          <StatusBadge v-if="alert.urgent" tone="lime">Urgente</StatusBadge>
          <RouterLink :to="alert.id === 'oil' ? '/alertas' : '/manutencoes'" class="button button-small" :class="alert.urgent ? 'button-lime' : 'button-ghost'" :aria-label="'Ver ' + alert.title">Ver</RouterLink>
        </article>
      </div>
    </section>
    <div class="dashboard-tables">
      <section aria-labelledby="recent-expenses-title">
        <SectionTitle id="recent-expenses-title">Gastos Recentes</SectionTitle>
        <DataTable :headers="expenseHeaders" :rows="dashboardExpenses" caption="Gastos recentes"><template #value="{ row }">{{ formatCurrency(row.value) }}</template></DataTable>
      </section>
      <section aria-labelledby="recent-maintenance-title">
        <SectionTitle id="recent-maintenance-title">Últimas Manutenções</SectionTitle>
        <DataTable :headers="maintenanceHeaders" :rows="latestMaintenance" caption="Últimas manutenções"><template #status="{ row }"><StatusBadge :tone="row.status === 'Feito' ? 'green' : 'amber'">{{ row.status }}</StatusBadge></template></DataTable>
      </section>
    </div>
  </div>
</template>
