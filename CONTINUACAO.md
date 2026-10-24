# Continuação do MotoCare - T1/T2/T3

## Sugeridos desaparecem após configurar — 23/10/2026

Pedido posterior do usuário substitui o retorno automático das sugestões após conclusão: suggestedReminders agora considera todos os tipos salvos da moto, incluindo concluídos. Oculta cada item e a seção quando não resta sugestão, mantendo ativos/CRUD/Novo alerta. Apenas excluir o último registro de um tipo o libera novamente. Sem localStorage, migration ou mudança da API/banco. Texto de orientação e estado vazio ajustados; sugestões antigas não aparecem durante carregamento.56 testes frontend/build e Chrome/SQLite isolado/cancelar/erro422/salvar/recarga/concluir/troca entre duas motos/oito tipos/configurar novamente pelo Novo alerta aprovados; fixtures removidas e QA8010/5190 encerrado. Preview5173 atualizado. [Evidências](VALIDACAO_LEMBRETES.md); commit/pacote incrementais em feature/correcao-acesso. Sem dados reais/configuração privada/push/SMTP alterados.

## Configurar verde e quadrado — 23/10/2026

Por preferência explícita do usuário, botões Configurar agora usam as classes button/button-lime existentes, preenchimento verde e raio0. Mantidos controles44px, comportamento, cards e demais elementos. Build local5173 atualizado e Chrome isolado com API simulada320/390/768/1440px/dois temas confirmou cores padrão/cantos retos/sem cortes e abertura do formulário. Sem contas/dados/API reais alterados; suítes anteriores não reexecutadas nesta mudança visual. Publicação e pacote incrementais em feature/correcao-acesso.

## Lembretes completos e visual da referência — 23/10/2026

Usuário informou que o painel da imagem ainda faltava. Base existente estava em grade e, em Alertas, só exibia sugestões. Complementado o mesmo componente: ativos+sugestões também em `/alertas`, lista vertical, ícones por categoria e acento roxo adaptado ao modo noturno. Dashboard preservado; pneus dianteiro/traseiro e regras reais mantidos, sem dados demonstrativos na interface. Ver lembrete foca os controles detalhados na própria tela; Ver todos aparece só no Dashboard.55 testes frontend/build e Chrome/SQLite320–1440px/temas/configuração/persistência/CRUD/foco/503/offline aprovados; capturas inspecionadas. [Detalhes](VALIDACAO_LEMBRETES.md). Sem backend/schema/dados reais/.env/chaves/push/SMTP alterados. Preview5173 atualizado; fixtures removidas/QA8010/5190 encerrado. Commit incremental e pacote correspondente na branch feature/correcao-acesso.

## Barra de Gastos alinhada — 23/10/2026

RecordsView recebe classe records-toolbar e CSS escopado: alinhamento pela base, controles44px, campos agrupados antes de Todo o histórico e grid responsivo (mobile com campos em linhas completas). Eventos e regras existentes preservados, sem mudança global de CSS/API/schema. Build de uso5173 atualizado;55 testes frontend e Chrome/SQLite320–1920px/claro-escuro/geometria/filtros/mês/histórico/atualizar/formulário aprovados. Tela compartilhada de Manutenções conferida em320/768/1440px. Capturas em tmp/expenses-align-*, fixtures removidas e QA8010/5190 encerrado; serviços de uso preservados. Publicação incremental em feature/correcao-acesso; pacote correspondente atualizado. Sem novo teste de backend, push, e-mail ou Safari físico.

## Logo na aba — 23/10/2026

`frontend/index.html` referencia a logo existente `/icons/icon-192.png` como favicon e apple-touch-icon. Sem novo asset/dependência, alteração de manifest/worker ou regras/dados. Build local5173 atualizado; Chrome isolado conferiu imagem200/192x192 na raiz e no login, com ícone já no precache. Recarregar a aba se ainda exibir o ícone antigo. Suítes anteriores não reexecutadas nesta mudança de metadados. Publicação permanece em `feature/correcao-acesso`.

## Acesso pela página inicial corrigido — 23/10/2026

Entrar em `/` agora abre `/dashboard`: registros distintos no Vue Router eliminam a navegação duplicada do antigo alias, preservando HomeView, URLs, AccountGate e raiz autenticada. Destaque/aria-current do menu Dashboard preservados. Build de uso5173 atualizado; aba antiga pode exigir Ctrl+F5. 55 testes frontend/build e regressão Chrome local/SQLite aprovados; [evidências](VALIDACAO_ACESSO.md). Sem backend/schema/dados reais/configuração privada alterados. QA8010/5190 encerrado após testes, fixtures removidas; serviços de uso preservados. Fontes em `feature/correcao-acesso`, sobre427aada; branches e PDF anteriores preservados.

## Histórico no estilo da referência — 23/10/2026

HistoryTimeline reutilizável no Dashboard/Gastos/Manutenções, com trilha/ícones/valores/datas, detalhes reais e resumos mensais. Tabela anterior acessível por botão; filtros/CRUD/favoritos/formulários/rotas preservados. Consumo por ciclo existente, associado ao novo campo opcional `ciclos[].abastecimento_id`; sem schema/migration/dependências novas. Pendentes fora do total, média ponderada, comparação só com mês anterior válido e totais completos mesmo com Mostrar mais. Ler [VALIDACAO_HISTORICO.md](VALIDACAO_HISTORICO.md) e SPEC.

82backend/878 verificações,55frontend/build, Pint e Chrome/SQLite320–1440px/temas/CRUD/tabela/filtros/mesmo dia/carga lenta/503/offline aprovados. Dashboard não mostra resumo parcial antes das fontes completas; falha de estatística em Gastos preserva registros sem inventar média. Fixtures removidas e QA8010/5190 encerrado; local de uso preservado, sem dados reais/.env/chaves/push/Gmail alterados. Branch atual `feature/historico-timeline` sobre728fd50, pacote próprio, branches/PDF anteriores históricos. SMTP continua pendente de senha de app local.

## Painel de lembretes — 23/10/2026

Dashboard com Lembretes/ativos/prazos e Sugeridos/Configurar; sugestões também em Alertas, reutilizando formulário/CRUD/CSRF. Tipos novos abastecimento, seguro, IPVA e licenciamento; últimos três apenas por data. Sem schema/migration/dependências novas nem registros automáticos de pagamento/manutenção/abastecimento/novo ciclo. T1/scheduler/90%/push e demais recursos preservados. Ler [VALIDACAO_LEMBRETES.md](VALIDACAO_LEMBRETES.md) e SPEC.

81backend/867 verificações,49frontend/build, Pint e Chrome320–1440px/temas com SQLite isolado aprovados; CRUD/persistência/links/foco/sugestões/503/offline/botões conferidos. Helper P-256 de testes corrigido para zeros iniciais, sem mudar produção. Fixtures removidas e QA8010/5190 encerrado ao concluir; sem dados reais/.env/chaves/push/e-mails alterados. Local8000/5173/MySQL3306 disponíveis na última checagem. Sem novo túnel/iPhone físico. Branch atual `feature/lembretes` sobre89cffc1, pacote próprio; PDF anterior histórico. Pendência SMTP Gmail separada.

## Recuperação de senha integrada — 23/10/2026

Preparação Gmail posterior: usuário forneceu o remetente; host587/SMTP/usuário/remetente preparados somente no `.env` privado. MAIL_PASSWORD continua vazio e MAIL_MAILER=log até o usuário gerar e inserir localmente uma senha de app do Google e ativar smtp. Nenhum envio externo feito. Não publicar o endereço pessoal nem pedir a senha pelo chat. Instruções em RECUPERACAO_SENHA; depois conferir config/reiniciar API e testar recebimento real.

Ler [RECUPERACAO_SENHA.md](RECUPERACAO_SENHA.md). Link no login, `/esqueci-senha` e `/redefinir-senha`, APIs JSON com CSRF/rate limits e broker existente (hash, 60 minutos, uso único). Sucesso revoga sessões/push da conta; alterações de senha/e-mail e reuso de endereço invalidam links anteriores. Sem schema/migration/dependência nova; negócio/rotas/T1/T2/T3/landing preservados.

77 testes backend/771 verificações,43 frontend/build, Pint/Composer e Chrome320/390/768/1440px/temas com SQLite/SMTP local isolados aprovados; fixtures removidas. Nenhum e-mail externo/push real nem dados MySQL alterados. **Pendente operacional: SMTP real**, conforme usuário informou não possuir provedor. `.env` de uso preservado em mailer log, recusado para recuperação com503 para não expor segredo. Configurar SMTP/remetente e FRONTEND_URL da origem pública quando disponível; não pedir credenciais no chat. PDF anterior histórico. Continuidade na branch `feature/recuperacao-senha`, sobre80b7b7f, preservando as anteriores.

Acesso local: frontend5173 preservado e API8000 iniciada; MySQL do XAMPP ficou indisponível na checagem final, reativar para usar contas. Não iniciar scheduler automaticamente. QA8010/5190 encerrado; sem túnel novo nesta tarefa.

## Landing gratuita integrada — 23/10/2026

Nova apresentação pública em `/apresentacao` e raiz `/` de visitantes. Raiz autenticada continua no dashboard; `/dashboard` e demais rotas permanecem. CTAs usam o AccountGate existente: `/dashboard` para entrar, `/dashboard?cadastro=1` para abrir cadastro, `/relatorios` para relatórios. Conta conectada vai ao painel sem cadastro forçado. Ler [VALIDACAO_LANDING.md](VALIDACAO_LANDING.md) e SPEC antes de mexer nos acessos.

Página responsiva preto/verde/Arial, gratuita sem planos, com navbar/âncoras/tema, hero e mockups próprios ilustrativos, funcionalidades, corrente/chuva, reserva estimada, relatórios, benefícios, CTA final e rodapé. Componentes/CSS escopados, chunk sob demanda, nenhum pacote novo. Backend/schema/dados/.env/chaves/serviços anteriores intactos; nenhuma migration necessária.

40 testes frontend/build aprovados; Chrome320/390/768/1024/1440/1920px/temas, âncoras, cadastro/login reais e regressão T3 aprovados em SQLite isolado, fixtures removidas. Página online funciona mesmo com API503, offline/reconexão e movimento reduzido preservados. Sem novo teste MySQL/iPhone físico/push. Fontes na branch `feature/landing-page` sobre d27002f, preservando branches anteriores. PDF acadêmico anterior não regenerado nesta adição.

## Correção visual do painel e shell — 23/10/2026

Solicitação de formatação atendida: Atualizar compacto ao lado do título, métricas antes dos cuidados, seletor/botões alinhados, cabeçalho/rodapé completos e menu mobile sem altura fixa. Painel conserva indicadores ao atualizar, aguarda suas consultas completas e oferece recuperação em erro. Estilo/temas/rotas/negócio preservados, sem mudança de API/schema/configuração privada. Evidências em [REVISAO_FRONTEND.md](REVISAO_FRONTEND.md).

38 testes frontend/build e Chrome1920/1440/768/390/320px aprovados, claro/noturno, geometria, espera/falha503/recuperação e rodapé/menu. Como XAMPP/servidores de uso estavam desligados, revisão final usou SQLite/portas8010/5190 isolados, sem dados reais. Para uso iniciar XAMPP/API/frontend habituais e recarregar online; nenhum novo aceite Safari/push.

## Aceite visual da T3 no iPhone — 23/10/2026

Em 23/10/2026, usuário respondeu “Abrem e estão normais” à pergunta sobre Gastos (incluindo Novo gasto) e Relatórios no Safari do iPhone, no acesso HTTPS da T3. Aceite visual desses fluxos registrado. A resposta não confirma novos cadastros, exportação/compartilhamento de CSV, impressão no aparelho ou os complementos de corrente, reserva e favoritos adicionados depois da pergunta. Sem nova execução de testes, envio push ou alteração de código/banco.

## Complementos preservando a base — 23/10/2026

Fontes desta extensão na branch dedicada `feature/complementos-motocicletas`, baseada em b3c70e9; master e feature/t3-financeiro preservadas, sem merge automático.

Pedido atual implementado sobre T3 publicada em b3c70e9. Ler [VALIDACAO_COMPLEMENTOS.md](VALIDACAO_COMPLEMENTOS.md), SPEC e DER: favoritos/preços por combustível, consumo cheio/parcial, tanque/reserva, painel/feed/gráficos, FAB, serviços por eixo/corrente, contador e chuva/lavagem. Regras/rotas legadas preservadas, campos novos opcionais e históricos sem preenchimento por suposição.

Migration `2026_10_23_000001_add_motorcycle_insights.php` aplicada no MySQL local. Contagens/hashes de todos os campos originais em usuários/motos/manutenções/despesas/alertas iguais antes/depois. Em outra instalação executar `php artisan migrate`, preservando .env/APP_KEY/VAPID; não usar migrate:fresh/rollback para atualizar.

Backend61/647 e frontend38/build aprovados, Chrome isolado + HTTP/MySQL com contas descartáveis removidas. Cenário novo: consumo30km/L, reserva250/310km, odômetro1350, gasto353,50, contador1, memória de posto e atalho idempotente. Nenhum push real/scheduler contínuo; não usar alertas da conta para demonstração sem cenário controlado. Aceite novo no iPhone/Safari físico pendente.

Atualizar online (Ctrl+F5 ou fechar completamente/reabrir PWA); cadastrar tanque/reserva na Garagem e favoritos/cheios/parciais com KM em Gastos. Média/autonomia exigem dados válidos, sem alterar históricos antigos. Registrar troca do kit/ajustes em Manutenções e programar lubrificação em Alertas. Chuva cria lembrete para hoje, não serviço realizado. Push segue dependente de scheduler/inscrição.

PDF/ZIP acadêmicos anteriores abaixo não incluem esta extensão. Fontes, SPEC/DER e relatório novo são a referência; não reimplementar esses módulos na retomada.

## Acesso móvel da T3 - 23/10/2026

Endereço temporário verificado: **https://success-browsers-showtimes-leg.trycloudflare.com**. Túnel anterior expirou; somente processos móveis identificados reiniciados. API móvel PID 1764, preview PID 11000, túnel PID 18324; estado em tmp/mobile-access.json. APP_URL/WEBPUSH_SUBJECT e FEATURE_FINANCEIRO=true definidos no processo, com debug desligado e cookies seguros. .env e chaves preservados.

HTTPS: HTML/build T3, manifest e CSRF 200; cookies Secure/HttpOnly/no-store, features anônimo 401 e .env 404. Smoke autenticado com conta descartável confirmou financeiro=true, gasto/relatório 1,52, privacidade, CSRF 419 e logout 401; conta/sessões removidas. Sem novo envio push/scheduler. No iPhone, abrir a nova origem no Safari; instalação/notificações anteriores permanecem vinculadas ao hostname antigo. Aceite visual da T3 recebido em 23/10: usuário confirmou “Abrem e estão normais” para Gastos/Novo gasto e Relatórios no iPhone/Safari. Exportações no aparelho e complementos posteriores não estão cobertos por essa resposta.

## T3 integrada e validada - 23/10/2026

A instrução de iniciar T3 substitui o adiamento anterior. Implementados Gastos/Abastecimentos, Relatórios, CSV e impressão/Salvar PDF; código em `feature/t3-financeiro`, baseado em 5cdce58. Não integrar automaticamente em master. Consultar [VALIDACAO_T3.md](VALIDACAO_T3.md), [RETOMADA_T3.md](RETOMADA_T3.md) e [ENTREGA.md](ENTREGA.md). Os registros de 22/10 abaixo são históricos.

Ativação: config/features.php e .env.example usam FEATURE_FINANCEIRO=true. Nenhum .env privado foi editado nesta máquina; quem herdou false em outro ambiente deve habilitar explicitamente, limpar config cache se houver e reiniciar. GET autenticado /api/features retorna somente financeiro:boolean; sessão/carregamento/menu/rotas são coerentes com esse valor. false continua suportado, com APIs financeiras404 e T1/T2 ativos.

Correções da retomada: motocicleta_id=0 não pode selecionar todas as motos; resultados de filtros anteriores não permanecem na tela após falha/alteração; relatório captura período/moto da consulta e desativa controles em voo. CSV usa vírgula decimal, BOM UTF-8, escape de texto e proteção contra fórmulas. Tabelas financeiras rolam em seu contêiner, sem arrastar a página inteira; impressão remove essas larguras mínimas. Garagem/Configurações não remontam por seleção de moto; primeira moto preserva confirmação de cadastro. Ícones decorativos do menu ocultos da leitura acessível.

Validação atual: 54 testes backend / 486 verificações e 34 frontend/build aprovados, Pint nos arquivos alterados e Composer validate. Chrome isolado em 1440x1000/390x844 com MySQL real: cadastro, garagem, combustível87,50 e arredondamento 1,52, trocas de tipo, reparos 0,10+0,20, duas motos, manutenção 100,01 com odômetro 1100 e pendente 500 fora dos totais, relatório 187,81/2,00/189,81 por escopo. CSV baixado e PDF impresso/renderizado conferidos. Isolamento404, CSRF 419, inválidos 422 e logout 401; nenhuma exceção JS no fluxo aprovado. Duas contas/sessões descartáveis removidas por rotina limitada às fixtures; nenhum dado da conta do usuário usado.

Sem nova migration: schema de despesas/descrição existente e aplicado. SPEC/DER atualizados quanto ao estado/contrato, sem alterar T1/T2, chaves, push ou banco existente. Prévia local já serve o build da T3; atualizar com Ctrl+F5 ou fechar completamente/reabrir PWA online. Aceite visual do usuário no iPhone/Safari recebido para Gastos/Novo gasto e Relatórios; não estender a confirmação a exportações ou complementos posteriores. Nenhum push adicional ou scheduler contínuo iniciado.

## Acesso móvel reaberto - 22/10/2026

- [x] Novo túnel autorizado pelo usuário e validado: https://own-deeper-just-photo.trycloudflare.com.
- [x] Página/assets/PWA e CSRF 200; cookies Secure/HttpOnly/no-store; acesso anônimo privado 401 e caminhos de segredos 404. Servidores locais8000/5173 preservados.
- [x] Estado local/roteiro móvel atualizados; apenas processos móveis reiniciados, com novo APP_URL/WEBPUSH_SUBJECT. Sem alteração de código/schema/.env/chaves, sem scheduler contínuo/push adicional ou novo aceite manual. A instalação antiga do iPhone usa a origem anterior; abrir/adicionar o novo endereço para testar notificações nesta origem.


## PDF final após publicação - 22/10/2026

Nova consolidação do [arquivo único](output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf), agora com nove páginas e quatro links. Referência funcional fixa: [5d554ed](https://github.com/guibirlem/motocare/commit/5d554ed7b5ee68c903c86a2bc6b6d062d7e58def), confirmado em master. Inclui ata de publicação, proteções de segurança e orientação de continuidade, além de arquitetura/DER/backlog/atas e aceites já registrados. Todas as nove páginas foram renderizadas e inspecionadas; texto, links, marcadores e paginação conferidos. Substitui PDFs anteriores de seis/oito páginas. Atualização documental; regras/schema/chaves/feature e validações funcionais não mudam.


## Entrega e publicação atualizadas - 22/10/2026

Esta edição consolida os fontes em [guibirlem/motocare](https://github.com/guibirlem/motocare), branch `master`, na atualização com mensagem `update task1 e task2 funcionando`. O commit preserva o histórico anterior; conferir seu SHA no [histórico público](https://github.com/guibirlem/motocare/commits/master/). O erro 403 do conector foi contornado pelo Git instalado, com autenticação de escrita conferida. Não usar a antiga base 7f5b9cb isoladamente para retomar.

O [PDF único de entrega](output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf) foi substituído por edição atual de nove páginas: repositório/commits, arquitetura, DER com km_base, backlog e atas retrospectivas, incluindo os aceites reais e a revisão de negócio/segurança. Todas as páginas foram renderizadas e inspecionadas; texto/links conferidos. Pacote de continuidade local e checksum atualizados. Registros históricos abaixo mantêm o estado observado em cada etapa; menções antigas a PDF histórico/publicação pendente foram superadas por esta edição.

Nenhuma regra ou configuração privada mudou nesta preparação documental/publicação. As validações funcionais vigentes são 45 testes backend / 391 verificações e 28 frontend/build, com limites em REVISAO_LOGICA_SEGURANCA.md. Financeiro/T3 continua false; para retomar, clonar/puxar a versão atual e seguir RETOMADA_T3.md. Não copiar .env, APP_KEY ou VAPID de outra máquina.

## Revisão de negócio e segurança - 22/10/2026

Relatório: [REVISAO_LOGICA_SEGURANCA.md](REVISAO_LOGICA_SEGURANCA.md). Usuário escolheu manutenção concluída avançar automaticamente o odômetro sem nunca reduzir e autorizou reconciliação de Kawasaki Ninja 66.777→66.779 km e Moto Duas rodas 0→122 km. Gravação de manutenção/KM atômica com bloqueio da moto; histórico menor, pendência, edição para KM menor e exclusão não reduzem odômetro. Concluído não aceita data futura; tela usa data de São Paulo. Alertas continuam com conclusão/próximo limite explícitos.

Usuário também escolheu 90% do intervalo, substituindo 90% do odômetro absoluto. Migration aditiva `2026_10_22_000001_add_km_base_to_alertas_table.php` aplicada no MySQL; SPEC/DER atualizados. Referência persistida no alerta e editável enquanto pendente; exemplo base 66.779, limite 71.779, aviso 71.279. Criação sem referência usa KM atual se abaixo do limite, senão zero. Pendentes antigos futuros inicializados pelo odômetro atual; vencidos continuam vencidos; histórico notificado/concluído preservado. UI/progresso/scheduler usam a mesma fórmula inteira. Nada foi reenviado nesta auditoria.

Segurança: respostas antigas descartadas após logout/troca de sessão e refresh fora de ordem; senhas novas limitadas a 72 bytes sem NUL; campos de senha atual com formato inválido tratados como 422; perfil/senha com limites separados de dez tentativas/minuto. Erros da API sanitizados mesmo com debug, com Retry-After preservado; CSRF/isolamento/criptografia push conferidos. Preview com CSP/nosniff/referrer/frame e bloqueio explícito de caminhos privados. Vite atualizado para 6.4.5/plugin Vue 5.2.4 e transitivas corrigidas; npm audit/Composer audit sem avisos conhecidos. APP_KEY/chave privada VAPID/padrões de credenciais não encontrados nos fontes elegíveis/build; .env/chaves mantidos privados, sem rotação. Chave pública VAPID é necessária ao Web Push.

Validações atuais: 45 testes backend / 391 verificações, 28 frontend, build, Pint dos arquivos alterados e Composer validate aprovados. HTTP/MySQL com contas descartáveis aprovou odômetro/histórico/datas/referência de alerta, isolamento, CSRF e logout; fixtures removidas. Banco atual: zero divergências manutenção/odômetro, concluídas futuras/sem KM e referências pendentes inválidas. HTTPS autorizado confirmou assets/PWA 200, acesso anônimo privado 401, caminhos de segredos 404 e cookies móveis Secure/HttpOnly. Previews substituídos pelo Vite corrigido: local PID 18208 e móvel 18524; API/túnel preservados, estado em tmp/mobile-access.json. Pint global aponta só formatação de quatro migrations antigas não alteradas; relatório explicita limite. Aceites visuais/push anteriores não foram repetidos manualmente; nenhum scheduler contínuo iniciado. Fontes consolidados na publicação descrita acima; pacote/checksum atualizados; T3 desativada.

## Modo noturno - 22/10/2026

Adicionado seletor claro/noturno no cabeçalho de computador/celular e antes do login/carregamento/tela offline. No cabeçalho móvel usa ícone com nome acessível; preferência local `motocare-theme` restaurada ao abrir, com preferência do sistema quando não há escolha válida. Armazenamento bloqueado não impede alternar nesta abertura. Tema e meta theme-color aplicados antes de montar o app; paleta cobre fundos, cards, formulários, tabelas, filtros, mensagens e indicadores, preservando texto escuro nas superfícies verdes. Impressão usa paleta clara.

Implementação em frontend/src/services/theme.js e components/ThemeToggle.vue, integrados no main/App e CSS sem alterações de API/schema/.env/chaves. 22 testes frontend e build aprovados; verificação pontual confirmou escolha salva/restaurada, alternância, prioridade sobre sistema e fallback com localStorage bloqueado. Versão nova servida localmente e no HTTPS autorizado (página, JS/CSS, precache e SW HTTP 200); mesmos processos de API/túnel preservados. Após a orientação de atualizar/reabrir, usuário confirmou “Sim, tema escuro funcionando”. Aceite visual específico do novo tema concluído por confirmação manual, somado à revisão mobile/PWA anterior aprovada. Pacote/checksum atualizados; T3 continua desativada.

## Revisão mobile/PWA concluída no iPhone - 22/10/2026

Usuário conferiu Dashboard, Manutenções, Garagem, Alertas, Configurações e formulários no app instalado: “Todas as telas e formulários estão normais”. Abertura pelo ícone, notificação pelo botão e ciclo offline/reconexão já confirmados. Conferência visual do iPhone foi manual pelo usuário; Computer Use foi interrompido porque não conseguiu identificar com segurança a URL do Chrome, sem validação visual automática.

Scheduler real `schedule:run`: 15:47:40 (America/Sao_Paulo), 899/1.000 km permaneceu pendente; 15:47:51, 900 km passou a notificado/média e usuário confirmou recebimento do alerta “MotoCare: Troca de óleo”, da moto “Teste MotoCare iPhone”, com app em segundo plano. Repetição às 15:48:24 preservou notificado; comando de verificação retornou zero envios/zero falhas. Inscrição Apple reativada pelo usuário antes do teste e WEBPUSH_SUBJECT HTTPS definido no processo.

Havia um alerta elegível no MySQL de uso. Para preservá-lo, o teste iPhone usou SQLite descartável com as migrations existentes, inscrição Apple criptografada e transporte push real; não foi um novo teste de persistência no MySQL (já validado na T1/T2). Banco/fixtures isolados removidos; comparação antes/depois confirmou alertas, motos e inscrições originais preservados. Nenhum código de aplicação, schema, .env ou chave alterado; nenhuma suíte completa reexecutada. Validações anteriores: 22 testes frontend/build, backend completo 29/246 e WebPush 8/74 após ajuste 429. T3 continua desativada e nenhum scheduler contínuo foi iniciado. Para avisos diários manter `schedule:work` ativo, com contato VAPID válido.

Revisão mobile/PWA encerrada para o iPhone e os fluxos conferidos, incluindo layout/formulários, offline/reconexão e scheduler em segundo plano. A publicação dos fontes desta edição está descrita no início do documento. Registros abaixo descrevem etapas anteriores e devem ser lidos com este aceite final.

## Mensagem offline corrigida - 22/10/2026

Aceite manual concluído no iPhone: após fechar completamente pela multitarefa e reabrir online, usuário confirmou “Modo offline”, orientação para conectar e botão “Verificar conexão” ao desligar a internet. Ao reconectar, confirmou “Sim, voltou a carregar”. Layout e scheduler também aprovados posteriormente, conforme aceite final acima.

Usuário relatou no iPhone erro genérico da API ao ficar offline. App agora mostra tela “Modo offline” antes de login/área autenticada, orienta reconexão e oferece Verificar conexão. Início offline não tenta iniciar sessão; evento online tenta recarregá-la. Cliente HTTP bloqueia novas requisições quando navigator.onLine=false; falhas de rede sem diagnóstico de offline mostram orientação de conexão, sem mencionar API. Sem fila/reenvio de gravações nem cache de dados pessoais; Service Worker continua apenas com shell público.

22 testes frontend e build aprovados, incluindo bloqueio do transporte de escrita offline/ausência de replay e mensagens distintas de falha de serviço. Versão atual /assets/index-uO4CJ1w0.js e precache conferidos HTTP 200 no HTTPS autorizado; a mensagem antiga da API também foi removida do fallback genérico, com regressão testada. Previews reiniciados; túnel/API com configuração Apple preservados. Estado/PIDs atuais em tmp/mobile-access.json. Usuário ainda viu o texto antigo na primeira repetição; fechar completamente e reabrir o app resolveu a atualização, com aceite manual descrito acima. Nenhuma alteração adicional de código nem nova suíte nesta confirmação.

## Preparação de acesso no iPhone - 22/10/2026

Notificações do iPhone corrigidas e recebimento confirmado pelo usuário, inclusive pelo botão do app. Causa: Apple rejeitou subject VAPID local (403/BadJwtToken). API móvel reiniciada com WEBPUSH_SUBJECT=HTTPS atual do MotoCare, mantendo chaves/inscrição e demais proteções; retorno Apple 201. Estado local em tmp/mobile-access.json, API PID 15812. Importante para retomada: definir o mesmo subject HTTPS no processo do scheduler; .env permanece com o contato local e não foi editado. Nenhum scheduler contínuo iniciado. Instalação/abertura, layout/formulários, offline e scheduler em segundo plano no iPhone aprovados posteriormente conforme aceite final acima. Roteiro ACESSO_CELULAR atualizado.

Ver [ACESSO_CELULAR.md](ACESSO_CELULAR.md). Usuário autorizou explicitamente o túnel Cloudflare após a recusa inicial da revisão automática. Acesso atual: https://select-pond-spouse-diy.trycloudflare.com (temporário; precisa dos processos/PC ligados). Preview móvel separado 5180 → API 8001, hostname exato, API sem debug e cookies Secure/HttpOnly no processo. HTTPS público validou página, assets, manifest/workers/ícones e CSRF 200; smoke com conta descartável aprovou login/sessão/logout, privacidade de /user e rejeição CSRF 419. Fixtures removidas. Processos de revisão fictícia encerrados; estado atual em tmp/mobile-access.json e roteiro. Nenhum .env, schema ou chave alterado; T3 desativada. Revisão real no iPhone concluída conforme aceite final acima. cloudflared oficial/checksum conferido, fora do pacote/Git.

## Estado consolidado da T1 - 22/10/2026

Em 22/10/2026, o usuário confirmou recebimento real em primeiro plano, do teste “MotoCare: teste em segundo plano” com Chrome minimizado e, em seguida, do alerta “MotoCare: Troca de óleo” enviado pelo scheduler real às 14:59 (America/Sao_Paulo), da moto temporária Teste MotoCare T1 em 900 km/limite 1.000 km. O alerta persistiu notificado/urgência média; uma nova verificação enviou zero alertas. O cenário anterior em 899 km permaneceu pendente/zero envios. Todas as fixtures foram removidas. Não há confirmação separada de banner flutuante ou de funcionamento com Chrome encerrado. O aceite remoto não foi usado sozinho como comprovação: houve confirmação do usuário.

Notificações T1 validadas em primeiro e segundo plano e pelo scheduler. Revisão mobile/PWA no iPhone concluída conforme aceite final acima; publicação dos fontes atuais permanece independente. T3 continua desativada. Foi usado schedule:run avulso; manter schedule:work em execução no uso diário.

Correções operacionais: PHP iniciado com OPENSSL_CONF do XAMPP e acesso externo ao serviço de push; HTTP 429 agora traduzido com Retry-After e botão com contagem regressiva. Backend completo: 29 testes/246 verificações antes da alteração; depois, 8 testes WebPush/74 verificações e 20 testes frontend/build aprovados. Esses testes não foram reexecutados nesta última confirmação manual.

A tela branca foi rastreada a frontend/API parados; reiniciados e conferidos página, JS, CSS e API via proxy com HTTP 200. Controle visual da ferramenta indisponível (native pipe); Chrome minimizado e recebimentos confirmados pelo próprio usuário. Manter os servidores locais em execução.

Atualizado em 22/10/2026. A solicitação atual é preparar a continuidade para fazer a T3 em outro momento. Nenhuma funcionalidade financeira foi ativada nesta sessão. Primeiro roteiro para o próximo trabalho: [RETOMADA_T3.md](RETOMADA_T3.md), com inventário confirmado no código, ordem de implementação e aceite.

## Próxima sessão

1. Ler AGENTS, este documento, TASKS, SPEC e MEMORY; depois RETOMADA_T3 e ENTREGA.
2. Clonar/puxar `master` de guibirlem/motocare e conferir a atualização `update task1 e task2 funcionando` no histórico. Ela incorpora a integração de 19/10 e a revisão de 22/10 sobre a base antiga `7f5b9cb`; não começar apenas por essa base antiga. A pasta original é uma cópia sem `.git`; a publicação foi preparada em clone separado.
3. Instalar/iniciar pelo README, conferir migrations e reexecutar testes isolados antes de editar. Criar `feature/t3-financeiro` em um clone com a base atual incorporada.
4. Revisar o financeiro preservado, integrar gastos e definir o alcance de relatórios/exportações ao iniciar. Usar o backlog T3 em TASKS e o roteiro detalhado em RETOMADA_T3.

Pacote de continuidade: `entrega/MotoCare-Retomada-T3-2026-10-22.zip`, com manifesto e SHA-256 ao lado. O ZIP de 19/10 é histórico e não contém os documentos atualizados. O PDF é o retrato T1/T2 preparado em 22/10, não comprovação de conclusão da T3.

## Documento de entrega - 22/10/2026

Atualizado `output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf`, agora com nove páginas reunindo código-fonte/histórico, arquitetura, DER com km_base, Sprint Backlog, atas retrospectivas e evidências atuais. Todas as páginas renderizadas e revisadas visualmente; texto, quatro links e marcadores conferidos. A edição anterior de seis páginas foi substituída. Atas não presumem reuniões ou calendário formal ausentes nos registros. O PDF atual inclui a validação real de notificações, revisão mobile/PWA, tema e regras/segurança posteriores.

Base funcional de 19/10/2026: T1/T2 ativos e T3/financeiro/relatórios adiados. O usuário substituiu Firebase por Web Push padrão para simplificar a configuração.

## Estado atual

- Conta/sessão/CSRF, perfil/senha e garagem reais, com isolamento por usuário e odômetro que não diminui.
- T1: CRUD de alertas de óleo/revisão, gatilho inclusivo de 90% do km OU data, scheduler por minuto, locks, tentativa posterior em falhas e conclusão explícita.
- Web Push nativo com VAPID local: sem SDK, projeto, conta de serviço ou variáveis Firebase. Chaves desta máquina já geradas com php artisan push:configurar.
- T2: CRUD dos quatro serviços, filtros e totais do histórico. PUT limpa opcionais omitidos; concluída pode avançar odômetro, nunca reduzir. Limites/estados de alertas não mudam automaticamente.
- Dashboard consulta /manutencoes e /alertas diretamente. Navegação: Dashboard, Manutenções, Garagem, Configurações e Alertas.

## Executar ou instalar

Instalação completa no [README](README.md); operação do push em [backend/ALERTAS.md](backend/ALERTAS.md).

1. Iniciar MySQL/XAMPP: 127.0.0.1:3306, banco motocare, root, senha vazia.
2. Restaurar dependências pelos lockfiles: composer install e npm ci. Preservar .env/APP_KEY existentes.
3. Em backend: php artisan migrate e php artisan push:configurar. O segundo comando é idempotente e gera chaves apenas na primeira execução.
4. Em backend: php artisan serve --host=127.0.0.1 --port=8000.
5. Em frontend: npm start. Abrir http://127.0.0.1:5173; Ctrl+F5 se aparecer uma versão antiga.
6. Em outro terminal backend: php artisan schedule:work.

npm start compila e serve o build para contornar a restrição local do esbuild. Após editar fontes, reiniciar esse comando. Os processos precisam ser iniciados novamente outro dia. Não há conta/senha padrão nem dados demonstrativos.

## Confirmar no navegador

Entrar → Configurações → Ativar notificações → permitir → Enviar teste de notificação. Conferir a notificação com a página aberta e em segundo plano. Um aceite HTTP do serviço não comprova recebimento. O último navegador ativado recebe os avisos; logout remove o vínculo e tenta cancelar a inscrição local. O antigo token não pode ser convertido: usuários da versão anterior precisam ativar novamente.

Não são necessárias credenciais Firebase. Recebimento real de notificações confirmado pelo usuário em primeiro plano, em segundo plano e pelo scheduler em 22/10, inclusive no iPhone. Revisão de layout/formulários e ciclo offline/reconexão também concluídos no iPhone. Este roteiro permite repetir a conferência em outra instalação.

## Estrutura e cuidados

- API JSON em backend/routes/history.php; auth/auth.session e CSRF no grupo web, sem Blade.
- HistoryController/Manutencao: T2; WorkspaceController: conta, garagem, alertas e inscrição push.
- WebPushConfiguration, comando push:configurar e WebPushService: chaves locais e envio; frontend/services/push.js e public/push-worker.js: cliente nativo.
- Migration 2026_10_19_000003 acrescenta usuarios.push_subscription criptografada e push_endpoint_hash único. SPEC e DER atualizados; fcm_token é coluna legada oculta, sem uso. Não apagar tabelas nem executar migrate:fresh em banco com dados.
- Preservar APP_KEY e storage/app/private/webpush-vapid.json em atualizações. Chaves privadas não entram no pacote/Git. Em outra instalação independente, gerar suas próprias chaves.
- Ao concluir manutenção, concluir o alerta e cadastrar o próximo limite explicitamente; não reativar notificados/concluídos.

## T3 preservada para depois

ExpensesView.vue, ReportsView.vue, Despesa, Money, testes financeiros e a migration despesas.descricao continuam no código. Rotas financeiras da API existem em `backend/routes/history.php`, bloqueadas por EnsureFinanceEnabled; não há rotas/menu dessas telas no frontend. `FEATURE_FINANCEIRO=false` permanece como padrão. Nenhum rollback ou apagamento de dados foi feito. A retomada exige revisão e integração, não reconstrução automática do zero; mapa completo em [RETOMADA_T3.md](RETOMADA_T3.md).

## Validações históricas da aplicação - 19/10/2026

- 29 testes backend, 246 verificações aprovadas: limites/data, tentativas, isolamento, criptografia/VAPID, expiração 410 e rejeição de destinos/redirecionamentos inseguros; T2 e T3 desativada por padrão.
- 19 testes frontend aprovados: ciclo do worker, notificação/clique, permissão por gesto, cancelamento na mudança de sessão, logout e cache público.
- Build, Pint e validação do Composer aprovados.
- Migration aplicada no MySQL e chaves locais geradas; GET /push-config confirmou configured=true via HTTP autenticado.
- Smoke no MySQL: inscrição/remoção, privacidade de /user, CRUD completo dos quatro serviços, ciclo de alerta, logout e financeiro 404. Conta/registros descartáveis removidos; nenhum push foi enviado ao endpoint sintético.
- Transporte criptografado validado com resposta HTTP simulada, sem envio real. Layout, permissão e recebimento reais aguardam navegador.

## Transferência

Pacote auxiliar local atual: entrega/MotoCare-Retomada-T3-2026-10-22.zip, recomposto com os documentos e PDF atuais. Pacote de 19/10 permanece histórico. Fontes/documentos/PDF integram a atualização Git desta edição; o diretório entrega é ignorado por duplicar os fontes. A pasta original não tem .git; foi usado clone separado para preservar o histórico. O erro 403 do conector foi contornado pelo Git instalado. Quatro arquivos legados FCM/demo retirados do clone conforme [ENTREGA.md](ENTREGA.md).

Não copiar .env, chaves privadas, dependências instaladas, caches, logs ou dist. Preservar o histórico de guibirlem/motocare.
