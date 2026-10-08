<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import MotorcycleIcon from './components/MotorcycleIcon.vue'
import { maintenanceRecords, motorcycles, navigationItems } from './data/demo'

const route = useRoute()
const searchQuery = ref('')
const searchFocused = ref(false)
const menuOpen = ref(false)
const searchItems = [
  ...navigationItems.map((item) => ({ ...item, description: 'Página do MotoCare' })),
  ...motorcycles.map((moto) => ({ label: moto.name, path: '/garagem', description: 'Moto — ' + moto.plate + ' · ' + moto.year })),
  ...maintenanceRecords.map((record) => ({ label: record.type, path: '/manutencoes', description: 'Manutenção — ' + record.motorcycle })),
]
const searchResults = computed(() => {
  const query = searchQuery.value.trim().toLocaleLowerCase('pt-BR')
  return query ? searchItems.filter((item) => (item.label + ' ' + item.description).toLocaleLowerCase('pt-BR').includes(query)).slice(0, 6) : []
})
watch(() => route.path, () => {
  menuOpen.value = false
  searchQuery.value = ''
  searchFocused.value = false
})
</script>

<template>
  <div class="app-shell">
    <header class="app-header">
      <RouterLink class="app-brand" to="/" aria-label="MotoCare — Dashboard">
        <span class="brand-mark"><MotorcycleIcon :size="20" /></span>
        <span>MotoCare</span>
      </RouterLink>
      <div class="header-search">
        <div class="search-control" @focusin="searchFocused = true" @focusout="searchFocused = $event.currentTarget.contains($event.relatedTarget)">
          <svg aria-hidden="true" viewBox="0 0 14 14" fill="none"><circle cx="5.5" cy="5.5" r="4.5" stroke="currentColor" stroke-width="1.5" /><path d="m9 9 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" /></svg>
          <input v-model="searchQuery" type="search" placeholder="Buscar manutenções, motos..." aria-label="Buscar páginas, manutenções e motos" @keydown.esc="searchFocused = false" />
          <div v-if="searchFocused && searchQuery.trim()" class="search-results">
            <RouterLink v-for="item in searchResults" :key="item.path + '-' + item.label" :to="item.path"><strong>{{ item.label }}</strong><span>{{ item.description }}</span></RouterLink>
            <p v-if="!searchResults.length">Nenhum resultado para “{{ searchQuery }}”</p>
          </div>
        </div>
      </div>
      <RouterLink class="header-user" to="/configuracoes" aria-label="Conta de Lucas Silva"><span class="user-avatar">L</span><span>Lucas Silva</span></RouterLink>
      <button class="mobile-menu-button" type="button" :aria-expanded="menuOpen" aria-controls="main-navigation" @click="menuOpen = !menuOpen">{{ menuOpen ? 'Fechar' : 'Menu' }}</button>
    </header>
    <div class="app-body">
      <aside class="app-sidebar" :class="{ 'sidebar-open': menuOpen }">
        <nav id="main-navigation" class="sidebar-navigation" aria-label="Navegação principal">
          <p class="sidebar-label">Menu</p>
          <RouterLink v-for="item in navigationItems" :key="item.path" :to="item.path" exact-active-class="sidebar-active"><span class="nav-icon" aria-hidden="true">{{ item.icon }}</span><span>{{ item.label }}</span></RouterLink>
        </nav>
        <div class="sidebar-footer">MotoCare v1.0</div>
      </aside>
      <main id="main-content" class="app-main">
        <div class="page-breadcrumb"><span aria-hidden="true"></span>{{ route.meta.title }}</div>
        <RouterView />
        <p class="demo-caption">Protótipo demonstrativo · Dados de exemplo</p>
      </main>
    </div>
  </div>
</template>
