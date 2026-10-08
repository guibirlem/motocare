<script setup>
import { ref } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'
import MotorcycleIcon from '../components/MotorcycleIcon.vue'
import { formatKm, motorcycles } from '../data/demo'

const selectedMotorcycle = ref(null)
const formOpen = ref(false)
const notice = ref('')
function openEditor(motorcycle = null) {
  selectedMotorcycle.value = motorcycle
  formOpen.value = true
  notice.value = ''
}
</script>

<template>
  <div class="page-stack page-stack-compact">
    <SectionTitle>Minha Garagem</SectionTitle>
    <div class="garage-grid">
      <article v-for="moto in motorcycles" :key="moto.id" class="motorcycle-card">
        <div class="motorcycle-art" :class="{ 'motorcycle-art-lime': moto.accent }"><MotorcycleIcon /></div>
        <div class="motorcycle-card-content">
          <h3>{{ moto.name }}</h3>
          <dl><div><dt>Placa</dt><dd>{{ moto.plate }}</dd></div><div><dt>Ano</dt><dd>{{ moto.year }}</dd></div><div><dt>KM</dt><dd>{{ formatKm(moto.currentKm) }} km</dd></div></dl>
          <div class="motorcycle-card-actions"><button class="button button-small button-lime" type="button" :aria-label="'Detalhes de ' + moto.name" @click="selectedMotorcycle = moto; formOpen = false">Detalhes</button><button class="button button-small button-ghost" type="button" :aria-label="'Editar ' + moto.name" @click="openEditor(moto)">Editar</button></div>
        </div>
      </article>
      <button class="add-motorcycle" type="button" @click="openEditor()"><span aria-hidden="true">+</span><strong>Adicionar Moto</strong></button>
    </div>
    <section v-if="selectedMotorcycle && !formOpen" class="panel motorcycle-details-panel">
      <h3 class="panel-heading">{{ selectedMotorcycle.name }}</h3><p class="muted">{{ selectedMotorcycle.plate }} · {{ selectedMotorcycle.year }} · {{ formatKm(selectedMotorcycle.currentKm) }} km</p>
      <div class="form-actions"><RouterLink class="button button-lime" to="/alertas">Ver Alertas</RouterLink><RouterLink class="button button-ghost" to="/manutencoes">Ver Manutenções</RouterLink><button class="button button-ghost" type="button" @click="selectedMotorcycle = null">Fechar</button></div>
    </section>
    <form v-if="formOpen" class="demo-form" :key="selectedMotorcycle?.id ?? 'new'" @submit.prevent="notice = 'Demonstração: nenhum dado da moto foi alterado.'">
      <h3 class="form-heading">{{ selectedMotorcycle ? 'Editar Moto' : 'Adicionar Moto' }}</h3>
      <div class="form-grid"><label class="field">Modelo<input :value="selectedMotorcycle?.name" placeholder="Ex: Honda CB 500F" required /></label><label class="field">Placa<input :value="selectedMotorcycle?.plate" placeholder="Ex: ABC-1D23" /></label><label class="field">Ano<input :value="selectedMotorcycle?.year" inputmode="numeric" placeholder="Ex: 2021" /></label><label class="field">KM Atual<input :value="selectedMotorcycle?.currentKm" inputmode="numeric" placeholder="Ex: 28.400" /></label></div>
      <div class="form-actions"><button class="button button-black" type="submit">Salvar</button><button class="button button-ghost" type="button" @click="formOpen = false; selectedMotorcycle = null">Cancelar</button></div>
      <p class="field-hint">Formulário demonstrativo. As alterações não são salvas.</p>
    </form>
    <p v-if="notice" class="demo-notice" role="status">{{ notice }}</p>
  </div>
</template>
