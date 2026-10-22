# Continuação do MotoCare - retomada futura da T3

## Entrega e publicação atualizadas - 22/10/2026

Esta edição consolida os fontes em [guibirlem/motocare](https://github.com/guibirlem/motocare), branch `master`, na atualização com mensagem `update task1 e task2 funcionando`. O commit preserva o histórico anterior; conferir seu SHA no [histórico público](https://github.com/guibirlem/motocare/commits/master/). O erro 403 do conector foi contornado pelo Git instalado, com autenticação de escrita conferida. Não usar a antiga base 7f5b9cb isoladamente para retomar.

O [PDF único de entrega](output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf) foi substituído por edição atual de oito páginas: repositório/commits, arquitetura, DER com km_base, backlog e atas retrospectivas, incluindo os aceites reais e a revisão de negócio/segurança. Todas as páginas foram renderizadas e inspecionadas; texto/links conferidos. Pacote de continuidade local e checksum atualizados. Registros históricos abaixo mantêm o estado observado em cada etapa; menções antigas a PDF histórico/publicação pendente foram superadas por esta edição.

Nenhuma regra ou configuração privada mudou nesta preparação documental/publicação. As validações funcionais vigentes são 45 testes backend/391 verificações e 28 frontend/build, com limites em REVISAO_LOGICA_SEGURANCA.md. Financeiro/T3 continua false; para retomar, clonar/puxar a versão atual e seguir RETOMADA_T3.md. Não copiar .env, APP_KEY ou VAPID de outra máquina.

## Revisão de negócio e segurança - 22/10/2026

Relatório: [REVISAO_LOGICA_SEGURANCA.md](REVISAO_LOGICA_SEGURANCA.md). Usuário escolheu manutenção concluída avançar automaticamente o odômetro sem nunca reduzir e autorizou reconciliação de Kawasaki Ninja 66.777→66.779 km e Moto Duas rodas 0→122 km. Gravação de manutenção/KM atômica com bloqueio da moto; histórico menor, pendência, edição para KM menor e exclusão não reduzem odômetro. Concluído não aceita data futura; tela usa data de São Paulo. Alertas continuam com conclusão/próximo limite explícitos.

Usuário também escolheu 90% do intervalo, substituindo 90% do odômetro absoluto. Migration aditiva `2026_10_22_000001_add_km_base_to_alertas_table.php` aplicada no MySQL; SPEC/DER atualizados. Referência persistida no alerta e editável enquanto pendente; exemplo base 66.779, limite 71.779, aviso 71.279. Criação sem referência usa KM atual se abaixo do limite, senão zero. Pendentes antigos futuros inicializados pelo odômetro atual; vencidos continuam vencidos; histórico notificado/concluído preservado. UI/progresso/scheduler usam a mesma fórmula inteira. Nada foi reenviado nesta auditoria.

Segurança: respostas antigas descartadas após logout/troca de sessão e refresh fora de ordem; senhas novas limitadas a 72 bytes sem NUL; campos de senha atual com formato inválido tratados como 422; perfil/senha com limites separados de dez tentativas/minuto. Erros da API sanitizados mesmo com debug, com Retry-After preservado; CSRF/isolamento/criptografia push conferidos. Preview com CSP/nosniff/referrer/frame e bloqueio explícito de caminhos privados. Vite atualizado para 6.4.5/plugin Vue 5.2.4 e transitivas corrigidas; npm audit/Composer audit sem avisos conhecidos. APP_KEY/chave privada VAPID/padrões de credenciais não encontrados nos fontes elegíveis/build; .env/chaves mantidos privados, sem rotação. Chave pública VAPID é necessária ao Web Push.

Validações atuais: 45 testes backend/391 verificações, 28 frontend, build, Pint dos arquivos alterados e Composer validate aprovados. HTTP/MySQL com contas descartáveis aprovou odômetro/histórico/datas/referência de alerta, isolamento, CSRF e logout; fixtures removidas. Banco atual: zero divergências manutenção/odômetro, concluídas futuras/sem KM e referências pendentes inválidas. HTTPS autorizado confirmou assets/PWA 200, acesso anônimo privado 401, caminhos de segredos 404 e cookies móveis Secure/HttpOnly. Previews substituídos pelo Vite corrigido: local PID 18208 e móvel 18524; API/túnel preservados, estado em tmp/mobile-access.json. Pint global aponta só formatação de quatro migrations antigas não alteradas; relatório explicita limite. Aceites visuais/push anteriores não foram repetidos manualmente; nenhum scheduler contínuo iniciado. Fontes consolidados na publicação descrita acima; pacote/checksum atualizados; T3 desativada.

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

Atualizado `output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf`, agora com oito páginas reunindo código-fonte/histórico, arquitetura, DER com km_base, Sprint Backlog, atas retrospectivas e evidências atuais. Todas as páginas renderizadas e revisadas visualmente; texto, três links e marcadores conferidos. A edição anterior de seis páginas foi substituída. Atas não presumem reuniões ou calendário formal ausentes nos registros. O PDF atual inclui a validação real de notificações, revisão mobile/PWA, tema e regras/segurança posteriores.

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
