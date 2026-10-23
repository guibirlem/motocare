<script setup>
defineProps({ name: String })
const paths = {
  fuel: ['M4 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16', 'M3 21h12M6 7h6v5H6z', 'M14 11h2a2 2 0 0 1 2 2v4a2 2 0 0 0 4 0V9l-4-4', 'M19 6v4h3'],
  service: ['M14 6a5 5 0 0 0-6 6L3 17a3 3 0 0 0 4 4l5-5a5 5 0 0 0 6-6l-3 3-4-4 3-3Z'],
  bell: ['M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9', 'M10 21h4'],
  chart: ['M4 3v17h17', 'm7 14 4-5 4 3 5-7'],
  chain: ['m9 15 6-6', 'M7 17 5 19a4 4 0 0 1-5-5l5-5a4 4 0 0 1 5 0', 'm17 7 2-2a4 4 0 0 1 5 5l-5 5a4 4 0 0 1-5 0'],
  drop: ['M12 3s-7 8-7 12a7 7 0 0 0 14 0c0-4-7-12-7-12Z', 'M9 16a3 3 0 0 0 3 3'],
  wallet: ['M20 8V5H5a3 3 0 0 0 0 6h16v10H5a3 3 0 0 1-3-3V8', 'M17 15h4'],
  shield: ['m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z', 'm8 12 3 3 5-5'],
  history: ['M4 7a9 9 0 1 1-1 9', 'M4 2v5h5', 'M12 7v6l4 2'],
}
</script>
<template><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path v-for="(path, index) in paths[name] || paths.chart" :key="index" :d="path" /></svg></template>
