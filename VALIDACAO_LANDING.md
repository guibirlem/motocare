# Landing Page MotoCare — 23/10/2026

Página de apresentação adicionada ao projeto Vue existente, sem substituir telas/serviços, alterar o backend ou criar banco/credenciais. Referência de organização consultada: [site oficial do Drivvo](https://www.drivvo.com/pt-BR/). Textos, marca, ícones e prévias são próprios do MotoCare; sem copiar números de usuários, avaliações, depoimentos, planos ou funcionalidades que o projeto não possui.

## Entrega

- Navbar responsiva com marca, tema, âncoras Funcionalidades/Diferenciais para Motos/Relatórios/Benefícios e acesso ao sistema. No celular, cadastro também disponível no menu.
- Hero com proposta de controle da moto, combustível/manutenção, CTA gratuito e mockup desktop/celular. Prévias usam MotorcycleIcon, MetricCard e estilos locais; gráfico ilustrativo reutiliza SeriesChart. Todos os dados demonstrativos estão identificados e separados do painel real.
- Abastecimentos/postos e consumo; manutenções específicas por componente/eixo; alertas por KM/data; relatórios e gráficos.
- Destaques para lubrificação/tensão/troca do kit, atalho de chuva/lavagem e reserva/autonomia estimada, respeitando as limitações reais do produto.
- Pilares de economia, segurança/cuidado e histórico para revenda; chamada final de acesso totalmente gratuito e rodapé com links reais. Nenhuma tabela de preços ou contratação.
- Paleta/Arial/CSS nativos preservados; tema claro/noturno, navegação por teclado, link de pular conteúdo, redução de movimento e CSS escopado. Código da landing em chunk separado, sem dependências novas ou imagens externas.

## Integração preservada

| Acesso | Comportamento |
| --- | --- |
| `/` visitante | Landing pública |
| `/` autenticado | Dashboard existente |
| `/apresentacao` | Landing pública para qualquer sessão |
| `/dashboard` visitante | Login existente no AccountGate |
| `/dashboard?cadastro=1` visitante | Cadastro selecionado no formulário existente |
| CTA de conta conectada | `/dashboard`, sem cadastro forçado |
| `/gastos`, `/relatorios` e demais deep links | Mesmas telas/autenticação/feature anteriores |

`presentation.js` concentra somente a seleção da apresentação e os destinos de CTA. `App.vue` acrescenta a condição pública, `AccountGate` interpreta a opção de cadastro e o router acrescenta apenas `/apresentacao`. O rodapé do sistema ganha o link “Sobre o MotoCare”, preservando os links anteriores. HomeView e todo o backend permanecem sem alterações nesta entrega. Cookies, CSRF, senhas e regras de negócio continuam nos serviços anteriores.

## Verificações

- **40 testes frontend aprovados**: 38 regressões anteriores mais preservação das rotas/autenticação e destinos dos CTAs. Build de produção aprovado, com chunk de landing separado.
- Chrome isolado em320/390/768/1024/1440/1920px, claro/noturno: sem overflow horizontal ou títulos/CTAs fora do viewport. Capturas do hero, funcionalidades, diferenciais, relatórios, benefícios e chamada final/rodapé conferidas.
- Âncoras e menu mobile aprovados. Entrar abre login; CTA gratuito abre cadastro e cria conta descartável pelo backend existente; painel abre após cadastro; raiz autenticada mantém HomeView. Conta conectada abre `/apresentacao`, volta ao dashboard e acessa Relatórios. Após logout, rotas protegidas continuam pedindo login.
- API503 simulada não impede a apresentação online; modo offline/reconexão existentes preservados. Movimento reduzido desativa rolagem suave. Nenhuma exceção JavaScript no fluxo aprovado.
- Regressão T3 com CRUD/trocas de tipo, odômetro, filtros/erros, duas motos/contas, CSV/impressão, relatórios, temas, feature desligada, isolamento404, CSRF419 e logout401 aprovada após a integração.
- Testes de navegador/HTTP usam API8010, preview5190 e SQLite isolado em tmp, com migrations existentes e fixtures próprias removidas. Não representam teste novo no MySQL nem no iPhone/Safari físico. Nenhum dado real, `.env`, chave, notificação ou scheduler alterado. A suíte backend anterior61/647 continua histórica, sem necessidade de reexecução para esta mudança de frontend.

## Ver e continuar

Iniciar o ambiente habitual conforme README. Visitante abre a raiz; para conferir a apresentação mantendo a conta conectada, usar `/apresentacao`. Ctrl+F5 ou reabrir o PWA online para receber o build. Os processos isolados de QA não são o ambiente de uso. Não executar migrate:fresh ou substituir arquivos privados por causa desta página.

PDF acadêmico anterior permanece preservado e não inclui automaticamente esta landing. Contrato atual em SPEC e estado em TASKS/CONTINUACAO/MEMORY. Fontes na branch dedicada [feature/landing-page](https://github.com/guibirlem/motocare/tree/feature/landing-page), baseada na revisão d27002f; master, T3 e branch de complementos preservadas, sem merge automático.
