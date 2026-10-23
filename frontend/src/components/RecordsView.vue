<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import SectionTitle from './SectionTitle.vue'
import MetricCard from './MetricCard.vue'
import TaskStatus from './TaskStatus.vue'
import { api } from '../services/api'
import { workspace as s, syncMaintenanceMileage, refreshMotos } from '../services/workspace'
import { useTask } from '../composables/useTask'
import { decimal, formatCurrency, formatDate, formatKm, fuelTotal, serviceTypes, today, fuelTypes, expenseTypes } from '../services/format'
import { fuelPriceFromTotal } from '../services/insights'
import { expensePayload } from '../services/finance'

const props = defineProps({ kind: { type: String, required: true } })
const isExpense = props.kind === 'despesas'
const route = useRoute(), stations = ref([]), stationName = ref(''), calculationHint = ref(''), calculationMode = ref('price')
const rows = ref([]), form = ref(null), editId = ref(null), filter = ref(''), month = ref(today().slice(0, 7))
const { busy, error, notice, run } = useTask()
const path = '/motocicletas/' + s.motoId + '/' + props.kind
const visible = computed(() => rows.value.filter(row => !filter.value || (isExpense ? row.tipo_despesa : row.status) === filter.value))
const total = computed(() => (isExpense ? visible.value : rows.value.filter(row => row.status === 'concluido')).reduce((sum, row) => sum + Math.round(Number(row.valor) * 100), 0) / 100)

async function load() {
  rows.value = []
  const params = isExpense && month.value ? { mes: month.value } : {}
  rows.value = (await api.get(path, { params })).data
  if (isExpense) stations.value = (await api.get('/postos')).data
}
function open(row = null) {
  editId.value = row?.id || null
  form.value = row ? { ...row } : { data: today(), valor: '', descricao: '', ...(isExpense ? { tipo_despesa: 'combustivel', litros: '', preco_litro: '', posto: '', posto_id: '', combustivel: 'gasolina', km_abastecimento: '', tanque_cheio: false } : { tipo_servico: 'troca_oleo', status: 'concluido', km_servico: '', natureza: 'preventiva' }) }
  calculationHint.value = ''; calculationMode.value = 'price'
  error.value = ''; notice.value = ''
}
function calculate(source = 'liters') {
  if (form.value?.tipo_despesa !== 'combustivel') return
  if (source === 'price') calculationMode.value = 'price'
  if (source === 'total') calculationMode.value = 'total'
  if (calculationMode.value === 'total') {
    const result = fuelPriceFromTotal(form.value.valor, form.value.litros)
    if (result) form.value.preco_litro = result.price
    calculationHint.value = 'Preço calculado pelo total e litros. Ao sair do campo, o total será conciliado com o preço arredondado a centavos.'
  } else form.value.valor = fuelTotal(form.value.litros, form.value.preco_litro)
}
function reconcile() {
  if (form.value?.tipo_despesa !== 'combustivel' || calculationMode.value !== 'total') return
  if (!fuelPriceFromTotal(form.value.valor, form.value.litros)) return
  const previous = decimal(form.value.valor), canonical = fuelTotal(form.value.litros, form.value.preco_litro)
  form.value.valor = canonical
  calculationMode.value = 'price'
  calculationHint.value = previous !== canonical ? 'Total ajustado para ' + formatCurrency(canonical) + ' pelo preço por litro arredondado a centavos. Confira com o comprovante.' : 'Total e preço por litro conferidos.'
}
function selectStation() {
  const station = stations.value.find(row => row.id === Number(form.value.posto_id))
  if (station) form.value.posto = station.nome
  const price = station?.precos.find(row => row.combustivel === form.value.combustivel)
  form.value.preco_litro = price?.preco_litro || ''
  calculationMode.value = 'price'; calculationHint.value = price ? 'Último preço deste posto e combustível preenchido. Edite se o valor mudou.' : 'Sem preço memorizado para esta combinação. Informe o valor da bomba.'
  calculate('price')
}
async function addStation() {
  await run(async () => {
    const response = await api.post('/postos', { nome: stationName.value })
    stations.value.push(response.data); form.value.posto_id = response.data.id
    stationName.value = ''; selectStation()
  }, 'Posto favorito cadastrado.')
}
async function removeStation(station) {
  if (!window.confirm('Remover o favorito ' + station.nome + '? Os abastecimentos serão preservados.')) return
  await run(async () => {
    await api.delete('/postos/' + station.id)
    stations.value = stations.value.filter(row => row.id !== station.id)
    if (Number(form.value?.posto_id) === station.id) form.value.posto_id = ''
  }, 'Favorito removido.')
}
function changeType() {
  Object.assign(form.value, { litros: '', preco_litro: '', posto: '', valor: '', posto_id: '', combustivel: 'gasolina', km_abastecimento: '', tanque_cheio: false })
  calculationHint.value = ''; calculationMode.value = 'price'
}
function changeMonth(value = month.value) {
  if (busy.value) return
  month.value = value
  form.value = null
  run(load)
}
async function save() {
  reconcile()
  await run(async () => {
    const payload = isExpense ? expensePayload(form.value) : { ...form.value, valor: decimal(form.value.valor), km_servico: form.value.km_servico === '' ? null : form.value.km_servico }
    const response = editId.value ? await api.put(path + '/' + editId.value, payload) : await api.post(path, payload)
    if (!isExpense) syncMaintenanceMileage(response.data)
    else await refreshMotos()
    form.value = null
    await load()
  }, 'Registro salvo.')
}
async function remove(row) {
  if (!window.confirm('Excluir este registro?')) return
  await run(async () => { await api.delete(path + '/' + row.id); await load() }, 'Registro excluído.')
}
function quickOpen() { if (s.motoId && route.query.novo) { open(); if (isExpense && route.query.novo === 'despesa') form.value.tipo_despesa = 'outros' } }
watch(() => route.query, quickOpen)
onMounted(() => { if (s.motoId) { run(load); quickOpen() } })
</script>

<template>
  <div class="page-stack">
    <SectionTitle>{{ isExpense ? 'Gastos e Abastecimentos' : 'Histórico de Manutenções' }}</SectionTitle>
    <TaskStatus :busy="busy" :error="error" :notice="notice" />
    <p v-if="!s.motoId">Cadastre uma moto na Garagem para começar.</p>
    <template v-else>
      <div class="metrics-grid metrics-three">
        <MetricCard icon="📋" label="Registros" :value="isExpense ? visible.length : rows.length" accent />
        <MetricCard icon="💰" :label="isExpense ? 'Despesas no filtro' : 'Total concluído'" :value="formatCurrency(total)" />
        <MetricCard icon="🏍" label="Escopo" value="Sua moto" :sub="isExpense ? (month || 'Todo o histórico') : 'Histórico completo'" />
      </div>
      <div class="toolbar">
        <button class="button button-lime" :disabled="busy" @click="open()">+ {{ isExpense ? 'Novo gasto' : 'Registrar manutenção' }}</button>
        <label v-if="isExpense" class="field">Mês<input v-model="month" type="month" :disabled="busy" @change="changeMonth()" /></label>
        <button v-if="isExpense" class="button button-ghost" :disabled="busy || !month" @click="changeMonth('')">Todo o histórico</button>
        <label class="field">Filtro<select v-model="filter" :disabled="busy"><option value="">Todos</option><template v-if="isExpense"><option v-for="(name, key) in expenseTypes" :key="key" :value="key">{{ key === 'reparo' ? 'Reparos' : name }}</option></template><template v-else><option value="concluido">Feito</option><option value="pendente">Pendente</option></template></select></label>
        <button class="button button-ghost" :disabled="busy" @click="run(load)">Atualizar</button>
      </div>
      <form v-if="form" class="demo-form" @submit.prevent="save">
        <h3>{{ editId ? 'Editar' : 'Novo' }} registro</h3>
        <fieldset :disabled="busy">
          <div class="form-grid">
            <label class="field">Data<input v-model="form.data" type="date" :max="!isExpense && form.status === 'concluido' ? today() : undefined" required /></label>
            <label v-if="isExpense" class="field">Tipo<select v-model="form.tipo_despesa" @change="changeType"><option v-for="(name, key) in expenseTypes" :key="key" :value="key">{{ name }}</option></select></label>
            <label v-else class="field">Serviço<select v-model="form.tipo_servico"><option v-for="(label, key) in serviceTypes" :key="key" :value="key">{{ label }}</option></select></label>
            <template v-if="isExpense && form.tipo_despesa === 'combustivel'">
              <label class="field">Posto favorito<select v-model="form.posto_id" @change="selectStation"><option value="">Sem favorito / informar nome</option><option v-for="station in stations" :key="station.id" :value="station.id">{{ station.nome }}</option></select></label>
              <label class="field">Combustível<select v-model="form.combustivel" :required="!!form.posto_id" @change="selectStation"><option value="">Não informado (histórico)</option><option v-for="(name, key) in fuelTypes" :key="key" :value="key">{{ name }}</option></select></label>
              <label class="field">Litros<input v-model="form.litros" inputmode="decimal" pattern="[0-9]+([,.][0-9]{1,2})?" maxlength="9" required @input="calculate('liters')" @blur="reconcile" /></label>
              <label class="field">Preço por litro (R$)<input v-model="form.preco_litro" inputmode="decimal" pattern="[0-9]+([,.][0-9]{1,2})?" maxlength="11" required @input="calculate('price')" /></label>
              <label v-if="!form.posto_id" class="field">Posto<input v-model="form.posto" maxlength="255" /></label>
              <label class="field">Odômetro no abastecimento (KM)<input v-model="form.km_abastecimento" inputmode="numeric" type="number" min="0" max="4294967295" :required="!!form.tanque_cheio" /></label>
              <label class="field">Tanque cheio<select v-model="form.tanque_cheio"><option :value="false">Não (parcial)</option><option :value="true">Sim</option><option v-if="form.tanque_cheio == null" :value="null">Não informado (histórico)</option></select></label>
            </template>
            <label class="field">Valor (R$)<input v-model="form.valor" inputmode="decimal" :pattern="isExpense ? '[0-9]+([,.][0-9]{1,2})?' : undefined" required @input="calculate('total')" @blur="reconcile" /></label>
            <label class="field">Descrição<input v-model="form.descricao" maxlength="5000" :required="isExpense && form.tipo_despesa === 'reparo'" /></label>
            <template v-if="!isExpense">
              <label class="field">Natureza da manutenção<select v-model="form.natureza"><option :value="null">Não classificada</option><option value="preventiva">Preventiva</option><option value="corretiva">Corretiva</option></select></label>
              <label class="field">Status<select v-model="form.status"><option value="concluido">Feito</option><option value="pendente">Pendente</option></select></label>
              <label class="field">Odômetro no serviço (KM)<input v-model="form.km_servico" type="number" min="0" max="4294967295" :required="form.status === 'concluido'" /></label>
            </template>
          </div>
          <template v-if="isExpense && form.tipo_despesa === 'combustivel'">
            <p class="field-hint">Litros e preço calculam o total; total e litros calculam o preço. Precisão de centavos. {{ calculationHint }} Informe o KM e marque os tanques cheios para calcular consumo; parciais entram no ciclo. O odômetro só avança.</p>
            <details class="station-manager"><summary>Gerenciar postos favoritos</summary><div class="toolbar"><label class="field">Nome do novo posto<input v-model="stationName" maxlength="255" /></label><button type="button" class="button button-ghost" :disabled="!stationName.trim()" @click="addStation">Cadastrar favorito</button></div><ul><li v-for="station in stations" :key="station.id">{{ station.nome }} <button type="button" class="button button-small button-danger" @click="removeStation(station)">Remover favorito</button></li></ul></details>
          </template>
          <p v-if="!isExpense" class="field-hint">Uma manutenção feita atualiza o odômetro quando o KM informado é maior. Registros históricos, pendentes, edições com KM menor e exclusões não reduzem o odômetro. Conclua o alerta correspondente em Alertas e cadastre o próximo limite.</p>
          <div class="form-actions"><button class="button button-black">Salvar</button><button class="button button-ghost" type="button" @click="form = null">Cancelar</button></div>
        </fieldset>
      </form>
      <p v-if="isExpense" class="field-hint">Registre cada gasto uma única vez: custos de manutenção já entram no total combinado dos relatórios. Para um serviço registrado em Manutenções, não crie outro lançamento em Reparos.</p>
      <div class="table-scroll">
        <table class="data-table" :class="{ 'expense-table': isExpense }">
          <caption class="sr-only">Registros da moto selecionada</caption>
          <thead><tr><th>Data</th><th>Tipo</th><th>Descrição</th><th>Valor</th><th>{{ isExpense ? 'Abastecimento' : 'Quilometragem / status' }}</th><th>Ações</th></tr></thead>
          <tbody>
            <tr v-for="row in visible" :key="row.id">
              <td>{{ formatDate(row.data) }}</td><td>{{ isExpense ? expenseTypes[row.tipo_despesa] : serviceTypes[row.tipo_servico] }}</td>
              <td class="wrap-text">{{ row.descricao || row.posto || '—' }}</td><td>{{ formatCurrency(row.valor) }}</td>
              <td>{{ isExpense ? (row.litros ? row.litros + ' L · ' + formatCurrency(row.preco_litro) + '/L' : '—') : (row.km_servico === null ? '—' : formatKm(row.km_servico) + ' km') + ' · ' + (row.status === 'concluido' ? 'Feito' : 'Pendente') }}</td>
              <td><div class="table-actions"><button class="button button-small button-ghost" :disabled="busy" @click="open(row)">Editar</button><button class="button button-small button-danger" :disabled="busy" @click="remove(row)">Excluir</button></div></td>
            </tr>
            <tr v-if="!busy && !visible.length"><td colspan="6" class="empty-table">Nenhum registro neste período ou filtro.</td></tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
