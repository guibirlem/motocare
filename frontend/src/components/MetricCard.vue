<script setup>
defineProps({
  icon: String,
  label: String,
  value: [String, Number],
  sub: String,
  accent: Boolean,
})
</script>

<template>
  <article class="metric-card" :class="{ 'metric-card-accent': accent }">
    <span class="metric-icon" aria-hidden="true">{{ icon }}</span>
    <p class="metric-label">{{ label }}</p>
    <strong class="metric-value">{{ value }}</strong>
    <p v-if="sub" class="metric-sub">{{ sub }}</p>
  </article>
</template>
