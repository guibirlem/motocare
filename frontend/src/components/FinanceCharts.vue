<script setup>
import { computed, ref, watch } from 'vue'
import SeriesChart from './SeriesChart.vue'
import { formatCurrency, formatDate, fuelTypes } from '../services/format'
import { spendingLabels } from '../services/insights'
const props = defineProps({ statistics: { type: Array, default: () => [] }, prices: { type: Array, default: () => [] }, categories: { type: Object, default: () => ({}) }, motorcycles: { type: Array, default: () => [] } })
const motorcycle = ref(''), station = ref('')
const fuels = computed(() => [...new Map(props.prices.map(row => [`${row.posto_id}|${row.combustivel}`, `${row.posto} · ${fuelTypes[row.combustivel] || row.combustivel}`])).entries()])
watch(() => props.statistics, () => { if (!props.statistics.some(row => String(row.motocicleta_id) === motorcycle.value)) motorcycle.value = String(props.statistics[0]?.motocicleta_id || '') }, { immediate: true })
watch(fuels, () => { if (!fuels.value.some(([key]) => key === station.value)) station.value = fuels.value[0]?.[0] || '' }, { immediate: true })
const cycles = computed(() => props.statistics.find(row => String(row.motocicleta_id) === motorcycle.value)?.ciclos || [])
const pricePoints = computed(() => props.prices.filter(row => `${row.posto_id}|${row.combustivel}` === station.value).map(row => ({ label: row.mes, value: row.preco_medio })))
const maximum = computed(() => Math.max(1, ...Object.values(props.categories).map(Number)))
</script>
<template>
  <div class="insight-charts">
    <section class="panel">
      <label v-if="statistics.length > 1" class="field">Moto do gráfico de consumo<select v-model="motorcycle"><option v-for="row in statistics" :key="row.motocicleta_id" :value="String(row.motocicleta_id)">{{ motorcycles.find(m => m.id === row.motocicleta_id)?.modelo || row.motocicleta_id }}</option></select></label>
      <SeriesChart title="Evolução do consumo" unit="km/L" :points="cycles.map(row => ({ label: formatDate(row.data), value: row.km_l }))" />
      <p class="field-hint">Cada ponto fecha um ciclo entre tanques cheios, incluindo parciais.</p>
    </section>
    <section class="panel">
      <label class="field">Posto e combustível<select v-model="station" :disabled="!fuels.length"><option v-if="!fuels.length" value="">Sem preços registrados</option><option v-for="[key, name] in fuels" :key="key" :value="key">{{ name }}</option></select></label>
      <SeriesChart title="Preço médio mensal" unit="R$/L" :points="pricePoints" />
      <p class="field-hint">Média dos preços informados no posto, por mês e combustível.</p>
    </section>
    <section class="panel spending-chart"><h3>Divisão dos gastos</h3>
      <div v-for="(name, key) in spendingLabels" :key="key" class="spending-row"><div><span>{{ name }}</span><strong>{{ formatCurrency(categories[key]) }}</strong></div><div class="spending-track" aria-hidden="true"><span :style="{ width: Number(categories[key] || 0) * 100 / maximum + '%' }"></span></div></div>
    </section>
  </div>
</template>
