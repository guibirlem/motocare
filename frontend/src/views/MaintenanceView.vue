<script setup>
import { computed, ref } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'
import MetricCard from '../components/MetricCard.vue'
import StatusBadge from '../components/StatusBadge.vue'
import DataTable from '../components/DataTable.vue'
import { formatCurrency, formatKm, maintenanceRecords, motorcycles } from '../data/demo'

const selectedFilter = ref('Todos')
const formOpen = ref(false)
const editingRecord = ref(null)
const notice = ref('')
const form = ref({})
const filteredRecords = computed(() => maintenanceRecords.filter((record) => selectedFilter.value === 'Todos' || record.status === selectedFilter.value))
const completedCount = maintenanceRecords.filter((record) => record.status === 'Feito').length
const totalSpent = maintenanceRecords.filter((record) => record.status === 'Feito').reduce((total, record) => total + record.value, 0)
const headers = [
  { key: 'date', label: 'Data' }, { key: 'motorcycle', label: 'Moto' },
  { key: 'type', label: 'Tipo de Serviço' }, { key: 'description', label: 'Descrição' },
  { key: 'value', label: 'Valor' }, { key: 'km', label: 'KM' },
  { key: 'status', label: 'Status' }, { key: 'actions', label: 'Ações' },
]
function openForm(record = null) {
  editingRecord.value = record
  form.value = record ? { ...record, date: record.date.split('/').reverse().join('-') } : { date: '', type: '', description: '', value: '', km: '', motorcycle: motorcycles[0].name, status: 'Feito' }
  formOpen.value = true
  notice.value = ''
}
function showDemoNotice() {
  notice.value = 'Esta é uma demonstração. Os registros de exemplo não foram alterados.'
}
</script>

<template>
  <div class="page-stack page-stack-compact">
    <SectionTitle>Histórico de Manutenções</SectionTitle>
    <div class="metrics-grid metrics-four">
      <MetricCard icon="🔩" label="Total" :value="maintenanceRecords.length" accent />
      <MetricCard icon="✅" label="Concluídos" :value="completedCount" />
      <MetricCard icon="⏳" label="Pendentes" :value="maintenanceRecords.length - completedCount" />
      <MetricCard icon="💰" label="Total gasto" :value="formatCurrency(totalSpent)" />
    </div>
    <div class="toolbar">
      <button class="button button-lime" type="button" :aria-expanded="formOpen" aria-controls="maintenance-form" @click="openForm()">+ Registrar Manutenção</button>
      <div class="filter-group" role="group" aria-label="Filtrar por status"><button v-for="filter in ['Todos', 'Feito', 'Pendente']" :key="filter" type="button" :class="{ selected: selectedFilter === filter }" :aria-pressed="selectedFilter === filter" @click="selectedFilter = filter">{{ filter }}</button></div>
    </div>
    <form v-if="formOpen" id="maintenance-form" class="demo-form" @submit.prevent="showDemoNotice">
      <h3 class="form-heading">{{ editingRecord ? 'Editar Manutenção' : 'Nova Manutenção' }}</h3>
      <div class="form-grid">
        <label class="field">Data *<input v-model="form.date" type="date" required /></label>
        <label class="field">Moto<select v-model="form.motorcycle"><option v-for="moto in motorcycles" :key="moto.id">{{ moto.name }}</option></select></label>
        <label class="field">Tipo de Serviço *<input v-model="form.type" placeholder="Ex: Troca de óleo" required /></label>
        <label class="field">Descrição<input v-model="form.description" placeholder="Ex: Óleo Motul 10W-40" /></label>
        <label class="field">Valor (R$)<input v-model="form.value" inputmode="decimal" placeholder="Ex: 142,00" /></label>
        <label class="field">KM no momento<input v-model="form.km" inputmode="numeric" placeholder="Ex: 28.400" /></label>
        <label class="field">Status<select v-model="form.status"><option>Feito</option><option>Pendente</option></select></label>
      </div>
      <div class="form-actions"><button class="button button-black" type="submit">{{ editingRecord ? 'Salvar' : 'Registrar' }}</button><button class="button button-ghost" type="button" @click="formOpen = false">Cancelar</button></div>
      <p class="field-hint">Formulário demonstrativo. As alterações não são salvas.</p>
    </form>
    <p v-if="notice" class="demo-notice" role="status">{{ notice }}</p>
    <DataTable :headers="headers" :rows="filteredRecords" caption="Histórico de manutenções">
      <template #type="{ row }"><strong>{{ row.type }}</strong></template>
      <template #description="{ row }"><span class="muted">{{ row.description }}</span></template>
      <template #value="{ row }">{{ formatCurrency(row.value) }}</template>
      <template #km="{ row }">{{ row.km === null ? '—' : formatKm(row.km) + ' km' }}</template>
      <template #status="{ row }"><StatusBadge :tone="row.status === 'Feito' ? 'green' : 'amber'">{{ row.status === 'Feito' ? '✓ Feito' : '⏳ Pendente' }}</StatusBadge></template>
      <template #actions="{ row }"><div class="table-actions"><button class="button button-small button-ghost" type="button" :aria-label="'Editar ' + row.type" @click="openForm(row)">Editar</button><button class="button button-small button-danger" type="button" :aria-label="'Excluir ' + row.type" @click="showDemoNotice">Excluir</button></div></template>
    </DataTable>
  </div>
</template>
