<script setup>
defineProps({ size: { type: Number, default: 80 } })
</script>

<template>
  <svg aria-hidden="true" viewBox="0 0 120 60" :width="size" :height="size / 2" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="22" cy="44" r="13" /><circle cx="22" cy="44" r="5" />
    <circle cx="96" cy="44" r="13" /><circle cx="96" cy="44" r="5" />
    <polyline points="35,44 42,22 58,18 68,24 80,28 96,31" />
    <line x1="42" y1="22" x2="38" y2="44" /><path d="M55,18 Q63,12 72,16" />
    <polyline points="80,28 86,18 96,16 98,22" /><rect x="44" y="24" width="20" height="14" rx="1" />
    <path d="M44,34 Q30,40 26,44" /><line x1="80" y1="28" x2="88" y2="44" />
  </svg>
</template>
