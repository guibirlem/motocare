<script setup>
import SectionTitle from '../components/SectionTitle.vue'
import MetricCard from '../components/MetricCard.vue'
import StatusBadge from '../components/StatusBadge.vue'
import { formatKm, maintenanceAlerts } from '../data/demo'

const alerts = maintenanceAlerts.map((alert) => ({
  ...alert,
  percentage: Math.round(alert.currentKm / alert.limitKm * 100),
  remainingKm: alert.limitKm - alert.currentKm,
  triggerKm: alert.limitKm * 0.9,
}))
</script>

<template>
  <div class="page-stack">
    <section aria-labelledby="alerts-title">
      <SectionTitle id="alerts-title">Alertas de Manutenção</SectionTitle>
      <div class="metrics-grid metrics-three">
        <MetricCard icon="⚠️" label="Alertas pendentes" :value="alerts.length" sub="óleo e revisões" accent />
        <MetricCard icon="🔧" label="Limite ultrapassado" :value="1" sub="programe sua revisão" />
        <MetricCard icon="🔔" label="Aviso automático" value="90%" sub="da quilometragem limite" />
      </div>
    </section>
    <div class="alerts-columns">
      <section aria-labelledby="maintenance-alerts-title">
        <SectionTitle id="maintenance-alerts-title">Cuidados Pendentes</SectionTitle>
        <div class="maintenance-alert-list">
          <article v-for="alert in alerts" :key="alert.id" class="panel maintenance-alert" :class="{ 'maintenance-alert-overdue': alert.remainingKm <= 0 }">
            <div class="alert-card-heading">
              <div class="alert-card-name"><span aria-hidden="true">{{ alert.icon }}</span><div><h3>{{ alert.title }}</h3><p>{{ alert.motorcycle }}</p></div></div>
              <StatusBadge :tone="alert.tone">{{ alert.status }}</StatusBadge>
            </div>
            <div class="alert-progress-label"><span>Quilometragem até o serviço</span><strong>{{ alert.percentage }}%</strong></div>
            <div class="alert-progress" role="img" :aria-label="alert.percentage + '% do limite atingido'"><span :style="{ width: Math.min(alert.percentage, 100) + '%' }"></span><i aria-hidden="true"></i></div>
            <div class="alert-km-labels"><span>Atual: <strong>{{ formatKm(alert.currentKm) }} km</strong></span><span>Limite: <strong>{{ formatKm(alert.limitKm) }} km</strong></span></div>
            <div class="alert-card-footer">
              <strong v-if="alert.remainingKm > 0">Vence em {{ formatKm(alert.remainingKm) }} km</strong>
              <strong v-else>{{ formatKm(-alert.remainingKm) }} km além do limite</strong>
              <span>Aviso a partir de {{ formatKm(alert.triggerKm) }} km</span>
            </div>
          </article>
        </div>
      </section>
      <aside class="alerts-information">
        <section class="panel notification-panel">
          <h3>🔔 Notificação de Alerta</h3>
          <p>Ao atingir 90% da quilometragem limite, receba um lembrete para planejar a manutenção.</p>
          <div class="notification-example">
            <div><span>MOTOCARE</span><span>Exemplo</span></div>
            <strong>Troca de Óleo</strong>
            <p>Honda CB 500F — Vence em 200 km. Programe o próximo cuidado da sua moto.</p>
          </div>
          <span class="field-hint">Prévia demonstrativa da notificação</span>
        </section>
        <section class="panel alerts-guide">
          <h3>Como Funcionam os Alertas</h3>
          <ol><li><span>1</span>Acompanhe a quilometragem.</li><li><span>2</span>Receba o aviso aos 90% do limite.</li><li><span>3</span>Programe o serviço antes de vencer.</li></ol>
          <RouterLink class="button button-lime" to="/manutencoes">Ver Manutenções</RouterLink>
        </section>
      </aside>
    </div>
  </div>
</template>
