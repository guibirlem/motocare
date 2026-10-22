<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import SectionTitle from '../components/SectionTitle.vue'
import TaskStatus from '../components/TaskStatus.vue'
import { api, prepareSession, retryAfterSeconds } from '../services/api'
import { workspace as s } from '../services/workspace'
import { enablePush, disablePush, testPush as sendPushTest } from '../services/push'
import { useTask } from '../composables/useTask'
const pushState = ref(null)
const retrySeconds = ref(0)
let retryTimer
const {busy,error,notice,run}=useTask(), profile=ref({nome:s.user.nome,email:s.user.email,telefone:s.user.telefone||'',current_password:''}), passwords=ref({current_password:'',password:'',password_confirmation:''})
async function saveProfile(){await run(async()=>{s.user=(await api.put('/user',profile.value)).data;profile.value.current_password=''},'Dados atualizados.')}
async function savePassword(){await run(async()=>{await api.put('/password',passwords.value);passwords.value={current_password:'',password:'',password_confirmation:''};await prepareSession()},'Senha alterada. Outras sessões precisarão entrar novamente.')}
async function loadPushState() { pushState.value = (await api.get('/push-config')).data }
async function activate() { await run(async () => { await enablePush(s.user.id, pushState.value); await loadPushState() }, 'Notificações ativadas. Envie um teste para confirmar o recebimento.') }
async function deactivate() { await run(async () => { await disablePush(); await loadPushState() }, 'Notificações desativadas.') }
async function sendTest() {
  if (retrySeconds.value) return
  await run(async () => {
    try { await sendPushTest(s.user.id) }
    catch (e) {
      if (e.response?.status === 429) {
        const retryAt = Date.now() + retryAfterSeconds(e) * 1000
        clearInterval(retryTimer)
        const update = () => {
          retrySeconds.value = Math.max(0, Math.ceil((retryAt - Date.now()) / 1000))
          if (!retrySeconds.value) clearInterval(retryTimer)
        }
        update(); retryTimer = setInterval(update, 1000)
      }
      throw e
    }
  }, 'Teste aceito pelo serviço. Confira a notificação neste navegador.')
}
onMounted(() => run(loadPushState))
onUnmounted(() => clearInterval(retryTimer))
</script>
<template><div class="page-stack"><SectionTitle>Configurações e Conta</SectionTitle><TaskStatus :busy="busy" :error="error" :notice="notice"/><div class="settings-grid"><form class="panel" @submit.prevent="saveProfile"><h3 class="panel-heading">Dados pessoais</h3><fieldset :disabled="busy"><div class="fields-stack"><label class="field">Nome<input v-model="profile.nome" required maxlength="255" autocomplete="name"/></label><label class="field">E-mail<input v-model="profile.email" required type="email" autocomplete="email"/></label><label class="field">Telefone<input v-model="profile.telefone" type="tel" maxlength="30" autocomplete="tel"/></label><label v-if="profile.email!==s.user.email" class="field">Senha atual para mudar e-mail<input v-model="profile.current_password" type="password" required autocomplete="current-password"/></label></div><div class="form-actions"><button class="button button-lime">Salvar</button></div></fieldset></form><form class="panel" @submit.prevent="savePassword"><h3 class="panel-heading">Alterar senha</h3><fieldset :disabled="busy"><div class="fields-stack"><label class="field">Senha atual<input v-model="passwords.current_password" type="password" required autocomplete="current-password"/></label><label class="field">Nova senha<input v-model="passwords.password" type="password" minlength="8" maxlength="72" required autocomplete="new-password"/></label><label class="field">Confirmar nova senha<input v-model="passwords.password_confirmation" type="password" maxlength="72" required autocomplete="new-password"/></label></div><div class="form-actions"><button class="button button-black">Alterar senha</button></div></fieldset></form></div><section class="panel"><h3>Notificações</h3>
<p>Ative os lembretes neste navegador. O último dispositivo ativado recebe os avisos da sua conta.</p>
<p v-if="pushState && !pushState.configured" class="field-hint">As notificações ainda não estão disponíveis neste ambiente.</p>
<p v-if="pushState?.configured" class="field-hint">{{pushState.subscriptionRegistered ? 'A conta possui um dispositivo ativado. Para testar aqui, ative este navegador primeiro.' : 'Ative este navegador e envie uma notificação de teste.'}}</p>
<div class="form-actions">
<button class="button button-lime" :disabled="busy || !pushState?.configured" @click="activate">Ativar notificações</button>
<button class="button button-ghost" :disabled="busy || retrySeconds > 0 || !pushState?.configured || !pushState?.subscriptionRegistered" @click="sendTest">{{retrySeconds ? `Aguarde ${retrySeconds}s para testar` : 'Enviar teste de notificação'}}</button>
<button class="button button-ghost" :disabled="busy" @click="deactivate">Desativar</button>
<button class="button button-ghost" :disabled="busy" @click="run(loadPushState)">Atualizar status</button>
</div></section></div></template>
