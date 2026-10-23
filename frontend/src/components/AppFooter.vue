<script setup>
const year = new Date().getFullYear()
</script>
<template>
  <footer class="app-footer no-print" role="contentinfo">
    <div class="footer-brand"><RouterLink to="/" aria-label="MotoCare — voltar ao painel">Moto<span>Care</span></RouterLink><p>Sua moto em dia. Sua rotina mais tranquila.</p></div>
    <nav class="footer-links" aria-label="Links do rodapé"><RouterLink to="/garagem">Minha garagem</RouterLink><RouterLink to="/alertas">Meus alertas</RouterLink><RouterLink to="/configuracoes">Minha conta</RouterLink></nav>
    <small>© {{ year }} MotoCare</small>
  </footer>
</template>
