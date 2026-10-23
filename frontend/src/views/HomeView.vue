<script setup>
import { computed, onMounted, ref } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'
import MetricCard from '../components/MetricCard.vue'
import TaskStatus from '../components/TaskStatus.vue'
import DashboardInsights from '../components/DashboardInsights.vue'
import { api } from '../services/api'
import { workspace as s } from '../services/workspace'
import { formatCurrency, formatDate, serviceTypes, alertTypes, alertThresholdKm, today } from '../services/format'
import { useTask } from '../composables/useTask'
const { busy, error, notice, run } = useTask()
const records = ref([]), alerts = ref([])
const insights = ref(null)
const insightsBusy = ref(false)
const refreshing = computed(() => busy.value || insightsBusy.value)
const due = alert => (alert.km_limite > 0 && Number(s.motos.find(m => m.id === Number(s.motoId))?.km_atual || 0) >= alertThresholdKm(alert)) || (alert.data_limite && alert.data_limite <= today())
const activeAlerts = computed(() => alerts.value.filter(a => a.status !== 'concluido').sort((a, b) => Number(!!due(b)) - Number(!!due(a))))
const total = computed(() => records.value.filter(r => r.status === 'concluido').reduce((sum, r) => sum + Math.round(Number(r.valor) * 100), 0) / 100)
async function load() {
  if (!s.motoId) { records.value = []; alerts.value = []; return }
  const path = '/motocicletas/' + s.motoId
  const results = await Promise.allSettled([api.get(path + '/manutencoes'), api.get(path + '/alertas')])
  records.value = results[0].status === 'fulfilled' ? results[0].value.data : []
  alerts.value = results[1].status === 'fulfilled' ? results[1].value.data : []
  const failure = results.find(result => result.status === 'rejected')
  if (failure) throw failure.reason
}
onMounted(() => run(load))
async function refresh() {
  if (refreshing.value) return
  await Promise.all([run(load), insights.value?.reload()])
}
</script>
<template>
  <div class="page-stack home-page" :aria-busy="refreshing">
    <div class="page-heading">
      <div><SectionTitle>Visão Geral</SectionTitle><p class="page-description">Acompanhe os gastos, o histórico e os próximos cuidados da sua moto.</p></div>
      <button class="button button-ghost dashboard-refresh" :disabled="refreshing || !s.motoId" @click="refresh"><span aria-hidden="true">↻</span>{{ refreshing ? 'Atualizando…' : 'Atualizar' }}</button>
    </div>
    <TaskStatus :error="error" :notice="notice" />
    <section v-if="!s.motos.length" class="panel garage-empty"><h3>Sua próxima viagem começa na garagem</h3><p>Cadastre sua primeira moto para acompanhar os gastos e organizar a manutenção.</p><RouterLink to="/garagem" class="button button-lime">Cadastrar primeira moto</RouterLink></section>
    <template v-else>
      <DashboardInsights ref="insights" :records="records" @refresh="run(load)" @busy="insightsBusy = $event">
      <template #after-metrics><section class="dashboard-care-section">
        <SectionTitle>Cuidados Pendentes</SectionTitle>
        <div class="dashboard-alerts">
          <article v-for="a in activeAlerts" :key="a.id" class="dashboard-alert" :class="{ 'dashboard-alert-overdue': due(a) }">
            <div class="dashboard-alert-copy"><h3>{{alertTypes[a.tipo_alerta] || a.tipo_alerta}}</h3><p>{{a.km_limite ? 'Limite: ' + a.km_limite + ' km' : ''}} {{a.data_limite ? '· ' + formatDate(a.data_limite) : ''}} · {{a.status}}</p></div>
            <RouterLink to="/alertas" class="button button-lime">Ver alerta</RouterLink>
          </article>
          <p v-if="!busy && !activeAlerts.length">Nenhum alerta ativo. Configure seus próximos cuidados em Alertas.</p>
        </div>
      </section></template>
      </DashboardInsights>
      <div class="metrics-grid metrics-four">
        <MetricCard icon="🏍" label="Motos na garagem" :value="s.motos.length" accent />
        <MetricCard icon="🔩" label="Manutenções" :value="records.length" />
        <MetricCard icon="💰" label="Manutenções concluídas" :value="formatCurrency(total)" />
        <MetricCard icon="⚠️" label="Alertas ativos" :value="activeAlerts.length" />
      </div>
      <section>
        <SectionTitle>Últimas Manutenções</SectionTitle>
        <div v-for="r in records.slice(0, 5)" :key="r.id" class="dashboard-alert"><div>{{formatDate(r.data)}} · {{serviceTypes[r.tipo_servico]}}</div><strong>{{r.status === 'concluido' ? 'Feito' : 'Pendente'}}</strong></div>
        <p v-if="!busy && !records.length">Sem manutenções registradas.</p>
        <RouterLink to="/manutencoes" class="button button-ghost">Ver histórico</RouterLink>
      </section>
    </template>
  </div>
</template>
