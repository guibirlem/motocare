<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { signIn } from '../services/workspace'
import { useTask } from '../composables/useTask'
import TaskStatus from './TaskStatus.vue'
const route = useRoute()
const register = ref(route.query.cadastro === '1'), credentials = ref({ nome:'', email:'', password:'', password_confirmation:'' })
const {busy,error,notice,run} = useTask()
watch(() => route.query.cadastro, value => { register.value = value === '1'; credentials.value.password = ''; credentials.value.password_confirmation = ''; error.value = ''; notice.value = '' })
async function submit() { await run(async () => { await signIn(register.value, credentials.value); credentials.value.password=''; credentials.value.password_confirmation='' }) }
</script>
<template><section class="auth-layout"><h1>MotoCare</h1><p>Suas motos, manutenções e lembretes em um só lugar.</p><TaskStatus :busy="busy" :error="error" :notice="notice"/><form class="demo-form" @submit.prevent="submit"><h2>{{register?'Criar conta':'Entrar'}}</h2><div class="fields-stack"><label v-if="register" class="field">Nome<input v-model="credentials.nome" required maxlength="255" autocomplete="name"/></label><label class="field">E-mail<input v-model="credentials.email" type="email" required autocomplete="username"/></label><label class="field">Senha<input v-model="credentials.password" type="password" required maxlength="72" :minlength="register?8:undefined" :autocomplete="register?'new-password':'current-password'"/></label><label v-if="register" class="field">Confirme a senha<input v-model="credentials.password_confirmation" type="password" required maxlength="72" autocomplete="new-password"/></label></div><div class="form-actions"><button class="button button-lime" :disabled="busy">{{register?'Criar conta':'Entrar'}}</button><button type="button" class="button button-ghost" :disabled="busy" @click="register=!register">{{register?'Já tenho conta':'Criar conta'}}</button></div><RouterLink v-if="!register" class="password-recovery-link" to="/esqueci-senha">Esqueci minha senha</RouterLink></form></section></template>
<style scoped>
.password-recovery-link{display:inline-block;color:var(--text);text-underline-offset:4px;padding:12px 0;margin-top:8px;min-height:44px;box-sizing:border-box}
</style>
