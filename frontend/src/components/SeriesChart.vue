<script setup>
import { computed } from 'vue'
const props = defineProps({ title: String, unit: String, points: { type: Array, default: () => [] } })
const maximum = computed(() => Math.max(1, ...props.points.map(point => Number(point.value))))
const dots = computed(() => props.points.map((point, index) => ({ ...point, x: props.points.length === 1 ? 200 : 32 + index * 336 / (props.points.length - 1), y: 134 - Number(point.value) * 110 / maximum.value })))
</script>
<template>
  <section class="insight-chart">
    <h3>{{ title }}</h3>
    <template v-if="dots.length">
      <svg class="series-svg" viewBox="0 0 400 170" role="img" :aria-label="title + '. Valores disponíveis na tabela abaixo.'">
        <line x1="30" y1="134" x2="370" y2="134" class="chart-axis" />
        <polyline v-if="dots.length > 1" :points="dots.map(p => `${p.x},${p.y}`).join(' ')" class="chart-line" />
        <circle v-for="(point, index) in dots" :key="index" :cx="point.x" :cy="point.y" r="4" class="chart-dot"><title>{{ point.label }}: {{ point.value }} {{ unit }}</title></circle>
        <text x="30" y="16">{{ maximum.toLocaleString('pt-BR') }} {{ unit }}</text>
        <text x="30" y="158">{{ dots[0].label }}</text><text v-if="dots.length > 1" x="370" y="158" text-anchor="end">{{ dots.at(-1).label }}</text>
      </svg>
      <details><summary>Ver valores de {{ title.toLocaleLowerCase('pt-BR') }}</summary><div class="table-scroll"><table class="data-table"><caption class="sr-only">{{ title }}</caption><thead><tr><th>Período</th><th>{{ unit }}</th></tr></thead><tbody><tr v-for="(point, index) in points" :key="index"><td>{{ point.label }}</td><td>{{ Number(point.value).toLocaleString('pt-BR') }}</td></tr></tbody></table></div></details>
    </template>
    <p v-else class="field-hint">Ainda não há registros suficientes para este gráfico.</p>
  </section>
</template>
