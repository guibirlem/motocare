<script setup>
defineProps({ tone: { type: String, default: 'gray' } })
</script>

<template>
  <span class="status-badge" :class="'status-' + tone"><slot /></span>
</template>
