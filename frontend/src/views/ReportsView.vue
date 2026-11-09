<script setup>
import { computed, ref, watch } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'
import MetricCard from '../components/MetricCard.vue'
import TaskStatus from '../components/TaskStatus.vue'
import FinanceCharts from '../components/FinanceCharts.vue'
import { api } from '../services/api'
import { workspace as s } from '../services/workspace'
import { formatCurrency, formatDate, reportParams, today } from '../services/format'
import { useTask } from '../composables/useTask'
import { reportEntries, reportCsv } from '../services/finance'

const { busy, error, notice, run } = useTask()
const start = ref(today().slice(0, 4) + '-01-01'), end = ref(today()), motoId = ref(s.motoId)
const data = ref(null), range = ref(null)
const lines = computed(() => data.value ? reportEntries(data.value) : [])
const motoName = id => {
  const moto = data.value?.motocicletas.find(item => item.id === id)
  return moto ? `${moto.marca} ${moto.modelo} · ${moto.placa}` : ''
}
watch([start, end, motoId], () => { data.value = null; range.value = null; error.value = ''; notice.value = '' })
async function load() {
  if (busy.value) return
  const params = reportParams(start.value, end.value, motoId.value)
  data.value = null
  range.value = null
  await run(async () => {
    const response = await api.get('/relatorios', { params })
    data.value = response.data
    range.value = { start: params.inicio, end: params.fim, moto: params.motocicleta_id ? motoName(Number(params.motocicleta_id)) : 'Todas as motos' }
  })
}
function csv() {
  if (!data.value) return
  const blob = new Blob([reportCsv(data.value)], { type: 'text/csv;charset=utf-8' })
  const url = URL.createObjectURL(blob), anchor = document.createElement('a')
  anchor.href = url
  anchor.download = `motocare-${range.value.start}-${range.value.end}.csv`
  document.body.appendChild(anchor)
  anchor.click()
  anchor.remove()
  setTimeout(() => URL.revokeObjectURL(url), 1000)
}
function print() { window.print() }
</script>

<template>
  <div class="page-stack">
    <SectionTitle>Relatórios</SectionTitle>
    <TaskStatus :busy="busy" :error="error" :notice="notice" />
    <form class="panel no-print" @submit.prevent="load">
      <fieldset :disabled="busy">
        <div class="form-grid">
          <label class="field">De<input v-model="start" type="date" required /></label>
          <label class="field">Até<input v-model="end" type="date" :min="start" required /></label>
          <label class="field">Moto<select v-model="motoId"><option value="">Todas</option><option v-for="moto in s.motos" :key="moto.id" :value="moto.id">{{ moto.marca }} {{ moto.modelo }} · {{ moto.placa }}</option></select></label>
        </div>
        <div class="form-actions">
          <button class="button button-lime">Gerar relatório</button>
          <button type="button" class="button button-ghost" :disabled="!data" @click="csv">Baixar CSV</button>
          <button type="button" class="button button-black" :disabled="!data" @click="print">Imprimir / Salvar PDF</button>
        </div>
      </fieldset>
      <p class="field-hint">Período de até dez anos. Para PDF, selecione “Salvar como PDF” na janela de impressão do navegador.</p>
    </form>
    <article v-if="data" class="report-content">
      <h2>MotoCare · Relatório financeiro</h2>
      <p>{{ formatDate(range.start) }} a {{ formatDate(range.end) }} · {{ range.moto }}</p>
      <div class="metrics-grid metrics-four">
        <MetricCard icon="⛽" label="Combustível" :value="formatCurrency(data.resumo.combustivel)" />
        <MetricCard icon="🔧" label="Reparos" :value="formatCurrency(data.resumo.reparos)" />
        <MetricCard icon="🔩" label="Manutenções concluídas" :value="formatCurrency(data.resumo.manutencoes)" />
        <MetricCard icon="∑" label="Total combinado" :value="formatCurrency(data.resumo.total)" accent />
      </div>
      <p class="field-hint">Pendentes aparecem nos lançamentos, mas não entram nos totais. Registre cada gasto apenas uma vez, em despesas ou manutenções.</p>
      <h3>Resumo por mês</h3>
      <FinanceCharts :statistics="data.estatisticas || []" :prices="data.precos_mensais || []" :categories="data.categorias || {}" :motorcycles="data.motocicletas" />
      <div class="table-scroll"><table class="data-table report-monthly">
        <caption class="sr-only">Resumo financeiro mensal</caption>
        <thead><tr><th>Mês</th><th>Combustível</th><th>Reparos</th><th>Manutenções</th><th>Demais despesas</th><th>Total</th></tr></thead>
        <tbody><tr v-for="row in data.mensal" :key="row.mes"><td>{{ row.mes }}</td><td>{{ formatCurrency(row.combustivel) }}</td><td>{{ formatCurrency(row.reparos) }}</td><td>{{ formatCurrency(row.manutencoes) }}</td><td>{{ formatCurrency(row.outras_despesas) }}</td><td>{{ formatCurrency(row.total) }}</td></tr></tbody>
      </table></div>
      <h3>Lançamentos</h3>
      <div class="table-scroll"><table class="data-table report-entries">
        <caption class="sr-only">Lançamentos do relatório</caption>
        <thead><tr><th>Data</th><th>Moto</th><th>Tipo</th><th>Descrição</th><th>Valor</th><th>Status</th></tr></thead>
        <tbody><tr v-for="row in lines" :key="row.key"><td>{{ formatDate(row.data) }}</td><td class="wrap-text">{{ motoName(row.motocicleta_id) }}</td><td>{{ row.category }}</td><td class="wrap-text">{{ row.descricao || row.posto || '—' }}</td><td>{{ formatCurrency(row.valor) }}</td><td>{{ row.status }}</td></tr><tr v-if="!lines.length"><td colspan="6" class="empty-table">Nenhum lançamento no período.</td></tr></tbody>
      </table></div>
    </article>
    <p v-else-if="!busy">Escolha o período e gere seu relatório.</p>
  </div>
</template>
