<script setup>
import { computed, ref } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'
import MetricCard from '../components/MetricCard.vue'
import TaskStatus from '../components/TaskStatus.vue'
import { api } from '../services/api'
import { workspace as s } from '../services/workspace'
import { formatCurrency, formatDate, reportParams, serviceTypes, today } from '../services/format'
import { useTask } from '../composables/useTask'
import { buildCsv } from '../services/csv'
const {busy,error,notice,run}=useTask(), start=ref(today().slice(0,4)+'-01-01'), end=ref(today()), motoId=ref(s.motoId), data=ref(null), range=ref({})
const lines=computed(()=>data.value?[...data.value.despesas.map(r=>({...r,category:r.tipo_despesa==='combustivel'?'Combustível':'Reparo',status:'Pago'})),...data.value.manutencoes.map(r=>({...r,category:serviceTypes[r.tipo_servico],status:r.status==='concluido'?'Feito':'Pendente'}))].sort((a,b)=>b.data.localeCompare(a.data)):[])
const motoName=id=>{const m=data.value?.motocicletas.find(m=>m.id===id);return m?m.marca+' '+m.modelo+' · '+m.placa:''}
async function load(){await run(async()=>{data.value=(await api.get('/relatorios',{params:reportParams(start.value,end.value,motoId.value)})).data;range.value={start:start.value,end:end.value,moto:motoId.value?motoName(Number(motoId.value)):'Todas as motos'}})}
function csv(){
  const rows=[['Data','Moto','Tipo','Descrição','Valor (R$)','Status'],...lines.value.map(r=>[formatDate(r.data),motoName(r.motocicleta_id),r.category,r.descricao||r.posto||'',r.valor,r.status])]
  const blob=new Blob([buildCsv(rows)],{type:'text/csv;charset=utf-8'})
  const url=URL.createObjectURL(blob), a=document.createElement('a')
  a.href=url; a.download='motocare-'+range.value.start+'-'+range.value.end+'.csv'; a.click()
  setTimeout(()=>URL.revokeObjectURL(url),1000)
}
function print(){window.print()}
</script>
<template><div class="page-stack"><SectionTitle>Relatórios</SectionTitle><TaskStatus :busy="busy" :error="error" :notice="notice"/><form class="panel no-print" @submit.prevent="load"><div class="form-grid"><label class="field">De<input v-model="start" type="date" required/></label><label class="field">Até<input v-model="end" type="date" :min="start" required/></label><label class="field">Moto<select v-model="motoId"><option value="">Todas</option><option v-for="m in s.motos" :key="m.id" :value="m.id">{{m.marca}} {{m.modelo}} · {{m.placa}}</option></select></label></div><div class="form-actions"><button class="button button-lime" :disabled="busy">Gerar relatório</button><button type="button" class="button button-ghost" :disabled="!data||busy" @click="csv">Baixar CSV</button><button type="button" class="button button-black" :disabled="!data||busy" @click="print">Imprimir / Salvar PDF</button></div><p class="field-hint">Para PDF, selecione “Salvar como PDF” na janela de impressão do navegador.</p></form><article v-if="data" class="report-content"><h2>MotoCare · Relatório financeiro</h2><p>{{formatDate(range.start)}} a {{formatDate(range.end)}} · {{range.moto}}</p><div class="metrics-grid metrics-three"><MetricCard icon="💰" label="Despesas" :value="formatCurrency(data.resumo.despesas)"/><MetricCard icon="🔩" label="Manutenções concluídas" :value="formatCurrency(data.resumo.manutencoes)"/><MetricCard icon="∑" label="Total combinado" :value="formatCurrency(data.resumo.total)" accent/></div><p class="field-hint">Pendentes não entram nos totais. Um mesmo gasto deve ser lançado apenas uma vez, em despesas ou manutenções.</p><h3>Resumo por mês</h3><div class="table-scroll"><table class="data-table"><thead><tr><th>Mês</th><th>Combustível</th><th>Reparos</th><th>Manutenções</th><th>Total</th></tr></thead><tbody><tr v-for="r in data.mensal" :key="r.mes"><td>{{r.mes}}</td><td>{{formatCurrency(r.combustivel)}}</td><td>{{formatCurrency(r.reparos)}}</td><td>{{formatCurrency(r.manutencoes)}}</td><td>{{formatCurrency(r.total)}}</td></tr></tbody></table></div><h3>Lançamentos</h3><div class="table-scroll"><table class="data-table"><thead><tr><th>Data</th><th>Moto</th><th>Tipo</th><th>Descrição</th><th>Valor</th><th>Status</th></tr></thead><tbody><tr v-for="(r,index) in lines" :key="index"><td>{{formatDate(r.data)}}</td><td>{{motoName(r.motocicleta_id)}}</td><td>{{r.category}}</td><td class="wrap-text">{{r.descricao||r.posto||'—'}}</td><td>{{formatCurrency(r.valor)}}</td><td>{{r.status}}</td></tr><tr v-if="!lines.length"><td colspan="6" class="empty-table">Nenhum lançamento no período.</td></tr></tbody></table></div></article><p v-else>Escolha o período e gere seu relatório.</p></div></template>
