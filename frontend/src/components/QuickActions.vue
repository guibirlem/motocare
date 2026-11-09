<script setup>
import { ref } from 'vue'
import { workspace as s } from '../services/workspace'
const open = ref(false)
const destination = (path, novo) => ({ path, query: { novo, acao: Date.now() } })
</script>
<template>
  <nav v-if="s.motoId" class="quick-actions no-print" aria-label="Registro rápido">
    <div v-if="open" id="quick-action-links" class="quick-action-links">
      <RouterLink v-if="s.features.financeiro" :to="destination('/gastos', 'abastecimento')" class="button button-lime" @click="open = false">Abastecimento</RouterLink>
      <RouterLink :to="destination('/manutencoes', 'manutencao')" class="button button-lime" @click="open = false">Manutenção</RouterLink>
      <RouterLink v-if="s.features.financeiro" :to="destination('/gastos', 'despesa')" class="button button-lime" @click="open = false">Despesa</RouterLink>
    </div>
    <button class="button button-black quick-action-toggle" :aria-expanded="open" aria-controls="quick-action-links" @click="open = !open">{{ open ? 'Fechar atalhos' : '+ Registrar' }}</button>
  </nav>
</template>
