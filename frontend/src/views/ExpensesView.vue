<script setup>
import { ref } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'
import MetricCard from '../components/MetricCard.vue'
import DataTable from '../components/DataTable.vue'
import { formatCurrency, fuelRecords, otherExpenses } from '../data/demo'

const formType = ref('')
const notice = ref('')
const fuelHeaders = [{ key: 'date', label: 'Data' }, { key: 'liters', label: 'Litros' }, { key: 'price', label: 'Preço/L' }, { key: 'value', label: 'Total' }, { key: 'station', label: 'Posto' }]
const expenseHeaders = [{ key: 'date', label: 'Data' }, { key: 'description', label: 'Descrição' }, { key: 'value', label: 'Valor' }]
function showForm(type) {
  formType.value = type
  notice.value = ''
}
</script>

<template>
  <div class="page-stack">
    <SectionTitle>Gastos &amp; Abastecimentos</SectionTitle>
    <div class="metrics-grid metrics-three">
      <MetricCard icon="⛽" label="Abastecimentos" :value="4" sub="últimos 3 meses" accent />
      <MetricCard icon="💰" label="Total combustível" value="R$ 345,81" />
      <MetricCard icon="🔧" label="Peças e serviços" value="R$ 807,00" />
    </div>
    <section aria-labelledby="fuel-title">
      <SectionTitle id="fuel-title">Abastecimentos</SectionTitle>
      <DataTable :headers="fuelHeaders" :rows="fuelRecords" caption="Abastecimentos"><template #price="{ row }">{{ formatCurrency(row.price) }}</template><template #value="{ row }">{{ formatCurrency(row.value) }}</template></DataTable>
      <div class="table-bottom-action"><button class="button button-lime" type="button" @click="showForm('fuel')">+ Novo Abastecimento</button></div>
    </section>
    <section aria-labelledby="expenses-title">
      <SectionTitle id="expenses-title">Outros Gastos</SectionTitle>
      <DataTable :headers="expenseHeaders" :rows="otherExpenses" caption="Outros gastos"><template #value="{ row }">{{ formatCurrency(row.value) }}</template></DataTable>
      <div class="table-bottom-action"><button class="button button-lime" type="button" @click="showForm('expense')">+ Novo Gasto</button></div>
    </section>
    <form v-if="formType" class="demo-form" @submit.prevent="notice = 'Demonstração: nenhum gasto foi salvo.'">
      <h3 class="form-heading">{{ formType === 'fuel' ? 'Novo Abastecimento' : 'Novo Gasto' }}</h3>
      <div class="form-grid">
        <label class="field">Data<input type="date" required /></label>
        <label class="field">Valor (R$)<input inputmode="decimal" placeholder="Ex: 87,50" required /></label>
        <template v-if="formType === 'fuel'"><label class="field">Litros<input inputmode="decimal" placeholder="Ex: 12,5" /></label><label class="field">Posto<input placeholder="Ex: Shell Centro" /></label></template>
        <label v-else class="field field-full">Descrição<input placeholder="Ex: Óleo Motor" /></label>
      </div>
      <div class="form-actions"><button class="button button-black" type="submit">Registrar</button><button class="button button-ghost" type="button" @click="formType = ''">Cancelar</button></div>
      <p class="field-hint">Formulário demonstrativo. As alterações não são salvas.</p>
    </form>
    <p v-if="notice" class="demo-notice" role="status">{{ notice }}</p>
  </div>
</template>
