<script setup>
import { computed, defineAsyncComponent, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import MotorcycleIcon from './components/MotorcycleIcon.vue'
import AccountGate from './components/AccountGate.vue'
import TaskStatus from './components/TaskStatus.vue'
import ThemeToggle from './components/ThemeToggle.vue'
import QuickActions from './components/QuickActions.vue'
import AppFooter from './components/AppFooter.vue'
const LandingView = defineAsyncComponent(() => import('./views/LandingView.vue'))
import { isPresentation } from './services/presentation'
import { workspace as s, startSession, signOut, refreshMotos } from './services/workspace'
import { useTask } from './composables/useTask'
import { offlineMessage } from './services/api'
import { navigationItems, routeEnabled } from './services/navigation'
const route=useRoute(), router=useRouter(), menuOpen=ref(false), search=ref('')
const presentation=computed(()=>isPresentation(route,s.user))
watch(()=>[presentation.value,route.fullPath],()=>{document.title=presentation.value?'MotoCare — Controle e manutenção da sua moto':route.meta.title+' · MotoCare'},{immediate:true})
const {busy,error,notice,run}=useTask()
const items=computed(()=>navigationItems(s.features))
const results=computed(()=>search.value.trim()?items.value.filter(i=>i.label.toLocaleLowerCase('pt-BR').includes(search.value.toLocaleLowerCase('pt-BR'))):[])
watch(()=>[s.user,s.features.financeiro,route.path],()=>{if(s.user&&!routeEnabled(route,s.features))router.replace('/')})
watch(()=>route.path,()=>{menuOpen.value=false;search.value=''})
onMounted(()=>{if(!s.offline&&!route.meta.passwordRecovery)run(startSession)})
watch(()=>route.meta.passwordRecovery,(recovery,wasRecovery)=>{if(wasRecovery&&!recovery&&!s.ready&&!s.offline)run(startSession)})
watch(()=>s.offline,(offline,wasOffline)=>{if(wasOffline&&!offline)run(startSession)})
function retryConnection(){s.offline=!navigator.onLine;if(!s.offline)run(startSession)}
const onNotification=event=>{if(s.user)notice.value=event.detail}
window.addEventListener('motocare-notification',onNotification)
onUnmounted(()=>window.removeEventListener('motocare-notification',onNotification))
</script>
<template>
  <div v-if="s.offline || route.meta.passwordRecovery || (!s.user && !presentation)" class="appearance-toolbar"><ThemeToggle/></div>
  <section v-if="s.offline" class="auth-layout"><h1>MotoCare</h1><h2>Modo offline</h2><p role="status">{{offlineMessage}}</p><p>Quando a conexão voltar, o aplicativo tentará carregar novamente.</p><button class="button button-lime" :disabled="busy" @click="retryConnection">Verificar conexão</button></section>
  <LandingView v-else-if="presentation" />
  <RouterView v-else-if="route.meta.passwordRecovery" :key="route.path"/>
  <div v-else-if="!s.ready && !s.user" class="auth-layout"><TaskStatus :busy="busy" :error="error"/><button v-if="!busy" class="button button-lime" @click="run(startSession)">Tentar novamente</button></div>
  <div v-else-if="!s.user"><TaskStatus :error="error" :notice="s.sessionNotice"/><AccountGate/></div>
  <div v-else class="app-shell">
    <QuickActions />
    <header class="app-header"><RouterLink class="app-brand" to="/"><span class="brand-mark"><MotorcycleIcon :size="20"/></span><span class="brand-copy"><strong>MotoCare</strong><small>Manutenção e finanças</small></span></RouterLink><div class="header-search"><div class="search-control"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg><input v-model="search" type="search" placeholder="Buscar página…" aria-label="Buscar página"/><div v-if="search" class="search-results"><RouterLink v-for="item in results" :key="item.path" :to="item.path">{{item.label}}</RouterLink><p v-if="!results.length">Nenhuma página encontrada.</p></div></div></div><RouterLink class="header-user" to="/configuracoes" :title="s.user.nome" aria-label="Abrir configurações da minha conta"><span class="user-avatar">{{s.user.nome.slice(0,1).toUpperCase()}}</span><span class="header-user-copy"><small>Minha conta</small><strong>{{s.user.nome}}</strong></span></RouterLink><ThemeToggle/><button class="mobile-menu-button" :aria-expanded="menuOpen" aria-controls="main-navigation" @click="menuOpen=!menuOpen">Menu</button></header>
    <div class="app-body"><aside class="app-sidebar" :class="{'sidebar-open':menuOpen}"><nav id="main-navigation" class="sidebar-navigation" aria-label="Principal"><p class="sidebar-label">Menu</p><RouterLink v-for="item in items" :key="item.path" :to="item.path" exact-active-class="sidebar-active"><span class="nav-icon" aria-hidden="true">{{item.icon}}</span>{{item.label}}</RouterLink></nav><button class="button sidebar-logout" :disabled="busy" @click="run(signOut)">Sair da conta</button><div class="sidebar-footer"><strong>MotoCare</strong><span>Cuidado em cada quilômetro.</span></div></aside><button v-if="menuOpen" class="navigation-scrim" aria-label="Fechar menu" @click="menuOpen=false"></button><main class="app-main"><div class="main-content"><div class="page-breadcrumb">{{route.meta.title}}</div><TaskStatus :error="error" :notice="notice"/><section class="toolbar workspace-selector" aria-label="Moto em acompanhamento"><label class="field">Moto selecionada<select v-model="s.motoId"><option v-if="!s.motos.length" value="">Nenhuma moto cadastrada</option><option v-for="m in s.motos" :key="m.id" :value="m.id">{{m.marca}} {{m.modelo}} · {{m.placa}}</option></select></label><RouterLink class="button button-ghost" to="/garagem">Gerenciar garagem</RouterLink><button class="button button-ghost" :disabled="busy" @click="run(refreshMotos)">Atualizar motos</button></section><RouterView :key="route.path+'-'+s.user.id+(route.meta.motorcycleScoped===false?'':'-'+s.motoId)"/></div><AppFooter/></main></div>
  </div>
</template>
