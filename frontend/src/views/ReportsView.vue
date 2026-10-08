<script setup>
import { ref } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'
import DataTable from '../components/DataTable.vue'
import { dashboardExpenses, formatCurrency, motorcycles } from '../data/demo'

const reportType = ref('Gastos Mensais')
const startMonth = ref('Janeiro/2026')
const endMonth = ref('Agosto/2026')
const motorcycle = ref('Todas')
const previewOpen = ref(false)
const notice = ref('')
const months = ['Janeiro/2026', 'Fevereiro/2026', 'Março/2026', 'Abril/2026', 'Maio/2026', 'Junho/2026', 'Julho/2026', 'Agosto/2026']
const headers = [{ key: 'date', label: 'Data' }, { key: 'type', label: 'Tipo' }, { key: 'value', label: 'Valor' }]
</script>

<template>
  <div class="page-stack page-stack-compact">
    <SectionTitle>Relatórios &amp; Exportar PDF</SectionTitle>
    <div class="reports-layout">
      <form class="panel" @submit.prevent="previewOpen = true">
        <h3 class="panel-heading">Configurar Relatório</h3>
        <div class="fields-stack">
          <label class="field">Tipo de Relatório<select v-model="reportType"><option v-for="type in ['Gastos Mensais', 'Manutenções', 'Abastecimentos', 'Completo']" :key="type">{{ type }}</option></select></label>
          <label class="field">De<select v-model="startMonth"><option v-for="month in months" :key="month">{{ month }}</option></select></label>
          <label class="field">Até<select v-model="endMonth"><option v-for="month in [...months].reverse()" :key="month">{{ month }}</option></select></label>
          <label class="field">Moto<select v-model="motorcycle"><option>Todas</option><option v-for="moto in motorcycles" :key="moto.id">{{ moto.name }}</option></select></label>
        </div>
        <div class="form-actions"><button class="button button-lime" type="submit">Pré-visualizar</button><button class="button button-black" type="button" @click="notice = 'A exportação de PDF é apenas demonstrativa neste protótipo.'">⬇ Exportar PDF</button></div>
        <p v-if="notice" class="field-hint" role="status">{{ notice }}</p>
      </form>
      <div class="report-preview" :class="{ 'report-preview-filled': previewOpen }">
        <template v-if="!previewOpen"><span class="report-preview-icon" aria-hidden="true">📄</span><h3>Pré-visualização do Relatório</h3><p>Configure e clique em “Pré-visualizar”</p></template>
        <template v-else><div class="report-preview-header"><strong>MotoCare</strong><span>EXEMPLO</span></div><h3>{{ reportType }}</h3><p>{{ startMonth }} — {{ endMonth }} · {{ motorcycle }}</p><DataTable :headers="headers" :rows="dashboardExpenses" caption="Exemplo visual de relatório"><template #value="{ row }">{{ formatCurrency(row.value) }}</template></DataTable><p class="field-hint">Prévia ilustrativa com dados de exemplo.</p></template>
      </div>
    </div>
  </div>
</template>
