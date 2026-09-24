<template>
  <section class="hero-grid">
    <article class="hero-card">
      <span class="badge">PWA + API Laravel</span>
      <h2>Menos ansiedade, mais previsibilidade na rotina da moto.</h2>
      <p>
        Acompanhe trocas de óleo, revisões, abastecimentos e custos mensais com alertas
        automáticos e uma experiência feita para uso diário no celular.
      </p>

      <div class="actions">
        <a class="button primary" href="#dashboard">Abrir dashboard</a>
        <a class="button secondary" href="https://vuejs.org/" target="_blank" rel="noreferrer">Stack Vue</a>
      </div>
    </article>

    <aside class="stats-panel" id="dashboard">
      <div class="stat">
        <strong>90%</strong>
        <span>gatilho para alertas de revisão</span>
      </div>
      <div class="stat">
        <strong>24/7</strong>
        <span>acesso mobile e offline com service worker</span>
      </div>
      <div class="stat">
        <strong>JSON</strong>
        <span>backend REST desacoplado em Laravel</span>
      </div>
    </aside>

    <section class="feature-grid">
      <article>
        <h3>Manutenção preventiva</h3>
        <p>Registre km, peças de desgaste e histórico completo da moto.</p>
      </article>
      <article>
        <h3>Finanças da moto</h3>
        <p>Consolide combustível e reparos para enxergar o custo real do mês.</p>
      </article>
      <article>
        <h3>Alertas em tempo real</h3>
        <p>Prepare a integração com FCM para notificar revisões antes da falha.</p>
      </article>
    </section>
  </section>
</template>