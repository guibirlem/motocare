<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import MotorcycleIcon from '../components/MotorcycleIcon.vue'
import ThemeToggle from '../components/ThemeToggle.vue'
import LandingIcon from '../components/LandingIcon.vue'
import LandingPreview from '../components/LandingPreview.vue'
import SeriesChart from '../components/SeriesChart.vue'
import { workspace } from '../services/workspace'
import { presentationAccess } from '../services/presentation'
const route = useRoute(), menuOpen = ref(false)
const start = computed(() => presentationAccess(workspace.user, true)), login = computed(() => presentationAccess(workspace.user))
const year = new Date().getFullYear()
const sections = [ ['funcionalidades', 'Funcionalidades'], ['diferenciais', 'Diferenciais para Motos'], ['relatorios', 'Relatórios'], ['beneficios', 'Benefícios'] ]
const features = [
  { icon: 'fuel', title: 'Abasteça. Registre. Entenda.', text: 'Seu posto favorito já lembra o último preço por combustível. Registre litros e valor e acompanhe o consumo médio entre tanques cheios, incluindo os abastecimentos parciais.', tag: 'Abastecimento inteligente' },
  { icon: 'service', title: 'Cada peça tem seu cuidado.', text: 'Óleo, filtros, pneus dianteiro e traseiro, pastilhas ou lonas, fluidos e cabos: um histórico organizado para a realidade de quem anda sobre duas rodas.', tag: 'Manutenção de verdade' },
  { icon: 'bell', title: 'O próximo cuidado, no radar.', text: 'Programe a revisão por quilometragem e por data. O MotoCare avisa pelo que vencer primeiro, com lembretes no painel e notificações quando ativadas.', tag: 'Alertas por KM ou tempo' },
  { icon: 'chart', title: 'Menos achismo. Mais clareza.', text: 'Veja o custo por quilômetro, a evolução do consumo e os preços registrados nos seus postos. Entenda quanto custa rodar e para onde vai o dinheiro.', tag: 'Relatórios e gráficos' },
]
const benefits = [
  { icon: 'wallet', title: 'Economia real', text: 'Compare os preços que você registrou nos postos favoritos e enxergue cada gasto com combustível, manutenção e documentação.' },
  { icon: 'shield', title: 'Segurança na pilotagem', text: 'Troque o “depois eu lembro” por uma rotina organizada. Acompanhe óleo, corrente, pneus e freios antes de pegar a estrada.' },
  { icon: 'history', title: 'Histórico que valoriza', text: 'Guarde datas, quilometragens e cuidados realizados. Na hora de vender, fica mais fácil mostrar como você cuidou da sua moto.' },
]
onMounted(() => {
  document.documentElement.classList.add('landing-active')
  if (route.hash) requestAnimationFrame(() => document.getElementById(route.hash.slice(1))?.scrollIntoView())
})
onUnmounted(() => document.documentElement.classList.remove('landing-active'))
</script>

<template>
  <div class="landing-page" @keydown.esc="menuOpen = false">
    <a class="lp-skip" href="#conteudo">Pular para o conteúdo</a>
    <header class="lp-header">
      <div class="lp-container lp-navbar">
        <RouterLink class="lp-brand" to="/apresentacao" aria-label="MotoCare — apresentação"><span><MotorcycleIcon :size="25" /></span><strong>MotoCare</strong></RouterLink>
        <nav id="landing-navigation" class="lp-navigation" :class="{ 'is-open': menuOpen }" aria-label="Navegação da apresentação"><a v-for="[id, name] in sections" :key="id" :href="'#' + id" @click="menuOpen = false">{{ name }}</a><RouterLink :to="start" class="button button-lime lp-menu-cta" @click="menuOpen = false">Começar Grátis</RouterLink></nav>
        <div class="lp-header-actions"><div class="lp-theme"><ThemeToggle /></div><RouterLink :to="login" class="lp-login">{{ workspace.user ? 'Meu painel' : 'Entrar' }}</RouterLink><RouterLink :to="start" class="button button-lime lp-nav-cta">{{ workspace.user ? 'Acessar sistema' : 'Começar Grátis' }}</RouterLink><button class="lp-menu" :aria-expanded="menuOpen" aria-controls="landing-navigation" @click="menuOpen = !menuOpen">{{ menuOpen ? 'Fechar' : 'Menu' }}</button></div>
      </div>
    </header>

    <main id="conteudo">
      <section class="lp-hero">
        <div class="lp-container lp-hero-grid">
          <div class="lp-hero-copy"><p class="lp-eyebrow"><span></span>FEITO PARA QUEM PILOTA</p><h1>Mais controle da moto.<br /><span>Mais liberdade</span><br />no caminho.</h1><p class="lp-lead">Economize no combustível, acompanhe seus gastos e mantenha a manutenção em dia. Tudo pelo celular ou computador, em poucos segundos.</p><RouterLink :to="start" class="button button-lime lp-primary">Usar Agora Gratuitamente <span aria-hidden="true">↗</span></RouterLink><p class="lp-free-note"><span aria-hidden="true">✓</span> 100% gratuito. Sem mensalidades ou recursos bloqueados.</p><div class="lp-hero-tags"><span>Do posto à oficina</span><span>Celular e computador</span><span>Exclusivo para motos</span></div></div>
          <LandingPreview />
        </div>
      </section>

      <div class="lp-ribbon"><div class="lp-container"><p>Menos papel. Menos conta de cabeça.</p><strong>Mais tempo para o que importa: pilotar.</strong><MotorcycleIcon :size="36" /></div></div>

      <section id="funcionalidades" class="lp-section lp-container" tabindex="-1">
        <div class="lp-section-heading"><div><p class="lp-eyebrow">TUDO NO MESMO LUGAR</p><h2>Sua rotina sobre duas rodas.<br />Agora, organizada.</h2></div><p>Do registro rápido na bomba ao próximo cuidado na oficina, cada detalhe da sua moto ganha um lugar.</p></div>
        <div class="lp-feature-grid"><article v-for="(feature, index) in features" :key="feature.title" class="lp-feature"><div class="lp-feature-top"><span class="lp-feature-icon"><LandingIcon :name="feature.icon" /></span><span class="lp-number">0{{ index + 1 }}</span></div><p class="lp-feature-tag">{{ feature.tag }}</p><h3>{{ feature.title }}</h3><p>{{ feature.text }}</p></article></div>
      </section>

      <section id="diferenciais" class="lp-moto-section" tabindex="-1"><div class="lp-container">
        <div class="lp-section-heading"><div><p class="lp-eyebrow">DUAS RODAS. OUTRA ROTINA.</p><h2>Quem pilota moto<br />precisa de mais.</h2></div><p>Detalhes que fazem sentido no seu dia a dia, da corrente que pegou chuva à moto sem marcador de combustível.</p></div>
        <div class="lp-differences">
          <article class="lp-chain-feature"><div class="lp-difference-copy"><span class="lp-feature-icon"><LandingIcon name="chain" /></span><h3>Corrente cuidada.<br />Caminho mais tranquilo.</h3><p>Lubrificação, ajuste de tensão e troca do kit relação têm controles separados. Acompanhe quantas esticadas foram feitas desde a última troca.</p><p>Pegou chuva ou lavou a moto? Um toque cria o lembrete de lubrificação para hoje.</p><RouterLink :to="start" class="lp-text-link">Cuidar da minha moto <span aria-hidden="true">→</span></RouterLink></div><div class="lp-chain-demo" aria-label="Exemplo ilustrativo de cuidados da corrente"><p>GESTOR DO KIT RELAÇÃO</p><div><span>Lubrificação</span><strong>Ciclo curto</strong></div><div><span>Ajuste de tensão</span><strong>Histórico de esticadas</strong></div><div><span>Troca do kit completo</span><strong>Uma nova referência</strong></div><span class="lp-rain-preview"><LandingIcon name="drop" />Peguei Chuva / Lavei a Moto</span><small>Prévia ilustrativa do atalho no painel.</small></div></article>
          <article class="lp-reserve-feature"><span class="lp-feature-icon"><LandingIcon name="fuel" /></span><div><h3>Sua próxima parada,<br />sem tanto achismo.</h3><p>Capacidade do tanque, volume de reserva e consumo histórico ajudam a estimar os quilômetros que ainda restam.</p></div><div class="lp-range-demo" aria-label="Exemplo ilustrativo: 185 quilômetros até a reserva"><div><small>ATÉ A RESERVA</small><strong>185 <span>km</span></strong></div><div class="lp-range-track"><span></span><i></i></div><div class="lp-range-labels"><span>Reserva</span><span>Tanque cheio</span></div></div><p class="lp-detail">Estimativa pelos seus registros, com parcial virtual e distância até esvaziar. Não é uma medição do combustível no tanque.</p></article>
        </div>
      </div></section>

      <section id="relatorios" class="lp-section lp-container lp-reports" tabindex="-1"><div class="lp-report-copy"><p class="lp-eyebrow">CADA QUILÔMETRO CONTA</p><h2>Descubra quanto<br />custa a sua liberdade.</h2><p>Veja o custo real por quilômetro registrado, acompanhe o consumo e compare a variação do preço do combustível nos seus postos favoritos.</p><ul class="lp-check-list"><li>Combustível, manutenção preventiva e corretiva</li><li>Documentação, impostos e outras despesas</li><li>Filtros por período e por moto</li><li>CSV e impressão para salvar em PDF</li></ul><RouterLink to="/relatorios" class="button button-black lp-report-cta">Ver meus relatórios <span aria-hidden="true">→</span></RouterLink></div><div class="lp-report-preview"><div class="lp-report-title"><span><LandingIcon name="chart" /></span><div><small>SUA ROTINA EM NÚMEROS</small><h3>Mais clareza em cada mês.</h3></div></div><SeriesChart title="Evolução do consumo" unit="km/L" :points="[{ label: 'Mar', value: 28.6 }, { label: 'Abr', value: 30.2 }, { label: 'Mai', value: 29.4 }, { label: 'Jun', value: 31.4 }]" /><div class="lp-spend-demo"><div><span>Combustível</span><b>58%</b></div><div class="lp-spend-track"><span style="width:58%"></span></div><div><span>Manutenção</span><b>30%</b></div><div class="lp-spend-track"><span style="width:30%"></span></div><div><span>Outras despesas</span><b>12%</b></div><div class="lp-spend-track"><span style="width:12%"></span></div></div><p class="lp-detail">Prévia ilustrativa. Valores e percentuais de exemplo.</p></div></section>

      <section id="beneficios" class="lp-benefits-section" tabindex="-1"><div class="lp-container"><div class="lp-centered-heading"><p class="lp-eyebrow">BOM PARA A MOTO. MELHOR PARA VOCÊ.</p><h2>Três motivos para começar<br />no próximo abastecimento.</h2></div><div class="lp-benefits"><article v-for="benefit in benefits" :key="benefit.title"><LandingIcon :name="benefit.icon" /><h3>{{ benefit.title }}</h3><p>{{ benefit.text }}</p></article></div></div></section>

      <section class="lp-final-section lp-container"><div class="lp-final-banner"><div><p class="lp-eyebrow">SUA MOTO MERECE ESSE CUIDADO.</p><h2>Todas as ferramentas.<br />Zero mensalidade.</h2><p>O MotoCare é 100% gratuito para qualquer motociclista. Sem planos pagos, sem versão Pro e sem funções escondidas atrás de uma assinatura.</p></div><div class="lp-final-action"><RouterLink :to="start" class="button button-black lp-primary">Começar Gratuitamente <span aria-hidden="true">↗</span></RouterLink><RouterLink :to="login">{{ workspace.user ? 'Voltar ao meu painel →' : 'Já tenho conta. Entrar →' }}</RouterLink></div></div></section>
    </main>

    <footer class="lp-footer"><div class="lp-container"><div class="lp-footer-top"><div><RouterLink class="lp-brand" to="/apresentacao"><span><MotorcycleIcon :size="25" /></span><strong>MotoCare</strong></RouterLink><p>Gestão de gastos e manutenção<br />para quem vive sobre duas rodas.</p></div><nav aria-label="Links rápidos da apresentação"><strong>Explore o MotoCare</strong><a v-for="[id, name] in sections" :key="id" :href="'#' + id">{{ name }}</a></nav><div class="lp-footer-access"><strong>Um cuidado que cabe na rotina.</strong><p>No celular, no computador.<br />Sempre gratuito.</p><RouterLink :to="start" class="lp-text-link">Acessar o sistema <span aria-hidden="true">→</span></RouterLink></div></div><div class="lp-footer-bottom"><small>© {{ year }} MotoCare. Todos os direitos reservados.</small><span>Feito para motos. Feito para você.</span></div></div></footer>
  </div>
</template>

<style scoped src="../styles/landing.css"></style>
