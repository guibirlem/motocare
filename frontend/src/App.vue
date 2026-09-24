<template>
  <div class="shell">
    <header class="topbar">
      <div>
        <p class="eyebrow">MotoCare</p>
        <h1>Controle de manutenção e custo para quem depende da moto todos os dias.</h1>
      </div>

      <nav class="nav">
        <RouterLink to="/">Visão geral</RouterLink>
      </nav>
    </header>

    <main>
      <RouterView />
    </main>
  </div>
</template>