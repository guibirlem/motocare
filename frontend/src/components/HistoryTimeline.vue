<script setup>
import { computed } from 'vue'
import AppIcon from './LandingIcon.vue'
import { formatCurrency, formatKm } from '../services/format'
import { historyMonths, visibleHistoryMonths, historyMonthLabel, historyShortDate, historyQuantity } from '../services/history'
const props = defineProps({ rows: { type: Array, default: () => [] }, cycles: { type: Array, default: () => [] }, limit: { type: Number, default: Infinity }, loading: Boolean, unavailable: Boolean, metricsUnavailable: Boolean })
const groups = computed(() => visibleHistoryMonths(historyMonths(props.rows, props.cycles), props.limit))
const mileage = row => row.category === 'manutencao' ? row.km_servico : row.km_abastecimento
</script>

<template>
  <div class="history-timeline" :aria-busy="loading">
    <p v-if="metricsUnavailable" class="history-note" role="status">O consumo não pôde ser carregado. Seus registros continuam disponíveis; use Atualizar para tentar novamente.</p>
    <p v-if="unavailable" class="history-empty" role="status">Não foi possível carregar o histórico. Use Atualizar para tentar novamente.</p>
    <p v-else-if="loading" class="history-empty" role="status">Carregando histórico…</p>
    <div v-else-if="groups.length" class="history-track">
      <section v-for="group in groups" :key="group.key" class="history-month" :aria-label="historyMonthLabel(group.key)">
        <span class="history-month-stop" aria-hidden="true"></span>
        <header class="history-month-summary">
          <h3>{{ historyMonthLabel(group.key) }}</h3>
          <div class="history-month-totals"><strong>{{ formatCurrency(group.totalCents / 100) }}</strong><span>Total no filtro · {{ group.recordCount }} {{ group.recordCount === 1 ? 'registro' : 'registros' }}</span><strong v-if="group.average != null">{{ historyQuantity(group.average) }} km/L</strong></div>
          <p v-if="group.pendingCents">{{ formatCurrency(group.pendingCents / 100) }} em manutenções pendentes, fora do total.</p>
          <p v-if="group.comparison" class="history-comparison">Média em km/L {{ group.comparison.percent === 0 ? 'igual à de' : historyQuantity(Math.abs(group.comparison.percent)) + '% ' + (group.comparison.percent > 0 ? 'acima de' : 'abaixo de') }} {{ historyMonthLabel(group.comparison.month) }}.</p>
          <p v-else-if="group.average != null" class="history-comparison">Média ponderada dos ciclos completos neste filtro.</p>
        </header>
        <ol class="history-entries">
          <li v-for="entry in group.entries" :key="entry.row.key" class="history-entry" :data-record-key="entry.row.key">
            <span class="history-marker" :class="'history-marker-' + entry.icon" aria-hidden="true"><AppIcon :name="entry.icon"/></span>
            <article class="history-entry-card" :aria-label="entry.title">
              <div class="history-entry-overview">
                <div class="history-entry-copy"><h4>{{ entry.title }}</h4><div class="history-entry-details">
                  <span v-if="mileage(entry.row) != null">{{ formatKm(mileage(entry.row)) }} km</span>
                  <strong v-if="entry.consumption != null">{{ historyQuantity(entry.consumption) }} km/L</strong>
                  <template v-if="entry.icon === 'fuel'"><span v-if="entry.row.litros != null">{{ historyQuantity(entry.row.litros) }} L</span><span v-if="entry.row.preco_litro != null">{{ formatCurrency(entry.row.preco_litro) }}/L</span></template>
                </div></div>
                <div class="history-entry-value"><strong>{{ formatCurrency(entry.row.valor) }}</strong><time :datetime="entry.row.data">{{ historyShortDate(entry.row.data) }}</time></div>
              </div>
              <p v-if="entry.row.descricao || entry.row.posto" class="history-description">{{ [entry.row.posto, entry.row.descricao].filter(Boolean).join(' · ') }}</p>
              <div v-if="entry.row.category === 'manutencao' || entry.icon === 'fuel'" class="history-entry-tags">
                <span v-if="entry.row.category === 'manutencao'" :class="{ 'history-pending': entry.row.status !== 'concluido' }">{{ entry.row.status === 'concluido' ? 'Feito' : 'Pendente' }}</span>
                <span v-if="entry.row.category === 'manutencao' && entry.row.natureza">{{ entry.row.natureza === 'preventiva' ? 'Preventiva' : 'Corretiva' }}</span>
                <span v-if="entry.icon === 'fuel' && entry.row.tanque_cheio != null">{{ entry.row.tanque_cheio ? 'Tanque cheio' : 'Parcial' }}</span>
              </div>
              <div v-if="$slots.actions" class="history-entry-actions"><slot name="actions" :row="entry.row"/></div>
            </article>
          </li>
        </ol>
      </section>
    </div>
    <p v-else-if="!loading" class="history-empty">Nenhum registro neste período ou filtro.</p>
    <p v-if="rows.some(row => row.tipo_despesa === 'combustivel')" class="history-note">Consumo disponível nos abastecimentos que fecham um ciclo válido entre tanques cheios, incluindo os parciais. Sem ciclo completo, a média fica em aberto.</p>
  </div>
</template>

<style scoped>
.history-timeline{min-width:0;color:var(--text)}
.history-track{position:relative;padding-left:70px}
.history-track::before,.history-track::after{content:'';position:absolute;top:16px;bottom:25px;left:23px;width:7px;background:var(--gray-600);border-radius:5px}
.history-track::after{left:26px;width:1px;border-radius:0;background:repeating-linear-gradient(to bottom,var(--surface) 0,var(--surface) 4px,transparent 4px,transparent 9px)}
.history-month{position:relative;margin-bottom:30px;min-width:0}.history-month:last-child{margin-bottom:0}
.history-month-stop{position:absolute;left:-57px;top:18px;width:26px;height:8px;background:var(--gray-400);border:3px solid var(--surface);box-sizing:content-box;border-radius:10px;z-index:1}
.history-month-summary{padding:16px 20px;margin:0 0 10px;border-radius:15px;background:var(--gray-100);border:1px solid var(--gray-200)}
.history-month-summary h3{font-size:11px;font-weight:700;line-height:1.5;letter-spacing:1.1px;text-transform:uppercase;color:var(--gray-600);margin:0 0 10px}
.history-month-totals{display:flex;align-items:baseline;flex-wrap:wrap;gap:5px 12px;font-size:14px;line-height:1.6}.history-month-totals>span{font-size:11px;color:var(--gray-600)}
.history-month-summary p{font-size:12px;line-height:1.6;margin:7px 0 0;color:var(--gray-600)}
.history-entries{list-style:none;margin:0;padding:0}.history-entry{position:relative;min-width:0}
.history-marker{position:absolute;left:-70px;top:14px;display:grid;place-items:center;width:53px;height:53px;background:var(--lime);color:#152000;border:4px solid var(--surface);border-radius:50%;z-index:1}
.history-marker svg{width:25px;height:25px}.history-marker-fuel{background:#ff9900;color:#241a06}.history-marker-wallet{background:var(--chrome-bg);color:var(--chrome-text)}
.history-entry-card{padding:22px 0 20px;border-bottom:1px solid var(--gray-200);min-width:0}
.history-entry-overview{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:start}
.history-entry-copy{min-width:0}.history-entry-copy h4{font-size:16px;line-height:1.45;font-weight:700;margin:0 0 6px;overflow-wrap:anywhere}
.history-entry-details{display:flex;flex-wrap:wrap;gap:5px 10px;color:var(--gray-600);font-size:13px;line-height:1.6}.history-entry-details strong{color:var(--text);font-weight:600}
.history-entry-details>span+span::before,.history-entry-details>strong::before,.history-entry-details>strong+span::before{content:'·';margin-right:8px;color:var(--gray-400)}
.history-entry-value{display:flex;flex-direction:column;align-items:flex-end;gap:7px;flex-shrink:0}.history-entry-value>strong{font-size:16px;line-height:1.45;font-weight:700;white-space:nowrap}.history-entry-value time{font-size:12px;color:var(--gray-600);white-space:nowrap}
.history-description{font-size:13px;line-height:1.7;color:var(--gray-600);margin:8px 0 0;overflow-wrap:anywhere}
.history-entry-tags{display:flex;flex-wrap:wrap;gap:6px;margin-top:9px}.history-entry-tags span{padding:3px 7px;border-radius:5px;font-size:10px;font-weight:700;background:var(--lime-pale);color:var(--on-lime-pale)}.history-entry-tags .history-pending{background:var(--amber-pale);color:var(--on-amber)}
.history-entry-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.history-note{font-size:12px;line-height:1.7;color:var(--gray-600);margin:14px 0 0}.history-empty{padding:24px 0;font-size:14px;line-height:1.7;color:var(--gray-600)}
@media(max-width:600px){.history-track{padding-left:48px}.history-track::before{left:16px;width:5px}.history-track::after{left:18px}.history-month-stop{left:-43px;width:20px}.history-marker{left:-48px;width:37px;height:37px;border-width:3px;top:20px}.history-marker svg{width:19px;height:19px}.history-month-summary{padding:13px 14px}.history-entry-overview{gap:10px}.history-entry-copy h4,.history-entry-value>strong{font-size:14px}.history-entry-details{font-size:12px;gap:3px 7px}.history-entry-value time{font-size:11px}.history-entry-card{padding:20px 0 18px}.history-entry-details>span+span::before,.history-entry-details>strong::before,.history-entry-details>strong+span::before{margin-right:6px}.history-entry-actions:deep(.button){min-height:40px}}
@media(max-width:359px){.history-entry-overview{grid-template-columns:minmax(0,1fr)}.history-entry-value{flex-direction:row;align-items:center;justify-content:space-between}.history-month-summary{padding:12px}.history-entry-actions{gap:6px}}
@media print{.history-entry-actions{display:none}.history-month{break-inside:avoid}}
</style>
