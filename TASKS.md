# Tarefas - T1/T2/T3

## Botões verdes no padrão do site — 23/10/2026

- [x] Configurar reutiliza button/button-lime, verde preenchido e cantos retos, preservando tamanho mínimo44px e adaptação mobile. Build aprovado; Chrome isolado/API simulada320/390/768/1440px, claro/escuro: cores iguais ao botão padrão, raio0, sem cortes e abertura da configuração de Seguro confirmados. Sem escrita na API/dados ou nova execução das suítes; demais aceites anteriores permanecem históricos.

## Painel completo no estilo da referência — 23/10/2026

- [x] Alertas agora reúne ativos e sugestões no RemindersPanel existente; Dashboard preservado. Lista vertical de sugestões, relógio/acentos roxos com contraste noturno, ícones laranja/marrom/vermelho e botões Configurar de44px à direita (abaixo do texto em telas muito estreitas). Pneus dianteiro/traseiro continuam independentes.
- [x] Ver lembrete foca os controles existentes também dentro da própria tela Alertas; removido Ver todos redundante nessa tela. Configuração/CRUD/90%/datas/scheduler/pendentes/concluídos preservados, sem duplicar registros ou alterar schema.
- [x]55 testes frontend/build e Chrome/SQLite320/390/768/1024/1440px/dois temas aprovados: configurar/salvar/persistir/editar/concluir/excluir, foco/atalhos, abastecimento230km/56dias, lista vertical/botões/tema,503/offline. Capturas inspecionadas; fixtures removidas e QA encerrado. [Evidências](VALIDACAO_LEMBRETES.md). Sem dados reais/segredos/envios push/e-mail ou novo aceite Safari físico.

## Alinhamento da barra de Gastos — 23/10/2026

- [x] Corrigir desalinhamento reproduzido: botões tinham35,5px, mês39,5px e select35px, centralizados com rótulos. CSS escopado em RecordsView alinha pela base e normaliza controles em44px; campos antes de Todo o histórico e quebra em grid em telas menores. Sem alterar eventos, filtros, CRUD ou estilos globais.
- [x] Build e55 testes frontend aprovados; Chrome/SQLite320/390/600/768/1024/1100/1280/1440/1920px nos dois temas, geometria/sem cortes, mês/categoria/histórico/atualizar/abrir-cancelar formulário aprovados. Manutenções compartilhada conferida em320/768/1440px. Capturas desktop/mobile inspecionadas; fixtures/sessões removidas. Sem backend/schema/dados reais/segredos alterados ou novo teste físico Safari.

## Logo na aba do navegador — 23/10/2026

- [x] Usar a logo PNG192 existente como favicon e apple-touch-icon, preservando manifest, ícones PWA e cache público atuais. Build aprovado; Chrome isolado confirmou PNG200/192x192 nas páginas inicial e login e presença no precache. Sem mudança de lógica, dados ou credenciais.

## Correção do botão Entrar — 23/10/2026

- [x] Reproduzir navegação ignorada de `/` para o alias `/dashboard`; separar registros mantendo HomeView, URLs e login/cadastro existentes. Preservar destaque/acessibilidade do menu Dashboard.
- [x] 55 testes frontend/build e Chrome local390/1440px aprovados; regressão Chrome/SQLite320–1920px, cadastro/sessão/logout/rotas/temas/503/offline aprovada, fixtures removidas. [Evidências](VALIDACAO_ACESSO.md). Sem backend/schema/dados reais/segredos modificados.

## Histórico em linha do tempo — 23/10/2026

- [x] Trilha/ícones/valores/datas/detalhes e resumos por mês no Dashboard, Gastos e Manutenções, com temas/mobile.
- [x] Preservar tabela por alternância, filtros, edição/exclusão, formulários/favoritos e regras; foco ao editar pelo histórico.
- [x] Associar consumo pelo ID do abastecimento final, sem médias falsas; totais em centavos/pendentes separados/média ponderada/comparação válida/Mostrar mais sem truncar totais.
- [x]82backend/878 verificações,55frontend/build, Pint e Chrome/SQLite320–1440px/temas/CRUD/carga lenta/503/offline aprovados. [Evidências](VALIDACAO_HISTORICO.md). Sem schema/dados reais/configuração privada/push/SMTP alterados ou novo aceite Safari físico.

## Lembretes inspirados na referência — 23/10/2026

- [x] Painel responsivo de lembretes/ativos/prazos/status e Sugeridos/Configurar no Dashboard, com temas/identidade existentes.
- [x] Atalhos para formulário/CRUD e foco por registro; sugestões reconhecem categorias ativas e voltam após conclusão, sem renovação automática.
- [x] Abastecimento/seguro/IPVA/licenciamento no contrato de alertas; últimos três por data, scheduler existente e nenhum schema/registro financeiro automático.
- [x]81backend/867 verificações,49frontend/build, Pint e Chrome/SQLite320–1440px/temas/CRUD/persistência/503/offline/botões aprovados. [Evidências](VALIDACAO_LEMBRETES.md). Sem push real/SMTP externo/novo aceite Safari físico.

## Recuperação de senha — 23/10/2026

- [x] Preparar SMTP Gmail/587 e remetente no `.env` local por indicação do usuário, preservando senhas/chaves e mantendo envio desativado até credencial válida.
- [ ] Usuário ativar verificação em duas etapas, gerar senha de app, inserir somente no `.env` e trocar MAIL_MAILER para smtp; reiniciar API e validar recebimento. Não solicitar segredo pelo chat.

- [x] Esqueci minha senha no login, solicitação e nova senha com identidade/temas/mobile existentes.
- [x] API JSON/CSRF, resposta genérica, limites por IP/e-mail, SMTP seguro e origem confiável do link; log mailer bloqueado.
- [x] Token hash/60min/uso único, transação e bloqueio, revogação de sessões/push, invalidação por troca de senha/e-mail e cadastro de endereço reutilizado.
- [x]77 backend/771 verificações,43 frontend/build, Pint/Composer e fluxo Chrome/SQLite/SMTP local descartável aprovados. Sem dados reais/schema/segredos modificados. [Evidências e configuração](RECUPERACAO_SENHA.md).
- [ ] Configurar provedor SMTP/remetente/origem e confirmar recebimento em caixa postal real. Usuário pediu preparar integração e informou ainda não possuir SMTP; não marcar entrega externa como testada.

## Landing Page gratuita — 23/10/2026

- [x] Referência Drivvo consultada e adaptada à identidade/recursos reais do MotoCare, sem preços/planos, avaliações ou depoimentos inventados.
- [x] Navbar/âncoras, hero com prévias próprias, funcionalidades, diferenciais de corrente/reserva, relatórios, três benefícios, chamada gratuita e footer.
- [x] Integrar `/apresentacao`, raiz pública para visitantes e CTAs ao login/cadastro/dashboard existentes; preservar raiz autenticada e deep links.
- [x]40 testes frontend/build, responsividade320–1920px/temas, CTAs com cadastro real, offline/reconexão, API indisponível e regressão T3 aprovados em SQLite isolado. Sem backend/schema/dados reais alterados. [Evidências](VALIDACAO_LANDING.md).
- [x] Fontes em feature/landing-page, com branches anteriores preservadas. Aceite novo no iPhone físico não foi inferido.

## Formatação do frontend — 23/10/2026

- [x] Corrigir faixa da Visão Geral/Atualizar e posicionar indicadores no início; alinhar Moto selecionada e ações.
- [x] Complementar cabeçalho, rodapé navegável, menu mobile, espaçamentos/cards/quebras de texto, preservando identidade preto/verde e temas.
- [x] Conferir espera/falha/recuperação do painel; 38 testes frontend/build e Chrome1920/1440/768/390/320px aprovados em SQLite isolado. Sem mudança de negócio/schema/dados reais. [Relatório](REVISAO_FRONTEND.md).

## Complementos responsivos e de motocicletas — 23/10/2026

- [x] Mapear a base e preservar T1/T2/T3, rotas/categorias/tema/contratos anteriores. Detalhes em [VALIDACAO_COMPLEMENTOS.md](VALIDACAO_COMPLEMENTOS.md).
- [x] Migration aditiva com campos opcionais, SPEC/DER e MySQL atualizados; hashes dos campos/registros originais preservados, sem backfill.
- [x] Favoritos por conta/combustível, memória de preços, cálculo cruzado, KM monotônico e consumo ponderado com parciais/lacunas tratados.
- [x] Painel com indicadores, feed/filtros, gráficos ao lado no desktop e botão flutuante de registro rápido.
- [x] Serviços por eixo/corrente, natureza preventiva/corretiva, contador desde a troca e chuva/lavagem sem duplicação. Alertas duplos/scheduler preservados.
- [x] Parcial/reserva estimada, categorias financeiras e séries de consumo/preço nos relatórios; CSV/impressão mantidos.
- [x] Backend61 testes/647 verificações, frontend38/build e Chrome/API/MySQL, regressão T3 e fluxos novos1600/390px/temas. Fixtures próprias removidas; sem push real/scheduler contínuo.
- [x] Pint/Composer validate, auditoria de177 arquivos sem segredos e fontes em branch dedicada feature/complementos-motocicletas; master/T3 anteriores preservadas.
- [ ] Aceite complementar desta extensão no iPhone/Safari físico. Mobile automatizado usa Chromium.

PDF/ZIP acadêmicos da T3 descritos abaixo antecedem estes complementos e permanecem históricos.

## Acesso móvel da T3 - 23/10/2026

Endereço temporário verificado: **https://success-browsers-showtimes-leg.trycloudflare.com**. Túnel anterior expirou; somente processos móveis identificados reiniciados. API móvel PID 1764, preview PID 11000, túnel PID 18324; estado em tmp/mobile-access.json. APP_URL/WEBPUSH_SUBJECT e FEATURE_FINANCEIRO=true definidos no processo, com debug desligado e cookies seguros. .env e chaves preservados.

HTTPS: HTML/build T3, manifest e CSRF 200; cookies Secure/HttpOnly/no-store, features anônimo 401 e .env 404. Smoke autenticado com conta descartável confirmou financeiro=true, gasto/relatório 1,52, privacidade, CSRF 419 e logout 401; conta/sessões removidas. Sem novo envio push/scheduler. No iPhone, abrir a nova origem no Safari; instalação/notificações anteriores permanecem vinculadas ao hostname antigo. Aceite visual da T3 recebido em 23/10: usuário confirmou “Abrem e estão normais” para Gastos/Novo gasto e Relatórios no iPhone/Safari. Exportações no aparelho e complementos posteriores não estão cobertos por essa resposta.

## T3 integrada - 23/10/2026

Solicitação de iniciar T3 substitui o adiamento anterior. Escopo adotado: gastos, abastecimentos, relatórios, CSV e impressão/Salvar PDF. Registros de 22/10 abaixo são históricos.

- [x] T3.0: base reexecutada 45 testes backend / 391 verificações e 28 frontend; todas as migrations aplicadas. Branch `feature/t3-financeiro` a partir de 5cdce58; sem mudança de schema.
- [x] T3.1: API de despesas/regras financeiras revisada; regressões de arredondamento/tolerância, limites, opcionais, trocas de tipo, propriedade, filtros e CSRF. Relatório rejeita motocicleta_id=0, negativos e frações em vez de interpretar zero como todas as motos.
- [x] T3.2: menu/rotas Gastos e Relatórios integrados. Gastos com filtro de mês/tipo, histórico completo, formulário, cálculo automático, CRUD real e totais do filtro. Limpar resultados antigos antes de nova consulta; moto selecionada isola registros.
- [x] T3.3: relatórios por período inclusivo e moto/todas; meses vazios, totais por combustível/reparos/manutenções concluídas, CSV pt-BR protegido contra fórmulas e impressão/PDF. Filtros alterados invalidam relatório anterior; controles bloqueados durante consulta.
- [x] T3.4: FEATURE_FINANCEIRO=true como padrão e no exemplo. GET autenticado /api/features sincroniza navegação com API; false mantém financeiro 404 e menu/rotas indisponíveis sem quebrar T1/T2. Configuração carregada antes de expor usuário no cliente e limpa ao sair.
- [x] T3.5: 54 testes backend / 486 verificações e 34 frontend aprovados; build, Pint alterados e Composer validate. Chrome headless isolado + HTTP/MySQL aprovou CRUD, duas motos/contas, CSRF 419, isolamento 404, validação 422, logout 401, CSV e impressão. Telas1440px/390px, formulários/tabelas e tema conferidos; fixtures e sessões próprias removidas.
- [x] T3.6: documentação/retomada/contratos atualizados, PDF acadêmico de dez páginas renderizado e conferido, manifesto/pacote/checksum e varredura sem segredos; entrega em `feature/t3-financeiro`, preservando master.
- [x] Aceite visual da T3 no iPhone/Safari real: em23/10, usuário confirmou “Abrem e estão normais” para Gastos/Novo gasto e Relatórios. Não inclui CSV/impressão no aparelho ou complementos posteriores; nenhum novo push confirmado por esta resposta.

Evidências e reprodução em [VALIDACAO_T3.md](VALIDACAO_T3.md). Sem novos dados estruturais/migrations, sem mudanças em .env/APP_KEY/VAPID e sem disparar notificações.

## Acesso móvel reaberto - 22/10/2026

- [x] Novo túnel autorizado pelo usuário e validado: https://own-deeper-just-photo.trycloudflare.com.
- [x] Página/assets/PWA e CSRF 200; cookies Secure/HttpOnly/no-store; acesso anônimo privado 401 e caminhos de segredos 404. Servidores locais8000/5173 preservados.
- [x] Estado local/roteiro móvel atualizados; apenas processos móveis reiniciados, com novo APP_URL/WEBPUSH_SUBJECT. Sem alteração de código/schema/.env/chaves, sem scheduler contínuo/push adicional ou novo aceite manual. A instalação antiga do iPhone usa a origem anterior; abrir/adicionar o novo endereço para testar notificações nesta origem.


## PDF consolidado após a publicação - 22/10/2026

- [x] Conferir master pelo GitHub: commit funcional 5d554ed, mensagem `update task1 e task2 funcionando`.
- [x] Gerar nova edição de nove páginas com referência clicável ao commit, ata da publicação e detalhamento de segurança/continuidade, além dos quatro artefatos solicitados.
- [x] Renderizar e inspecionar as nove páginas; verificar texto, quatro links, nove marcadores e paginação. Pacote/checksum locais atualizados; sem mudança funcional ou nova execução das suítes.


## Publicação e entrega consolidada - 22/10/2026

- [x] Fontes T1/T2, revisão de negócio/segurança, documentos de entrega/retomada e PDF atual reunidos para a atualização de `master` em [guibirlem/motocare](https://github.com/guibirlem/motocare).
- [x] Mensagem solicitada pelo usuário: `update task1 e task2 funcionando`; histórico anterior preservado, sem force push. O SHA desta edição deve ser consultado no [histórico](https://github.com/guibirlem/motocare/commits/master/).
- [x] PDF atualizado para nove páginas, renderizadas e conferidas; texto, links e marcadores verificados. Inclui arquitetura, DER com km_base, backlog, atas e evidências atuais.
- [x] Auditoria dos arquivos elegíveis sem credenciais/chaves privadas; dependências, caches, logs e builds excluídos. Pacote local e checksum recompostos.
- Esta etapa publica resultados já validados: 45 testes backend / 391 verificações e 28 frontend/build. Não houve mudança de aplicação, schema, configuração privada ou novo envio push nesta publicação. T3 continua desativada.

## Revisão de negócio e segurança - 22/10/2026

- [x] Regra escolhida: manutenção concluída atualiza KM para maior, transação/bloqueio, sem regressão por histórico/pendência/edição/exclusão; concluído futuro rejeitado e UI sincronizada.
- [x] Reconciliações autorizadas: Kawasaki Ninja 66.777→66.779 km e Moto Duas rodas 0→122 km; banco sem divergências de KM/datas/KM ausente após ajuste.
- [x] Regra escolhida: 90% do intervalo entre referência e próximo limite; campo km_base, migration aditiva aplicada, DER/SPEC, formulário/progresso/scheduler e backfill conservador dos alertas antigos atualizados. Fronteira 71.278/71.279 e ausência de repetição testadas; zero referências pendentes inválidas no banco.
- [x] Corrigir respostas tardias de sessão/garagem, senhas truncadas/NUL, formato de senha atual, normalização de placa, exposição de erros e limitação de tentativas em perfil/senha. Opcionais do financeiro preservado ajustados sem ativá-lo.
- [x] Atualizar Vite/plugin/transitivas, auditar dependências (npm/Composer sem avisos conhecidos), bloquear caminhos privados e aplicar cabeçalhos de segurança; checar fontes/build sem APP_KEY/chave privada VAPID ou padrões de credenciais.
- [x] 45 testes backend / 391 verificações, 28 frontend, build, Pint alterados e Composer validate; smoke HTTP/MySQL e HTTPS, limpeza das fixtures e relatório [REVISAO_LOGICA_SEGURANCA.md](REVISAO_LOGICA_SEGURANCA.md). Pint global: apenas formatação legada de quatro migrations não alteradas.
- [x] Atualizar continuidade/memória/contratos/DER/manifesto e pacote/checksum. Sem novo envio push/scheduler contínuo, sem rotação de chaves ou publicação remota. T3 segue desativada.

## Preparação de continuidade - 22/10/2026

- [x] Inventário de código financeiro, rotas, feature, migrations e testes conferido por leitura.
- [x] Roteiro [RETOMADA_T3.md](RETOMADA_T3.md) com ordem de retomada, arquivos e critérios de aceite.
- [x] CONTINUACAO, ENTREGA, README, SPEC e MEMORY alinhados; pacote de continuidade com manifesto/checksum preparado.
- A preparação é documental. T3 continua desativada; testes da aplicação não foram reexecutados nesta sessão.

## Documento único de entrega - 22/10/2026

- [x] PDF consolidado em `output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf`: links Git/histórico, arquitetura, DER, backlog e atas retrospectivas baseadas nos registros.
- [x] Nove páginas da edição atual renderizadas e conferidas visualmente; extração de texto, links e marcadores verificados. Não houve nova execução das suítes da aplicação.
- [x] Atualização GitHub desta edição com a mensagem `update task1 e task2 funcionando`, sobre a base `7f5b9cb` de 08/10; consulta prévia e histórico preservados. Notificações T1 e revisão mobile/PWA já confirmadas em 22/10.

Estado funcional validado em 19/10/2026; documentação atualizada em 22/10/2026. T3/financeiro/relatórios adiados. Por decisão do usuário, Web Push padrão substitui Firebase.

## T1 — Alertas

- [x] 22/10: scheduler real no iPhone em segundo plano confirmado pelo usuário às 15:47:51; 899 km pendente, 900/1.000 km notificado/média, repetição e verificação zero envios/zero falhas. Usado SQLite descartável com migrations existentes/transporte real para preservar alerta elegível no MySQL; cenário removido e dados originais conferidos intactos.
- [x] 22/10: recebimento real confirmado pelo usuário em primeiro plano e com Chrome minimizado (teste e alerta de óleo do scheduler às 14:59).
- [x] 22/10: HTTP 429 em português com Retry-After e botão com contagem regressiva; 20 testes frontend/build e 8 testes WebPush/74 verificações aprovados. Backend completo anterior: 29 testes/246 verificações com OPENSSL_CONF.
- [x] 22/10: execução local com acesso de saída ao serviço de push; fixtures removidas.

- [x] CRUD de alertas próprios de óleo/revisão por limite absoluto de km ou data.
- [x] Scheduler por minuto, fronteira inclusiva de 90%, locks e tentativas posteriores em falha/ausência de inscrição.
- [x] Conclusão explícita; sem reativar alertas notificados/concluídos.
- [x] Cliente Push API/Service Worker e envio VAPID criptografado com minishlink/web-push; SDK/configuração Firebase removidos.
- [x] Chaves geradas localmente por php artisan push:configurar, sem conta externa; configuração pronta nesta máquina.
- [x] Inscrição criptografada, única entre contas, oculta nas respostas; logout/desativação, expiração e troca de sessão tratados.
- [x] Teste autenticado, com CSRF e limite de três tentativas/minuto, restrito à inscrição atual e sem alterar alertas.
- [x] 22/10: teste real com página aberta confirmado pelo usuário na central de notificações do Windows; processamento também observado na mensagem do Service Worker na interface.
- [x] 22/10: cenário real isolado: 899/1.000 km pendente/zero envios; 900 km via `schedule:run` passou a notificado/média; repetição zero envios. Fixtures removidas.
- [x] Usuário confirmou “Sim, chegou esse alerta de óleo” com Chrome minimizado após schedule:run das 14:59. Confirmação do cenário em segundo plano concluída; banner flutuante e Chrome encerrado não foram validados separadamente.

## T2 — Histórico

- [x] CRUD de óleo, kit relação, pneus e freios, com validação de campos/status/data/km/valor.
- [x] Isolamento por usuário/moto; valor retornado como string decimal.
- [x] Listagem/filtros/totais de concluídos, estados de carregamento/vazio/erro e confirmação de exclusão.
- [x] PUT substitui opcionais omitidos; manutenção concluída pode avançar odômetro sem reduzi-lo. Alertas são concluídos/reprogramados explicitamente.
- [x] CRUD completo dos quatro serviços reconferido por HTTP no MySQL local; fixtures removidas.

## Suporte

- [x] 22/10: modo noturno/claro em computador e celular, inclusive login/carregamento/offline; seletor no cabeçalho, compacto/acessível no mobile, preferência local restaurada e padrão do sistema quando não escolhida. Paleta de formulários/cards/tabelas/avisos e impressão clara ajustadas.
- [x] 22 testes frontend/build aprovados e verificação pontual de persistência, alternância, prioridade sobre sistema e armazenamento bloqueado. Build novo servido no local e no HTTPS autorizado; página/JS/CSS/precache/SW HTTP 200.
- [x] Aceite visual específico do modo noturno: após orientação de atualizar/reabrir, usuário confirmou “Sim, tema escuro funcionando”. Confirmação manual do usuário; sem alegar inspeção visual automática.

- [x] 22/10: corrigir mensagem genérica offline no iPhone/navegador: tela global antes do login, orientação de conexão, bloqueio de novas requisições offline e tentativa de recarga ao evento online. Sem gravações offline/replay/cache de dados pessoais.
- [x] 22 testes frontend e build aprovados; JS/precache novos publicados no preview local e no túnel HTTPS existente, com HTTP 200.
- [x] Aceite manual no iPhone: usuário confirmou “Modo offline”, orientação e botão “Verificar conexão”; ao reconectar, confirmou “Sim, voltou a carregar”.
- Reteste concluído: após a frase antiga na primeira repetição, fechar completamente pela multitarefa e reabrir online permitiu carregar a versão atual. Build index-uO4CJ1w0.js conferido por HTTPS e comportamento confirmado no aparelho.

- [x] 22/10: preparar acesso móvel separado, sem debug, cookies Secure/HttpOnly e allowlist de hostname; validações HTTP locais aprovadas. Roteiro em ACESSO_CELULAR.md; cloudflared oficial/checksum conferido.
- [x] Aprovação explícita recebida em 22/10 para Cloudflare/trycloudflare.com; túnel temporário aberto. URL atual em ACESSO_CELULAR.md; recusa inicial resolvida pela autorização do usuário.
- [x] HTTPS externo: página/assets/manifest/workers/ícones/CSRF 200, cookies Secure/HttpOnly/no-store; smoke de cadastro/login/sessão/logout, privacidade e CSRF 419 aprovado. Conta descartável removida.
- [x] Revisão geral de apresentação/formulários no iPhone: usuário conferiu Dashboard, Manutenções, Garagem, Alertas e Configurações e confirmou todas as telas/formulários normais. Abertura pelo app instalado, push pelo botão e ciclo offline/reconexão confirmados.
- [x] Alerta do scheduler no iPhone em segundo plano recebido e confirmado pelo usuário; detalhes do isolamento e evidências em CONTINUACAO. Sem scheduler contínuo iniciado.
- [x] 22/10: teste de notificação real no iPhone confirmado, inclusive pelo botão do app, após corrigir WEBPUSH_SUBJECT do processo móvel (403 BadJwtToken → 201). Chaves e inscrição preservadas.
- Scheduler móvel precisa herdar o mesmo contato VAPID HTTPS. Aceites de push pelo botão e offline não substituem o teste específico desse scheduler no iPhone.

- [x] 22/10: recuperar execução local após frontend/API parados; novo build e HTTP 200 de página, JS, CSS e API via proxy. Conferência visual dessa recuperação não disponível (native pipe indisponível).

- [x] Conta/perfil/senha, sessões compartilhadas/CSRF, garagem/odômetro e Dashboard usando T1/T2 diretamente.
- [x] PWA, cache exclusivo de shell público e execução npm start reproduzível.
- [x] Migration Web Push aplicada; SPEC/DER/README/continuação e exemplos atualizados; chaves privadas fora do pacote.
- [x] Revisão mobile/PWA no iPhone concluída no cenário conferido: app instalado, telas/formulários, offline/reconexão e notificações pelo botão/scheduler. Conferência visual pelo usuário; automação visual interrompida por impossibilidade de identificar URL do Chrome com segurança.

## Adiado

- [ ] T3: despesas/abastecimentos, financeiro, relatórios/exportações.
- Código/schema históricos preservados; sem navegação ativa e APIs retornando 404 por padrão (FEATURE_FINANCEIRO=false). Não reativar durante correções de T1/T2 nem reverter migrations para ocultar telas.

### Backlog de retomada T3 (a executar futuramente)

- [ ] T3.0: incorporar fontes atuais, validar ambiente/migrations/testes e definir alcance de relatórios/exportações.
- [ ] T3.1: revisar API preservada, validações financeiras, centavos/arredondamento, opcionais em PUT, filtros e isolamento; corrigir lacunas comprovadas.
- [ ] T3.2: integrar Gastos na interface (rotas/menu/estado real), CRUD combustível/reparo, troca de moto, filtros e estados de tela; preservar RecordsView da T2.
- [ ] T3.3: se incluídos, integrar relatórios, resumo mensal, CSV e impressão/PDF e conferir totais/datas/acesso.
- [ ] T3.4: definir ativação e navegação coerentes com a feature, sem quebrar Dashboard/T1/T2 quando desligada.
- [ ] T3.5: suítes, build, smoke HTTP/MySQL, revisão desktop/móvel e exportações; registrar evidências atuais e limpar fixtures descartáveis.
- [ ] T3.6: atualizar documentação, pacote/checksum e entrega acadêmica conforme escopo; publicar fontes com histórico preservado.

Implementação antiga e testes preservados não encerram este checklist de revalidação/integração. Roteiro detalhado: [RETOMADA_T3.md](RETOMADA_T3.md).

## Evidências

29 testes backend/246 verificações, 19 testes frontend, build, Pint e Composer aprovados. Smoke autenticado no MySQL confirmou Web Push configurado, inscrição/remoção sem expor dados privados, CRUD de quatro serviços, alerta/logout e financeiro 404. Não houve envio real ao endpoint sintético. Em 22/10 houve envio ao navegador real e confirmação do usuário em primeiro plano, segundo plano e pelo scheduler. Não confundir com os mocks históricos.

Pacote atual para continuidade: entrega/MotoCare-Retomada-T3-2026-10-22.zip. O pacote de 19/10 permanece histórico. Publicação desta edição pelo Git instalado; o conector havia retornado 403, contornado por autenticação Git disponível. Ver [ENTREGA.md](ENTREGA.md).
