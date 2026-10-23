<script setup>
import { computed, onMounted, ref } from 'vue'
import MetricCard from './MetricCard.vue'
import TaskStatus from './TaskStatus.vue'
import FinanceCharts from './FinanceCharts.vue'
import { api } from '../services/api'
import { workspace as s } from '../services/workspace'
import { useTask } from '../composables/useTask'
import { feedEntries } from '../services/insights'
import { formatCurrency, formatDate, formatKm, serviceTypes, expenseTypes, today } from '../services/format'
const props = defineProps({ records: { type: Array, default: () => [] } })
const emit = defineEmits(['refresh'])
const { busy, error, notice, run } = useTask()
const data = ref(null), category = ref(''), month = ref(today().slice(0, 7))
const moto = computed(() => s.motos.find(row => row.id === Number(s.motoId)))
const feed = computed(() => feedEntries(props.records, data.value?.despesas || [], category.value, month.value))
const limit = ref(20)
async function load() { data.value = null; data.value = (await api.get('/motocicletas/' + s.motoId + '/painel')).data }
async function rain() {
  await run(async () => {
    await api.post('/motocicletas/' + s.motoId + '/chuva-lavagem')
    emit('refresh')
    await load()
  }, 'Lembrete de lubrificação ativo para hoje. O scheduler enviará o push quando estiver em execução e houver notificações ativadas.')
}
defineExpose({ reload: () => run(load) })
onMounted(() => run(load))
</script>
<template>
  <section class="daily-dashboard page-stack">
    <TaskStatus :busy="busy" :error="error" :notice="notice" />
    <button v-if="error" class="button button-ghost" :disabled="busy" @click="run(load)">Tentar carregar painel</button>
    <template v-if="data">
      <div class="metrics-grid metrics-four">
        <MetricCard icon="🏍" label="Hodômetro atual" :value="formatKm(moto?.km_atual || 0) + ' km'" accent />
        <MetricCard icon="💰" label="Gasto total do mês" :value="formatCurrency(data.gasto_mes)" />
        <MetricCard v-if="s.features.financeiro" icon="⛽" label="Consumo médio geral" :value="data.consumo_medio == null ? '—' : formatKm(data.consumo_medio) + ' km/L'" sub="Média ponderada entre tanques cheios" />
        <MetricCard v-if="s.features.financeiro" icon="↗" label="Custo por quilômetro" :value="data.custo_km == null ? '—' : formatCurrency(data.custo_km) + '/km'" sub="Custos desde a primeira leitura registrada" />
      </div>
      <div class="daily-care-grid">
        <section class="panel chain-panel"><h3>Kit relação e corrente</h3><p v-if="data.corrente.kit_instalado_em">Kit instalado em {{ formatDate(data.corrente.kit_instalado_em) }} · {{ formatKm(data.corrente.km_instalacao) }} km.</p><p v-else>Registre a troca do kit para iniciar a contagem.</p><p><strong>{{ data.corrente.ajustes_desde_troca ?? '—' }}</strong> ajustes de tensão desde a troca do kit.</p><p class="field-hint">Contador de histórico; o limite de desgaste exige inspeção. Lubrificação, ajuste e troca são serviços independentes.</p><button class="button button-lime" :disabled="busy" @click="rain">Peguei chuva / Lavei a moto</button><RouterLink class="button button-ghost" to="/alertas">Programar cuidado da corrente</RouterLink></section>
        <section v-if="s.features.financeiro" class="panel reserve-panel"><h3>Hodômetro parcial e reserva estimada</h3>
          <template v-if="data.reserva"><p>Parcial desde o último tanque cheio: <strong>{{ formatKm(data.reserva.parcial_km) }} km</strong></p><label class="reserve-label">Tanque estimado: {{ formatKm(data.reserva.litros_estimados) }} L<progress :value="data.reserva.percentual" max="100">{{ data.reserva.percentual }}%</progress></label><p><strong>{{ formatKm(data.reserva.km_ate_reserva) }} km</strong> até a reserva · <strong>{{ formatKm(data.reserva.km_ate_secar) }} km</strong> até esvaziar.</p><p class="field-hint">Estimativa pelos últimos cinco ciclos ({{ formatKm(data.reserva.consumo_referencia) }} km/L). Condições de uso alteram o consumo; não espere o tanque esvaziar.</p></template>
          <p v-else class="field-hint">Informe capacidade e reserva na Garagem, registre dois tanques cheios com KM e os abastecimentos parciais entre eles. Mantenha o odômetro atualizado.</p>
          <RouterLink to="/garagem" class="button button-ghost">Configurar tanque / atualizar KM</RouterLink>
        </section>
      </div>
      <div class="dashboard-analysis-grid">
        <section class="panel timeline-panel"><h3>Linha do tempo</h3><div class="toolbar"><label class="field">Categoria do histórico<select v-model="category"><option value="">Todas</option><option value="manutencao">Manutenção</option><option v-if="s.features.financeiro" value="combustivel">Abastecimento</option><option v-if="s.features.financeiro" value="despesa">Despesas</option></select></label><label class="field">Período do histórico<input v-model="month" type="month" /></label><button class="button button-ghost" @click="month = ''">Todos os períodos</button></div>
          <ol class="record-feed"><li v-for="row in feed.slice(0, limit)" :key="row.key"><div><time>{{ formatDate(row.data) }}</time><strong>{{ row.category === 'manutencao' ? serviceTypes[row.tipo_servico] : expenseTypes[row.tipo_despesa] }}</strong><p>{{ row.descricao || row.posto || 'Sem descrição' }}</p><span v-if="row.category === 'manutencao'">{{ row.status === 'concluido' ? 'Feito' : 'Pendente' }} · {{ row.km_servico == null ? 'KM não informado' : formatKm(row.km_servico) + ' km' }}</span><span v-else-if="row.km_abastecimento != null">{{ formatKm(row.km_abastecimento) }} km · {{ row.tanque_cheio ? 'Tanque cheio' : 'Parcial' }}</span></div><strong>{{ formatCurrency(row.valor) }}</strong></li></ol>
          <p v-if="!feed.length">Nenhum registro neste filtro.</p><button v-if="feed.length > limit" class="button button-ghost" @click="limit += 20">Mostrar mais registros</button>
        </section>
        <FinanceCharts v-if="s.features.financeiro" :statistics="[{ motocicleta_id: Number(s.motoId), ciclos: data.ciclos }]" :prices="data.precos_mensais" :categories="data.categorias" :motorcycles="s.motos" />
      </div>
    </template>
  </section>
</template>
