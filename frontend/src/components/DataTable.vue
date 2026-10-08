<script setup>
defineProps({
  headers: { type: Array, required: true },
  rows: { type: Array, required: true },
  caption: { type: String, required: true },
})
</script>

<template>
  <div class="table-scroll" tabindex="0" :aria-label="caption">
    <table class="data-table">
      <caption class="sr-only">{{ caption }}</caption>
      <thead><tr><th v-for="header in headers" :key="header.key" scope="col">{{ header.label }}</th></tr></thead>
      <tbody>
        <tr v-for="(row, index) in rows" :key="row.id ?? index">
          <td v-for="header in headers" :key="header.key">
            <slot :name="header.key" :row="row">{{ row[header.key] }}</slot>
          </td>
        </tr>
        <tr v-if="!rows.length"><td :colspan="headers.length" class="empty-table">Nenhum registro encontrado.</td></tr>
      </tbody>
    </table>
  </div>
</template>
