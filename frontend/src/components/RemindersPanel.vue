<script setup>
import { computed } from 'vue'
import AppIcon from './LandingIcon.vue'
import { activeReminders, suggestedReminders, reminderSuggestions } from '../services/reminders'
import { alertTypes, formatDate, formatKm } from '../services/format'

const props = defineProps({ alerts: { type: Array, default: () => [] }, currentKm: { type: Number, default: 0 }, loading: Boolean, unavailable: Boolean, suggestionsOnly: Boolean })
defineEmits(['configure'])
const active = computed(() => activeReminders(props.alerts, props.currentKm))
const suggestions = computed(() => suggestedReminders(props.alerts))
const iconFor = type => reminderSuggestions.find(item => item.type === type)?.icon || 'service'
</script>

<template>
  <section class="reminders-panel" :aria-label="suggestionsOnly ? 'Lembretes sugeridos' : 'Lembretes'" :aria-busy="loading">
    <header class="reminders-heading">
      <div class="reminders-heading-copy"><span class="reminders-symbol"><AppIcon name="bell"/></span><div><h2>{{ suggestionsOnly ? 'Lembretes sugeridos' : 'Lembretes' }}</h2><p>Os próximos cuidados, sempre à vista.</p></div></div>
      <RouterLink v-if="!suggestionsOnly" to="/alertas" class="reminders-all">Ver todos <span aria-hidden="true">↗</span></RouterLink>
    </header>
    <p v-if="unavailable" class="reminders-message" role="status">Não foi possível carregar seus lembretes. Use Atualizar para tentar novamente.</p>
    <p v-else-if="loading && !alerts.length" class="reminders-message" role="status">Carregando seus lembretes…</p>
    <template v-else>
      <div v-if="!suggestionsOnly" class="reminders-active">
        <div class="reminders-label"><h3>Em acompanhamento</h3><span>{{ active.length }} {{ active.length === 1 ? 'ativo' : 'ativos' }}</span></div>
        <div v-if="active.length" class="reminders-active-list">
          <article v-for="alert in active" :key="alert.id" class="reminder-card" :class="'reminder-' + alert.state.tone">
            <div class="reminder-title"><span class="reminder-icon" :class="{ 'reminder-fuel': alert.tipo_alerta === 'abastecimento' }"><AppIcon :name="iconFor(alert.tipo_alerta)"/></span><h4>{{ alertTypes[alert.tipo_alerta] || alert.tipo_alerta }}</h4></div>
            <p class="reminder-target"><span v-if="alert.km_limite">{{ formatKm(alert.km_limite) }} km</span><span v-if="alert.km_limite && alert.data_limite" aria-hidden="true"> · </span><time v-if="alert.data_limite" :datetime="alert.data_limite">{{ formatDate(alert.data_limite) }}</time></p>
            <div class="reminder-remaining"><AppIcon name="history"/><span v-if="alert.state.distance">{{ alert.state.distance }}</span><span v-if="alert.state.date" :class="{ 'reminder-date-due': alert.state.tone === 'overdue' }">{{ alert.state.date }}</span></div>
            <div class="reminder-bottom"><span class="reminder-status">{{ alert.state.label }}</span><RouterLink :to="{ path: '/alertas', hash: '#lembrete-' + alert.id }" :aria-label="'Ver lembrete: ' + (alertTypes[alert.tipo_alerta] || alert.tipo_alerta)">Ver lembrete <span aria-hidden="true">→</span></RouterLink></div>
          </article>
        </div>
        <p v-else class="reminders-empty">Nenhum lembrete ativo. Escolha um cuidado abaixo para começar.</p>
      </div>
      <div v-if="suggestions.length" class="reminders-suggestions">
        <div class="reminders-label"><h3>Sugeridos</h3><span>Configure uma vez por vencimento</span></div>
        <div class="reminder-suggestion-list"><article v-for="item in suggestions" :key="item.type" class="reminder-suggestion">
          <span class="reminder-icon" :class="{ 'reminder-fuel': item.type === 'abastecimento' }"><AppIcon :name="item.icon"/></span>
          <div class="reminder-suggestion-copy"><h4>{{ item.label }}</h4><p>{{ item.hint }}</p></div>
          <button class="reminder-configure" :disabled="loading" :aria-label="'Configurar ' + item.label" @click="$emit('configure', item.type)">Configurar</button>
        </article></div>
      </div>
      <p v-else-if="suggestionsOnly" class="reminders-empty">Todos os lembretes sugeridos já estão configurados. Use Novo alerta para outros cuidados.</p>
      <p class="reminders-footnote">Seguro, IPVA e licenciamento usam a data que você informar. O lembrete não registra serviços ou pagamentos nem renova automaticamente.</p>
    </template>
  </section>
</template>

<style scoped>
.reminders-panel{padding:clamp(18px,3vw,28px);background:var(--surface);border:1px solid var(--gray-200);border-radius:18px;min-width:0;color:var(--text)}
.reminders-heading,.reminders-heading-copy{display:flex;align-items:center;gap:12px;min-width:0}
.reminders-heading{justify-content:space-between;gap:20px;margin-bottom:24px}
.reminders-heading h2{font-size:22px;margin:0;line-height:1.3;letter-spacing:-.4px}
.reminders-heading p{margin:5px 0 0;color:var(--gray-600);font-size:13px;line-height:1.5}
.reminders-symbol{display:grid;place-items:center;width:42px;height:42px;border-radius:12px;background:var(--lime-pale);flex-shrink:0;color:var(--on-lime-pale)}
.reminders-symbol svg{width:25px;height:25px}
.reminders-all{display:flex;gap:8px;align-items:center;white-space:nowrap;color:var(--text);font-size:13px;font-weight:700;text-underline-offset:4px;min-height:44px}
.reminders-label{display:flex;align-items:baseline;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 12px}
.reminders-label h3{margin:0;font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--gray-600)}
.reminders-label>span{font-size:12px;color:var(--gray-600)}
.reminders-active{margin-bottom:26px}
.reminders-active-list,.reminder-suggestion-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,300px),1fr));gap:12px}
.reminder-card{border:1px solid var(--gray-200);border-left:3px solid var(--lime-dark);border-radius:14px;padding:17px;min-width:0}
.reminder-attention{border-left-color:#d88100}.reminder-overdue{border-left-color:var(--red)}
.reminder-title{display:flex;gap:10px;align-items:center;min-width:0}
.reminder-title h4,.reminder-suggestion h4{font-size:15px;line-height:1.4;margin:0;overflow-wrap:anywhere}
.reminder-icon{display:grid;place-items:center;flex-shrink:0;width:34px;height:34px;border-radius:50%;background:var(--lime-pale);color:var(--on-lime-pale)}
.reminder-icon svg{width:19px;height:19px}.reminder-fuel{background:var(--amber-pale);color:var(--on-amber)}
.reminder-target{font-size:13px;line-height:1.6;color:var(--gray-600);margin:12px 0 8px;overflow-wrap:anywhere}
.reminder-remaining{display:flex;align-items:center;flex-wrap:wrap;gap:6px 12px;min-height:22px;font-size:13px;line-height:1.5}
.reminder-remaining svg{width:16px;height:16px;color:var(--on-amber);flex-shrink:0}.reminder-date-due{color:var(--on-red)}
.reminder-bottom{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border-top:1px solid var(--gray-200);padding-top:10px;margin-top:14px}
.reminder-status{font-size:11px;font-weight:700;color:var(--gray-600)}
.reminder-bottom a{font-size:12px;font-weight:700;min-height:32px;display:inline-flex;align-items:center;gap:6px;color:var(--text);text-underline-offset:4px}
.reminder-suggestion{display:grid;grid-template-columns:34px minmax(0,1fr) auto;align-items:center;gap:12px;padding:12px 14px;border:1px solid var(--gray-200);border-radius:14px;min-width:0}
.reminder-suggestion-copy{min-width:0}.reminder-suggestion p{margin:2px 0 0;font-size:12px;line-height:1.5;color:var(--gray-600)}
.reminder-configure{min-height:42px;padding:9px 14px;border:1px solid var(--lime-dark);border-radius:999px;background:transparent;color:var(--text);font-size:11px;letter-spacing:.6px;font-weight:700;text-transform:uppercase;cursor:pointer;transition:background .15s}
.reminder-configure:hover:not(:disabled){background:var(--lime-pale)}.reminder-configure:disabled{opacity:.55;cursor:wait}
.reminders-footnote{font-size:12px;line-height:1.7;color:var(--gray-600);margin:18px 0 0}
.reminders-message,.reminders-empty{font-size:14px;line-height:1.7;color:var(--gray-600);margin:12px 0;padding:16px;background:var(--gray-100);border-radius:10px}
@media(max-width:480px){.reminders-heading{align-items:flex-start;gap:10px}.reminders-heading h2{font-size:20px}.reminders-heading-copy{gap:9px}.reminders-symbol{width:34px;height:34px}.reminders-symbol svg{width:21px;height:21px}.reminders-heading p{font-size:12px}.reminder-suggestion{grid-template-columns:30px minmax(0,1fr);gap:8px 10px;padding:13px}.reminder-suggestion .reminder-icon{width:30px;height:30px}.reminder-configure{grid-column:2;justify-self:start;min-height:44px}.reminders-all{font-size:12px}.reminder-card{padding:14px}}
@media(prefers-reduced-motion:reduce){.reminder-configure{transition:none}}
</style>
