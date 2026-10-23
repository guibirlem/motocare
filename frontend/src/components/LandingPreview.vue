<script setup>
import MetricCard from './MetricCard.vue'
import MotorcycleIcon from './MotorcycleIcon.vue'
</script>
<template>
  <figure class="lp-preview">
    <div class="lp-desktop-preview" aria-hidden="true">
      <div class="lp-window-bar"><span class="lp-window-dots"><i></i><i></i><i></i></span><span>Seu próximo destino: tranquilidade.</span></div>
      <div class="lp-preview-app">
        <div class="lp-preview-sidebar"><MotorcycleIcon :size="26" /><strong>MotoCare</strong><span class="lp-preview-selected">Visão geral</span><span>Abastecimentos</span><span>Manutenções</span><span>Relatórios</span><span>Minha garagem</span></div>
        <div class="lp-preview-main">
          <div class="lp-preview-heading"><span>SUA MOTO, EM DIA</span><strong>Visão Geral</strong><small>Honda CG 160 · Minha garagem</small></div>
          <div class="lp-preview-metrics"><MetricCard label="Gasto do mês" value="R$ 248,90" accent /><MetricCard label="Consumo médio" value="31,4 km/L" /><MetricCard label="Custo por km" value="R$ 0,18" /></div>
          <div class="lp-preview-panels"><div class="lp-preview-feed"><strong>Últimos registros</strong><div><span class="lp-feed-dot"></span><p>Abastecimento<small>Posto favorito · tanque cheio</small></p><b>R$ 62,90</b></div><div><span class="lp-feed-dot lp-feed-neutral"></span><p>Óleo do motor<small>Manutenção concluída</small></p><b>R$ 45,00</b></div><div><span class="lp-feed-dot"></span><p>Corrente lubrificada<small>Mais um cuidado em dia</small></p><b>✓</b></div></div><div class="lp-preview-mini-chart"><strong>Consumo em evolução</strong><div class="lp-preview-bars"><i style="height:42%"></i><i style="height:58%"></i><i style="height:48%"></i><i style="height:72%"></i><i style="height:84%"></i></div><small>Mar · Abr · Mai · Jun · Jul</small></div></div>
          <div class="lp-preview-reminder"><span class="lp-feed-dot"></span><div><strong>Próximo cuidado: óleo do motor</strong><small>Organize seus lembretes por KM e por data.</small></div><span>Ver alerta →</span></div>
        </div>
      </div>
    </div>
    <div class="lp-phone-preview" aria-hidden="true"><span class="lp-phone-speaker"></span><div class="lp-phone-top"><MotorcycleIcon :size="18" /><strong>MotoCare</strong><span>☰</span></div><div class="lp-phone-body"><small>SUA MOTO, EM DIA</small><strong>Pronto para rodar.</strong><div class="lp-phone-odometer"><small>HODÔMETRO ATUAL</small><b>12.480 <span>km</span></b></div><div class="lp-phone-range"><small>ATÉ A RESERVA</small><strong>185 km</strong><div><span></span></div><small>Estimativa pelos seus registros</small></div><div class="lp-phone-chain"><span>Corrente e kit relação</span><strong>Choveu no caminho?</strong><span class="lp-phone-rain">Peguei chuva / Lavei a moto</span></div><span class="lp-phone-register">+ Registrar</span></div></div>
    <figcaption>Prévia ilustrativa do painel e do celular · dados de exemplo.</figcaption>
  </figure>
</template>

<style scoped>
.lp-preview { margin: 0; position: relative; padding-bottom: 58px; min-width: 0; width: 100%; max-width: 680px; justify-self: end; }
.lp-desktop-preview { background: var(--surface); border: 1px solid var(--gray-200); box-shadow: 0 22px 50px #00000012, 12px -12px 0 var(--lime); }
.lp-window-bar { height: 31px; display: flex; align-items: center; gap: 18px; padding-inline: 12px; background: var(--chrome-bg); color: #a8b2ba; font-size: 8px; }
.lp-window-dots { display: flex; gap: 4px; }
.lp-window-dots i { width: 5px; height: 5px; border-radius: 50%; background: #717e88; }
.lp-window-dots i:first-child { background: var(--lime); }
.lp-preview-app { display: grid; grid-template-columns: 100px minmax(0,1fr); min-height: 335px; }
.lp-preview-sidebar { display: flex; flex-direction: column; background: var(--chrome-bg); color: #a8b2ba; font-size: 8px; gap: 18px; padding: 18px 10px; }
.lp-preview-sidebar > svg { color: var(--lime); }
.lp-preview-sidebar > strong { color: white; font-size: 11px; margin-bottom: 7px; }
.lp-preview-selected { color: var(--black); background: var(--lime); margin-inline: -10px; padding: 8px 10px; font-weight: 700; }
.lp-preview-main { padding: 18px 16px 22px; background: var(--gray-100); min-width: 0; }
.lp-preview-heading { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
.lp-preview-heading > span { color: var(--gray-600); font-size: 7px; letter-spacing: .1em; }
.lp-preview-heading strong { font-size: 19px; letter-spacing: -.5px; line-height: 1.2; }
.lp-preview-heading small { font-size: 8px; color: var(--gray-600); }
.lp-preview-metrics { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 7px; }
.lp-preview :deep(.metric-card) { padding: 12px 9px; gap: 7px; min-height: 70px; }
.lp-preview :deep(.metric-label) { font-size: 6px; letter-spacing: .025em; }
.lp-preview :deep(.metric-value) { font-size: 16px; line-height: 1.25; white-space: nowrap; }
.lp-preview-panels { display: grid; grid-template-columns: 1.3fr .8fr; gap: 9px; margin-top: 11px; }
.lp-preview-feed, .lp-preview-mini-chart { padding: 12px 10px; background: var(--surface); border: 1px solid var(--gray-200); min-width: 0; }
.lp-preview-feed > strong, .lp-preview-mini-chart > strong { font-size: 8px; }
.lp-preview-feed > div { display: flex; align-items: center; gap: 6px; font-size: 7px; padding-block: 9px; border-bottom: 1px solid var(--gray-200); }
.lp-preview-feed > div:last-child { border-bottom: 0; padding-bottom: 0; }
.lp-feed-dot { display: block; width: 5px; height: 5px; border-radius: 50%; background: var(--lime-dark); flex-shrink: 0; }
.lp-feed-neutral { background: var(--gray-600); }
.lp-preview-feed p { flex: 1; line-height: 1.4; }
.lp-preview-feed p small { display: block; font-size: 6px; color: var(--gray-600); margin-top: 2px; }
.lp-preview-feed b { font-size: 7px; white-space: nowrap; }
.lp-preview-bars { display: flex; align-items: flex-end; gap: 9px; height: 88px; border-bottom: 1px solid var(--gray-200); padding-top: 12px; }
.lp-preview-bars > i { flex: 1; background: var(--lime); }
.lp-preview-mini-chart > small { font-size: 6px; color: var(--gray-600); }
.lp-preview-reminder { display: flex; gap: 6px; align-items: center; padding: 10px; margin-top: 10px; background: var(--lime-pale); border-left: 2px solid var(--lime-dark); font-size: 7px; }
.lp-preview-reminder div { flex: 1; }
.lp-preview-reminder strong { display: block; font-size: 7px; }
.lp-preview-reminder small { display: block; margin-top: 3px; font-size: 6px; color: var(--gray-600); }
.lp-preview-reminder > span:last-child { font-weight: 700; font-size: 6px; }
.lp-phone-preview { width: 172px; background: #101619; color: #e9f0f5; border: 5px solid #20292f; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 35px #0004; position: absolute; bottom: 20px; right: -15px; }
.lp-phone-speaker { display: block; height: 4px; width: 36px; margin: 5px auto 3px; background: #3a464f; border-radius: 4px; }
.lp-phone-top { display: flex; align-items: center; gap: 5px; padding: 10px; background: #090d10; font-size: 9px; }
.lp-phone-top > svg { color: var(--lime); }
.lp-phone-top > span { margin-left: auto; }
.lp-phone-body { padding: 13px 10px 12px; display: flex; flex-direction: column; gap: 10px; }
.lp-phone-body > small { color: #a8b2ba; font-size: 6px; letter-spacing: .08em; }
.lp-phone-body > strong { font-size: 15px; line-height: 1.3; }
.lp-phone-odometer { background: var(--lime); color: var(--black); padding: 12px; display: flex; flex-direction: column; gap: 6px; }
.lp-phone-odometer > small { font-size: 6px; font-weight: 700; }
.lp-phone-odometer > b { font-size: 23px; line-height: 1; letter-spacing: -.5px; }
.lp-phone-odometer b > span { font-size: 10px; }
.lp-phone-range { border: 1px solid #344149; padding: 10px; }
.lp-phone-range > small { display: block; font-size: 6px; color: #a8b2ba; }
.lp-phone-range > strong { font-size: 22px; color: var(--lime); }
.lp-phone-range > div { height: 5px; background: #344149; margin-block: 8px; }
.lp-phone-range > div span { display: block; width: 65%; height: 100%; background: var(--lime); }
.lp-phone-chain { display: flex; flex-direction: column; gap: 4px; font-size: 6px; }
.lp-phone-chain > strong { font-size: 9px; }
.lp-phone-rain { background: var(--lime); color: var(--black); padding: 7px 3px; font-size: 6px; text-align: center; font-weight: 700; margin-top: 3px; }
.lp-phone-register { align-self: flex-end; font-weight: 700; font-size: 7px; }
.lp-preview figcaption { position: absolute; left: 0; bottom: 0; font-size: 9px; color: var(--gray-600); max-width: calc(100% - 90px); }
@media (max-width: 1100px) and (min-width: 951px) { .lp-preview-app { grid-template-columns: 77px minmax(0,1fr); } .lp-preview-main { padding: 15px 10px; } .lp-preview :deep(.metric-value) { font-size: 13px; } .lp-preview :deep(.metric-card) { padding-inline: 6px; } .lp-phone-preview { width: 145px; right: -8px; } .lp-preview-panels { grid-template-columns: 1fr; } .lp-preview-mini-chart { display: none; } }
@media (max-width: 950px) { .lp-preview { max-width: 670px; margin-inline: auto; width: calc(100% - 24px); } .lp-preview-app { min-height: 375px; grid-template-columns: 120px minmax(0,1fr); } .lp-preview-main { padding: 22px; } .lp-phone-preview { width: 180px; right: -12px; } }
@media (max-width: 640px) { .lp-preview { width: calc(100% - 12px); margin-left: 0; padding-bottom: 45px; } .lp-desktop-preview { box-shadow: 7px -7px 0 var(--lime), 0 12px 24px #00000015; } .lp-window-bar { font-size: 6px; height: 24px; gap: 10px; padding-inline: 8px; } .lp-preview-app { grid-template-columns: 70px minmax(0,1fr); min-height: 318px; } .lp-preview-sidebar { padding: 14px 7px; font-size: 6px; gap: 16px; } .lp-preview-sidebar > strong { font-size: 9px; } .lp-preview-selected { margin-inline: -7px; padding-inline: 7px; } .lp-preview-main { padding: 15px 10px; } .lp-preview-heading strong { font-size: 17px; } .lp-preview-metrics { grid-template-columns: 1fr 1fr; gap: 5px; } .lp-preview-metrics > :last-child { display: none; } .lp-preview :deep(.metric-card) { padding: 9px 6px; min-height: 60px; } .lp-preview :deep(.metric-value) { font-size: 14px; } .lp-preview :deep(.metric-label) { font-size: 6px; } .lp-preview-panels { grid-template-columns: 1fr; } .lp-preview-feed { padding: 10px 7px; } .lp-preview-mini-chart { display: none; } .lp-preview-feed > div { padding-block: 8px; } .lp-preview-reminder { padding: 8px; } .lp-preview-reminder > span:last-child { display: none; } .lp-preview-reminder small { font-size: 5px; } .lp-phone-preview { width: 125px; right: -10px; bottom: 22px; border-width: 4px; border-radius: 16px; } .lp-phone-body { padding: 10px 7px; gap: 9px; } .lp-phone-body > strong { font-size: 11px; } .lp-phone-top { padding: 7px; font-size: 7px; } .lp-phone-top > svg { width: 15px; } .lp-phone-odometer { padding: 10px 8px; } .lp-phone-odometer > b { font-size: 19px; } .lp-phone-range { padding: 8px; } .lp-phone-range > strong { font-size: 19px; } .lp-phone-chain > strong { font-size: 8px; } .lp-phone-rain { font-size: 5px; } .lp-preview figcaption { max-width: 215px; font-size: 8px; line-height: 1.6; } }
@media (max-width: 359px) { .lp-preview-app { grid-template-columns: 56px minmax(0,1fr); } .lp-preview-sidebar { font-size: 5px; } .lp-preview-sidebar > strong { font-size: 8px; } .lp-preview :deep(.metric-value) { font-size: 12px; } .lp-preview :deep(.metric-label) { font-size: 5px; } .lp-phone-preview { width: 112px; } .lp-phone-body > small { font-size: 5px; } .lp-phone-odometer > b { font-size: 17px; } }
</style>
