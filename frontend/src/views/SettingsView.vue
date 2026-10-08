<script setup>
import { ref } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'

const profile = ref({ name: '', email: '', phone: '' })
const notice = ref('')
function showDemoNotice() {
  notice.value = 'Esta é uma demonstração. Os dados da conta e a senha não foram alterados.'
}
</script>

<template>
  <div class="page-stack page-stack-compact">
    <SectionTitle>Configurações &amp; Conta</SectionTitle>
    <div class="settings-grid">
      <form class="panel" @submit.prevent="showDemoNotice">
        <h3 class="panel-heading">Dados Pessoais</h3>
        <div class="fields-stack"><label class="field">Nome completo<input v-model="profile.name" placeholder="Lucas Silva" autocomplete="off" /></label><label class="field">E-mail<input v-model="profile.email" type="email" placeholder="lucas@email.com" autocomplete="off" /></label><label class="field">Telefone<input v-model="profile.phone" type="tel" placeholder="(11) 99999-9999" autocomplete="off" /></label></div>
        <div class="form-actions"><button class="button button-lime" type="submit">Salvar</button><button class="button button-ghost" type="button" @click="profile = { name: '', email: '', phone: '' }; notice = ''">Cancelar</button></div>
      </form>
      <form class="panel" @submit.prevent="showDemoNotice">
        <h3 class="panel-heading">Alterar Senha</h3>
        <div class="fields-stack"><label class="field">Senha atual<input type="password" placeholder="••••••••" autocomplete="off" /></label><label class="field">Nova senha<input type="password" placeholder="••••••••" autocomplete="off" /></label><label class="field">Confirmar nova senha<input type="password" placeholder="••••••••" autocomplete="off" /></label></div>
        <div class="form-actions"><button class="button button-black" type="submit">Alterar Senha</button></div>
      </form>
    </div>
    <p v-if="notice" class="demo-notice" role="status">{{ notice }}</p>
    <p class="field-hint">Formulários demonstrativos. As alterações não são salvas.</p>
  </div>
</template>
