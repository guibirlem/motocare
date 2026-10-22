<script setup>
import { computed, onMounted, ref } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'
import MetricCard from '../components/MetricCard.vue'
import TaskStatus from '../components/TaskStatus.vue'
import { api } from '../services/api'
import { workspace as s } from '../services/workspace'
import { formatCurrency, formatDate, serviceTypes } from '../services/format'
import { useTask } from '../composables/useTask'
const { busy, error, notice, run } = useTask()
const records = ref([]), alerts = ref([])
const activeAlerts = computed(() => alerts.value.filter(a => a.status !== 'concluido'))
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
</script>
<template>
  <div class="page-stack">
    <SectionTitle>Visão Geral</SectionTitle>
    <TaskStatus :busy="busy" :error="error" :notice="notice" />
    <button class="button button-ghost" :disabled="busy" @click="run(load)">Atualizar</button>
    <p v-if="!s.motos.length">Sua garagem está vazia. <RouterLink to="/garagem" class="button button-lime">Cadastrar primeira moto</RouterLink></p>
    <template v-else>
      <p>Histórico e cuidados da moto selecionada.</p>
      <div class="metrics-grid metrics-four">
        <MetricCard icon="🏍" label="Motos na garagem" :value="s.motos.length" accent />
        <MetricCard icon="🔩" label="Manutenções" :value="records.length" />
        <MetricCard icon="💰" label="Manutenções concluídas" :value="formatCurrency(total)" />
        <MetricCard icon="⚠️" label="Alertas ativos" :value="activeAlerts.length" />
      </div>
      <section>
        <SectionTitle>Cuidados Pendentes</SectionTitle>
        <div class="dashboard-alerts">
          <article v-for="a in activeAlerts" :key="a.id" class="dashboard-alert">
            <div class="dashboard-alert-copy"><h3>{{a.tipo_alerta === 'troca_oleo' ? 'Troca de óleo' : 'Revisão'}}</h3><p>{{a.km_limite ? 'Limite: ' + a.km_limite + ' km' : ''}} {{a.data_limite ? '· ' + formatDate(a.data_limite) : ''}} · {{a.status}}</p></div>
            <RouterLink to="/alertas" class="button button-lime">Ver alerta</RouterLink>
          </article>
          <p v-if="!busy && !activeAlerts.length">Nenhum alerta ativo. Configure seus próximos cuidados em Alertas.</p>
        </div>
      </section>
      <section>
        <SectionTitle>Últimas Manutenções</SectionTitle>
        <div v-for="r in records.slice(0, 5)" :key="r.id" class="dashboard-alert"><div>{{formatDate(r.data)}} · {{serviceTypes[r.tipo_servico]}}</div><strong>{{r.status === 'concluido' ? 'Feito' : 'Pendente'}}</strong></div>
        <p v-if="!busy && !records.length">Sem manutenções registradas.</p>
        <RouterLink to="/manutencoes" class="button button-ghost">Ver histórico</RouterLink>
      </section>
    </template>
  </div>
</template>
