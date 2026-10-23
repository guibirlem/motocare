<script setup>
import { computed, onUnmounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import TaskStatus from '../components/TaskStatus.vue'
import MotorcycleIcon from '../components/MotorcycleIcon.vue'
import { useTask } from '../composables/useTask'
import { readResetLink, requestPasswordReset, resetPassword } from '../services/passwordRecovery'
import { clearSession, workspace } from '../services/workspace'

const route = useRoute(), router = useRouter()
const resetting = computed(() => route.name === 'reset-password')
const link = resetting.value ? readResetLink(window.location.hash) : null
const credentials = ref({ email: link?.email || '', token: link?.token || '', password: '', password_confirmation: '' })
const validLink = ref(Boolean(link)), completed = ref(false), cooldown = ref(0)
const { busy, error, notice, run } = useTask()
// O segredo só permanece na memória desta tela, sem URL, storage, logs ou cache.
if (route.hash || Object.keys(route.query).length) void router.replace({ path: route.path, hash: '', query: {} })
let timer

async function submit() {
  if (completed.value || cooldown.value || (resetting.value && !validLink.value)) return
  const ok = await run(async () => {
    if (resetting.value) {
      const message = await resetPassword({ ...credentials.value })
      if (!workspace.user || workspace.user.email.toLowerCase() === credentials.value.email.toLowerCase()) clearSession()
      credentials.value.token = ''; validLink.value = false; completed.value = true
      return message
    }
    return requestPasswordReset(credentials.value.email)
  })
  if (ok) {
    notice.value = resetting.value
      ? 'Senha atualizada! Entre com sua nova senha. Reative as notificações nos dispositivos que você utiliza.'
      : 'Se houver uma conta com este e-mail, você receberá um link para redefinir sua senha. Confira também a pasta de spam.'
    if (!resetting.value) {
      cooldown.value = 60
      timer = window.setInterval(() => { if (--cooldown.value <= 0) window.clearInterval(timer) }, 1000)
    }
  }
  credentials.value.password = ''; credentials.value.password_confirmation = ''
}
onUnmounted(() => {
  window.clearInterval(timer)
  credentials.value.token = ''; credentials.value.password = ''; credentials.value.password_confirmation = ''
})
</script>

<template>
  <main class="auth-layout recovery-layout">
    <RouterLink class="recovery-brand" to="/apresentacao"><MotorcycleIcon :size="32"/><span>MotoCare</span></RouterLink>
    <section class="demo-form recovery-card" aria-labelledby="recovery-heading">
      <h1 id="recovery-heading">{{ resetting ? 'Escolha uma nova senha' : 'Esqueceu sua senha?' }}</h1>
      <p v-if="!completed">{{ resetting ? 'Use uma senha de pelo menos 8 caracteres para proteger sua conta.' : 'Informe o e-mail da sua conta para receber o link de recuperação.' }}</p>
      <TaskStatus :busy="busy" :error="error" :notice="notice"/>
      <template v-if="resetting && !validLink && !completed">
        <p role="alert">Abra o link recebido por e-mail. Se ele expirou ou a página foi recarregada, solicite um novo link.</p>
        <RouterLink class="button button-lime" to="/esqueci-senha">Solicitar novo link</RouterLink>
      </template>
      <form v-else-if="!completed" class="fields-stack" @submit.prevent="submit">
        <label class="field">E-mail<input v-model="credentials.email" type="email" required maxlength="255" autocomplete="email" :readonly="resetting" :disabled="busy"/></label>
        <template v-if="resetting">
          <label class="field">Nova senha<input v-model="credentials.password" type="password" required minlength="8" maxlength="72" autocomplete="new-password" :disabled="busy"/></label>
          <label class="field">Confirme a nova senha<input v-model="credentials.password_confirmation" type="password" required minlength="8" maxlength="72" autocomplete="new-password" :disabled="busy"/></label>
        </template>
        <button class="button button-lime" :disabled="busy || cooldown > 0">{{ resetting ? 'Salvar nova senha' : cooldown ? `Aguarde ${cooldown}s para reenviar` : 'Enviar link de recuperação' }}</button>
      </form>
      <RouterLink v-if="resetting && validLink && error" class="recovery-help" to="/esqueci-senha">Solicitar outro link</RouterLink>
      <RouterLink class="recovery-back" to="/dashboard">{{ completed ? 'Voltar ao sistema' : 'Voltar para entrar' }}</RouterLink>
    </section>
    <p class="recovery-caption">Cuidado em cada quilômetro.</p>
  </main>
</template>

<style scoped>
.recovery-layout{max-width:520px;padding-top:32px;padding-bottom:40px}
.recovery-brand{display:flex;align-items:center;justify-content:center;gap:12px;font-size:26px;font-weight:800;color:var(--text);text-decoration:none;margin-bottom:28px}
.recovery-brand svg{color:var(--lime)}
.recovery-card{padding:28px;display:flex;flex-direction:column;gap:18px}
.recovery-card h1{font-size:clamp(23px,5vw,30px);line-height:1.2;margin:0}
.recovery-card p{line-height:1.65;margin:0}
.recovery-card form{margin:0}
.recovery-card .button{white-space:normal;text-align:center;min-height:44px}
.recovery-back,.recovery-help{text-align:center;color:var(--text);text-underline-offset:4px;padding:8px;overflow-wrap:anywhere}
.recovery-caption{text-align:center;color:var(--gray-600);font-size:13px;margin-top:24px}
@media(max-width:480px){.recovery-layout{padding:24px 16px 32px}.recovery-card{padding:22px 18px}.recovery-brand{margin-bottom:20px}}
</style>
