# SPEC.md - Especificação Funcional do MotoCare

## Apresentação pública — 23/10/2026

- Landing em `/apresentacao`, acessível com ou sem conta. Raiz `/` mostra apresentação para visitantes e mantém o dashboard para a sessão autenticada. Alias `/dashboard` e demais rotas preservados; visitante em rota do sistema continua no AccountGate existente.
- CTAs Entrar e acesso autenticado usam `/dashboard`. Começar Grátis usa `/dashboard?cadastro=1` para selecionar o cadastro no mesmo formulário atual, sem nova API ou rota de autenticação. Usuário conectado recebe destino `/dashboard` sem modo de cadastro. Link de relatórios usa `/relatorios` e conserva a autenticação/feature existentes.
- Página apresenta funcionalidades, diferenciais de moto, relatórios e benefícios, com âncoras suaves, versão mobile e preferência de movimento reduzido. Modelo 100% gratuito, sem planos/tabelas de preços. Prévias têm dados fixos identificados como ilustrativos e não consultam dados privados.
- Backend, schema, dados, contratos financeiros, permissões, sessão/CSRF, PWA/offline e temas preservados. Modo offline global continua anterior à apresentação; a página online independe do sucesso da API. CSS da landing é escopado e seu código é carregado sob demanda.

## Complementos de uso diário — 23/10/2026

Extensão aditiva de T1/T2/T3. Migration `2026_10_23_000001_add_motorcycle_insights.php` cria postos/preços e acrescenta apenas campos opcionais, sem transformar registros antigos. Serviços legados pneus/freios continuam disponíveis, junto aos separados por eixo e aos de corrente, filtros, suspensão, arrefecimento, cabos e vela. `natureza` preventiva/corretiva é opcional; históricos sem classificação são apresentados separadamente.

- Postos favoritos pertencem à conta, compartilhados entre suas motos: GET/POST `/api/postos`, DELETE `/api/postos/{id}`. Preços separados por combustível (gasolina, gasolina_aditivada, etanol, diesel). Ao salvar/editar/excluir abastecimento, a memória acompanha o último registro por data/id ainda vinculado ao posto. Lançamento histórico não substitui preço mais recente; exclusão de favorito preserva seu texto nos gastos. Posto alheio retorna 404.
- Despesas aceitam ainda documentacao, impostos e outros. Abastecimento recebe posto_id/combustivel, km_abastecimento e tanque_cheio opcionais; tanque cheio exige KM. Com KM, não aceita data futura e avança o odômetro em transação, sem diminuir. Contrato anterior e precisão de duas casas são preservados. Cálculo inverso arredonda preço e reconcilia total por litros × preço com informação na tela.
- Consumo ponderado: soma das distâncias dividida pelos litros dos ciclos válidos entre dois tanques cheios, incluindo parciais intermediários e excluindo litros do primeiro cheio. Falta de KM/marcação ou regressão cronológica invalida trecho até nova referência cheia. Ordenação data/id; no mesmo dia cadastrar na ordem de ocorrência. Relatórios selecionam ciclos pela data do cheio final, com referência anterior ao período quando disponível. Sem dados suficientes retorna null.
- Moto recebe capacidade_tanque/volume_reserva opcionais (litros), reserva menor que capacidade. Estimativa usa consumo ponderado dos cinco últimos ciclos, último cheio, parciais seguintes e odômetro atual; informa parcial, litros e KM até reserva/secar. É estimativa, sem medição do combustível. Sem dados suficientes orienta completar registros.
- GET `/api/motocicletas/{id}/painel` acrescenta gasto do mês, categorias/corrente; financeiro habilitado acrescenta consumo, custo/KM, despesas e séries. Custo/KM: custos registrados desde a primeira leitura de odômetro até a atual, excluindo pendentes; distância não positiva retorna null. Relatórios preservam todas as chaves/totais anteriores e acrescentam categorias/estatísticas/preços mensais (média aritmética de preços por posto/combustível/mês).
- Alertas aceitam os serviços adicionais mantendo 90% do intervalo ou data, locks, tentativas e conclusão explícita. POST `/api/motocicletas/{id}/chuva-lavagem` cria lembrete de lubrificação vencendo hoje, sem alterar alertas anteriores; repetição devolve lembrete ainda ativo. Dashboard destaca imediatamente; push enviado pelo scheduler existente. Não implica serviço realizado nem conclusão automática. Usuário registra o serviço e próximo ciclo (sugestão editável 500 km, conforme fabricante).
- Contador: ajustes concluídos posteriores à última troca concluída do kit, ordenados data/KM/id. Sem troca registrada, desconhecido. Não estima limite físico/segurança do desgaste. Edição/exclusão recalcula sem reescrever históricos.

Resumo mensal acrescenta `outras_despesas` (documentação, impostos e outros), preservando as chaves anteriores; essa coluna participa do total combinado. Gráficos da tela de relatórios respeitam o período selecionado; o dashboard apresenta o histórico válido completo. Nas estimativas, cada abastecimento parcial é limitado à capacidade física do tanque antes de descontar o trecho seguinte.

## Contrato de integração da T3 - 23/10/2026

- Gastos em /gastos e relatórios em /relatorios; GET autenticado /api/features retorna `{ "financeiro": true|false }`. Cliente carrega esse valor antes de expor usuário/menu e limpa ao logout; respostas antigas de sessão não reativam módulos. Default true e exemplo true; false mantém despesas/relatórios 404 e navegação indisponível. Dashboard continua independente dessa API.
- CRUD de despesas segue o contrato já preservado. PUT substitui opcionais omitidos por null; troca para reparo limpa litros/preço/posto. Interface calcula combustível em centavos e transforma vírgula decimal em ponto no payload. Reparo exige descrição; o custo de um serviço deve ser cadastrado uma única vez, em despesas ou manutenção. Não existe deduplicação automática entre cadastros manuais.
- Filtro mes=AAAA-MM inclui o mês inteiro; sem mês mostra histórico completo. Ordenação por data/id descendentes. Total da tela de Gastos acompanha os registros do filtro de tipo selecionado.
- Relatórios com início/fim inclusivos e até dez anos. motocicleta_id opcional, inteiro positivo; zero/negativo/fração/formato inválido 422, moto inexistente/alheia 404. Sem ID, inclui somente motos da conta. Meses vazios retornam zero; manutenção pendente pode aparecer nos lançamentos, mas nunca nos totais. Somas em centavos; JSON usa strings decimais.
- Alterar filtros de relatório descarta o resultado anterior e desabilita exportação até nova consulta. Campos ficam bloqueados durante consulta; títulos/arquivo usam o período capturado no envio. Falha de consulta não mantém dados de outro período. Troca global de moto reinicia telas vinculadas à moto, mas não descarta formulário de conta/garagem.
- CSV: BOM UTF-8, separador ponto e vírgula, casas decimais com vírgula, campos entre aspas e aspas escapadas; neutralizar prefixos de fórmula. Inclui status para distinguir pendências. Impressão/Salvar PDF executada pelo navegador, paleta clara, sem navegação/formulários, cabeçalhos repetidos e tabelas sem rolagem; não existe API de geração PDF nem view Blade.
- Schema/migrations já aplicados permanecem iguais. Validações e limites em VALIDACAO_T3.md; teste de 390px usa Chromium e não comprova aceite novo em Safari/iPhone físico.

## Continuidade preparada em 22/10/2026

Teste móvel: preview separado na porta loopback 5180, proxy /api para 8001, hostname exato permitido; backend móvel usa debug desativado e cookies Secure/HttpOnly no processo, mantendo sessão/CSRF/autorização. Nenhum contrato de dados alterado. Túnel Cloudflare autorizado explicitamente e aberto em 22/10; HTTPS público/login/sessão/CSRF validados com fixture descartável removida. Revisão mobile/PWA concluída no iPhone: usuário confirmou abertura pelo app instalado, telas/formulários, push pelo botão, ciclo offline/reconexão e alerta do scheduler em segundo plano. Operação e URL temporária em [ACESSO_CELULAR.md](ACESSO_CELULAR.md).

Em 22/10 a continuidade preparou a T3 sem ativá-la. A retomada de 23/10 abaixo substitui esse estado; roteiro e aceite atuais em [RETOMADA_T3.md](RETOMADA_T3.md), [TASKS.md](TASKS.md) e [VALIDACAO_T3.md](VALIDACAO_T3.md).

## Escopo vigente — 23/10/2026

Por solicitação de iniciar a T3 em 23/10, a entrega ativa cobre T1, T2 e T3, incluindo gastos, abastecimentos, relatórios, CSV e impressão/Salvar PDF. FEATURE_FINANCEIRO=true é o padrão; false permite desligar financeiro/relatórios sem afetar Dashboard/T1/T2. Menu e rotas do cliente seguem a configuração autenticada da API. O adiamento de 19/10 foi superado; contratos financeiros abaixo estão ativos, sem mudança de schema nesta retomada.

## Contexto do Produto
MotoCare é uma plataforma para motociclistas que precisam controlar manutenção preventiva, revisões, despesas com combustível e reparos, além de receber alertas automáticos para reduzir risco mecânico e ansiedade no uso diário.

## Persona Validada
**Lucas "Luke" Almeida**, 21 anos, estudante de TI e estagiário.

### Perfil
- Usa moto de baixa cilindrada todos os dias para trabalhar e estudar.
- Tem rotina acelerada e baixa tolerância a falhas mecânicas inesperadas.

### Dores
1. Confiabilidade nula: depende de memória ou adesivos físicos que se perdem ou apagam.
2. Medo e ansiedade: teme ficar parado na rua por falha mecânica evitável.
3. Descontrole financeiro: perde notas fiscais e não enxerga o gasto mensal real com moto.

### Proposta Única de Valor
Oferecer tranquilidade, segurança ao pilotar e economia financeira com acompanhamento automático de quilometragem e despesas.

## Regras de Negócio
- Cada motocicleta pertence a um usuário.
- Alertas devem ser vinculados a uma motocicleta e disparados conforme quilometragem ou data.
- A manutenção deve registrar serviço, valor, status e quilometragem da execução.
- A despesa deve suportar combustível e reparos com detalhamento financeiro.
- O backend deve responder apenas JSON.

## Mapeamento de Entidades e DER

Diagrama atualizado em [DER.md](DER.md).

### usuarios
- `id` [PK]
- `nome`
- `email` [UNIQUE]
- `password`
- `telefone`
- `fcm_token` [legado sem uso, oculto]
- `push_subscription` [TEXT NULL, criptografado por APP_KEY]
- `push_endpoint_hash` [CHAR(64) NULL UNIQUE, oculto]

### motocicletas
- `id` [PK]
- `usuario_id` [FK]
- `marca`
- `modelo`
- `ano`
- `placa` [UNIQUE]
- `km_atual`

### manutencoes
- `id` [PK]
- `motocicleta_id` [FK]
- `tipo_servico`
- `descricao`
- `valor`
- `status`
- `data`
- `km_servico`

### despesas
- `descricao` [TEXT NULL; migration aditiva da entrega integrada]
- `id` [PK]
- `motocicleta_id` [FK]
- `tipo_despesa`
- `valor`
- `litros`
- `preco_litro`
- `posto`
- `data`

### alertas
- `id` [PK]
- `motocicleta_id` [FK]
- `tipo_alerta`
- `nivel_urgencia`
- `km_limite`
- `km_base` [UNSIGNED INTEGER NULL; referência do intervalo, adicionada em 22/10/2026]
- `data_limite`
- `status`

## Relacionamentos
- `usuarios` 1:N `motocicletas`
- `motocicletas` 1:N `manutencoes`
- `motocicletas` 1:N `despesas`
- `motocicletas` 1:N `alertas`

## Diretrizes Técnicas

### Segurança e validações revisadas - 22/10/2026

- Novas senhas: mínimo oito caracteres, no máximo 72 bytes e sem NUL para evitar truncamento/falha de bcrypt. Senhas existentes não são regravadas. Senha atual deve ser string válida antes da verificação; perfil e troca de senha têm limites separados de dez tentativas/minuto, além dos limites existentes de login/cadastro/teste push.
- API não retorna detalhes internos nos erros mesmo com debug ligado. Respostas de validação 422 mantêm campos/erros; demais erros preservam código HTTP/Retry-After e retornam mensagem saneada. API usa no-store/private/nosniff. Preview local/móvel aplica CSP/nosniff/referrer/frame e nega caminhos privados antes do fallback.
- Respostas do frontend são vinculadas à sessão que as iniciou e descartadas após mudança/logout; atualização fora de ordem da garagem não substitui a mais nova. Tokens CSRF atrasados não substituem o da sessão atual. Formulários não reproduzem automaticamente gravações offline.
- Placa é normalizada também na API, sem confiar no cliente. Locks de edição/exclusão de alertas usam o ID real encontrado por propriedade, inclusive se a URL tiver zeros à esquerda, para corresponder ao scheduler.
- Revisão/limites e evidências em [REVISAO_LOGICA_SEGURANCA.md](REVISAO_LOGICA_SEGURANCA.md). Frontend depende de Vite 6.4.5/plugin Vue5.2.4 com lockfile atualizado; não reverter ao Vite 5 da entrega histórica.

### Tema claro e noturno - 22/10/2026

- Seletor disponível antes do login, durante carregamento, na tela offline e no cabeçalho autenticado, em computador/mobile. No cabeçalho móvel o ícone tem nome acessível e indica a ação de alternar.
- Preferência `light`/`dark` salva localmente em `motocare-theme`, por navegador/origem, sem vínculo à conta ou chamada de API. Uma escolha válida prevalece sobre o sistema; sem escolha válida, a abertura segue prefers-color-scheme. Se o armazenamento estiver bloqueado, alternar continua possível nesta abertura.
- Tema aplicado antes de montar o app e mantido na navegação/offline. Paleta inclui conteúdo, formulários, tabelas, avisos e controles nativos via color-scheme; identidade verde preservada com texto escuro. Meta theme-color acompanha o tema. Impressão permanece clara.
- Nenhuma consulta/gravação de dados da conta passa a funcionar offline por causa do tema. Sem mudança de API/schema, inscrição push ou ativação da T3.

### Comportamento offline da interface - 22/10/2026

- Abrir o shell offline exibe “Modo offline” antes do login e da área autenticada: conectar à internet para consultar/salvar dados. Não tenta iniciar sessão enquanto offline; ao evento online tenta carregar sessão/dados novamente, com opção manual de verificar conexão.
- O cliente HTTP bloqueia novas requisições quando navigator.onLine é false. Não há fila nem replay de gravações. Falhas sem resposta, quando o navegador não indica offline, orientam verificar a conexão; resposta de serviço 5xx continua diferenciada.
- Cache permanece restrito a shell/assets públicos; nenhuma resposta de API/dado pessoal é armazenada para consulta offline. Em 22/10, usuário confirmou mensagem/botão offline e retorno ao carregar após reconectar no iPhone, além dos testes automatizados.

- MySQL 8.0 com InnoDB e chaves estrangeiras.
- Tipos numéricos devem preservar precisão financeira.
- Campos de data devem ser definidos de forma consistente para consultas e alertas.
- Qualquer alteração estrutural requer atualização da SPEC, do DER e das migrations correspondentes.
- Implementação atual: Laravel 12.x/PHP 8.2+ no backend; Vue 3/Vite no frontend.

### T1 — Alertas por quilometragem

- Tipos: `troca_oleo` e `revisao`; limite absoluto do odômetro, positivo.
- Por decisão do usuário em 22/10/2026, os 90% passam a ser do intervalo entre serviço e próximo limite: `km_base + ceil((km_limite - km_base) * 9 / 10)`. Verificação inteira inclusiva a cada minuto: `km_atual * 10 >= km_limite * 9 + km_base`. Exemplo: base 66.779, limite 71.779, aviso em 71.279 km. Substitui a regra antiga de 90% do odômetro absoluto.
- `km_base` fica persistido por alerta com limite de KM. Na criação, se omitido, usa o odômetro atual quando abaixo do limite, senão zero para um alerta já vencido. Uma referência explícita deve estar entre zero e o odômetro atual e ser menor que o limite; usuário pode informar o KM do último serviço. Em edição pendente, referência omitida preserva a existente quando há limite de KM; mudar para apenas data limpa KM/base.
- Migration aditiva `2026_10_22_000001_add_km_base_to_alertas_table.php`: alertas pendentes antigos com limite futuro recebem o odômetro atual como referência; os já vencidos recebem zero e continuam vencidos. Notificados/concluídos mantêm status e base zero para preservar o cálculo histórico; somente data mantém base null. Não envia notificações nem reduz odômetros. DER atualizado.
- Estados: `pendente`, `notificado` após aceite HTTP 2xx do serviço de push do navegador e `concluido` após serviço.
- Urgência no envio: `media` antes do limite, `alta` no limite ou acima.
- Falhas e ausência de inscrição push preservam o estado pendente para nova tentativa. A entrega integrada também verifica data_limite <= hoje (America/Sao_Paulo); basta atingir km OU data.
- Configuração e operação: [backend/ALERTAS.md](backend/ALERTAS.md).
- Inscrição push em usuarios acrescentada por migration aditiva; relacionamentos preservados.

### T2 — Histórico de manutenções

- Implementado em 19/10/2026, reutilizando o schema existente, sem migrations novas ou alteração do DER.
- Tipos: `troca_oleo`, `kit_relacao`, `pneus`, `freios`. Estados: `pendente` e `concluido` (Feito na interface).
- Campos obrigatórios: tipo, valor entre 0 e 99999999.99 com até duas casas, status e data válida em AAAA-MM-DD. Descrição opcional, até 5.000 caracteres. Quilometragem inteira entre 0 e 4294967295, obrigatória para concluído e opcional para pendente.
- Valor retornado como string decimal com duas casas. Data retornada como AAAA-MM-DD; interface exibe DD/MM/AAAA.
- Listagem por moto, ordenada por data decrescente e depois ID decrescente. Totais na interface consideram a moto selecionada; total gasto soma somente concluídos.
- Por escolha do usuário em 22/10, criar/editar manutenção concluída aumenta o odômetro para o KM do serviço se maior, na mesma transação com bloqueio da moto. Registro histórico menor, pendência, edição para KM menor e exclusão nunca reduzem o odômetro. Concluído não aceita data futura; pendente pode ser planejado para o futuro. Frontend usa a data de São Paulo.
- Registrar manutenção não conclui nem reinicia alertas automaticamente. Referências/limites/estados permanecem explícitos; a próxima execução do scheduler usa o odômetro atualizado.
- O proprietário é obtido da sessão autenticada. Motos alheias e registros fora da moto informada retornam 404. Sem autenticação: 401; validação: 422; CSRF inválido: 419. Respostas JSON, exceto exclusão bem-sucedida (204 sem corpo).

#### Sessão e endpoints

API em `/api`, registrada por `routes/history.php` no grupo web para usar sessão e CSRF nativos do Laravel. Não usa Sanctum nem tokens bearer. Frontend e API devem compartilhar a origem: proxy Vite no desenvolvimento e reverse proxy em produção, com HTTPS e cookie seguro em produção.

| Método | Caminho após /api | Função |
| --- | --- | --- |
| GET | /csrf | Inicia sessão e retorna token CSRF |
| POST | /register | Cria conta e autentica (nome, email, password, password_confirmation) |
| POST | /login | Autentica por email/password |
| GET | /user | Usuário autenticado, sem senha |
| GET | /features | Configuração autenticada: financeiro:boolean; sem segredos |
| POST | /logout | Invalida sessão |
| GET / POST | /motocicletas | Consulta motos próprias / cadastra moto |
| GET / POST | /motocicletas/{moto}/manutencoes | Lista / cria manutenção |
| PUT / DELETE | /motocicletas/{moto}/manutencoes/{manutencao} | Substitui campos de serviço / exclui |

- Todas as gravações exigem cookie de sessão e cabeçalho X-CSRF-TOKEN obtido em /csrf; atualizar token após login/cadastro e ao reiniciar sessão.
- Cadastro exige senha confirmada de pelo menos oito caracteres; email único. Login e cadastro limitados a cinco tentativas por minuto por IP.
- Moto: marca/modelo obrigatórios até 255 caracteres, ano inteiro entre 1900 e 2100, placa brasileira normalizada com sete caracteres (única), km_atual inteiro não negativo até 4294967295. usuario_id enviado pelo cliente é ignorado.
- PUT exige os mesmos campos obrigatórios de POST. moto/usuário do registro não podem ser trocados pelo payload.

## Entrega integrada — contrato definido em 19/10/2026

- Todas as telas passam a exigir a mesma sessão; nenhum dado demonstrativo deve aparecer como dado real. Seleção de moto compartilhada.
- T3: tipos combustivel e reparo. Valor em centavos decimais, não negativo; datas AAAA-MM-DD. Combustível exige litros e preco_litro positivos, até duas casas, e valor igual ao produto arredondado a centavos (tolerância de um centavo). Reparos exigem descricao; litros/preco_litro/posto são limpos em reparos. Migration aditiva acrescenta despesas.descricao TEXT nullable; DER atualizado abaixo.
- Despesas não são criadas automaticamente pelas manutenções. Resumos mostram despesas, manutenções concluídas e total combinado, sem replicar lançamentos. O usuário deve registrar um mesmo serviço apenas uma vez.
- CRUD /api/motocicletas/{moto}/despesas e filtros mes=AAAA-MM; /api/relatorios recebe inicio/fim (datas inclusivas), motocicleta_id opcional, retorna lançamentos e resumo por mês com strings decimais. Relatório limitado a dez anos de intervalo.
- Garagem permite criar/editar/excluir motos próprias. Exclusão confirmada remove dados vinculados por FK. Odômetro não pode diminuir; correção exige decisão futura, não sobrescrita silenciosa.
- PUT /api/user altera nome, email, telefone; mudança de email exige current_password. PUT /api/password exige current_password e nova password confirmada, mínimo 8; outras sessões autenticadas são invalidadas pelo middleware auth.session.
- Alertas próprios: CRUD em /api/motocicletas/{moto}/alertas. Tipos troca_oleo/revisao; limite de km positivo ou data_limite obrigatórios. Disparo por 90% do intervalo entre km_base e km_limite OU chegada da data. Criação pendente; alteração de limites/referência apenas enquanto pendente; transição explícita para concluido disponível. Alertas notificados não são reativados.
- PUT /api/push-subscription registra a inscrição do navegador autenticado; DELETE remove o endpoint correspondente. Logout remove o vínculo da conta. GET /api/push-config informa disponibilidade/chave pública, sem chave privada. Permissão solicitada apenas por ação do usuário. Uma inscrição por usuário (último navegador ativado).
- Relatórios usam dados reais e podem ser baixados em CSV ou impressos/salvos em PDF pelo navegador. Sem views Blade.
- PWA armazena somente shell/assets públicos, nunca respostas de API; offline permite abrir o shell e mostra indisponibilidade, sem prometer gravação offline.

- Em PUT de alerta pendente, limites omitidos são limpos: trocar de data para km não mantém uma data anterior inadvertidamente.

### Verificação operacional da T1

- Aceite iPhone de 22/10 às 15:47:51: `schedule:run` real enviou alerta de óleo em 900/1.000 km, notificado/média, e usuário confirmou recebimento em segundo plano. Em 899 km permaneceu pendente; repetição e verificação retornaram zero novos envios/zero falhas. Usado SQLite descartável com migrations existentes e push real para preservar alerta elegível no MySQL; banco temporário removido e dados originais preservados. Não altera regras/contratos nem substitui a validação anterior de persistência no MySQL. Nenhum scheduler contínuo iniciado.

- Compatibilidade iPhone de 22/10: contato VAPID local foi rejeitado pela Apple (403/BadJwtToken). Configurar WEBPUSH_SUBJECT como contato externo válido também no processo do scheduler. No teste via túnel, o HTTPS do MotoCare foi aceito (201) e o usuário confirmou recebimento no iPhone pelo botão. Não requer rotação de chaves nem mudança estrutural/contratual.

- Evidência de 22/10/2026: recebimento real confirmado pelo usuário em primeiro plano, segundo plano (Chrome minimizado) e alerta de óleo via scheduler às 14:59, na fronteira de 90%. Estado notificado/média e ausência de reenvio conferidos no MySQL; fixtures removidas. Isso não altera a regra de que aceite HTTP sozinho não comprova recebimento, nem comprova funcionamento com Chrome encerrado.

- Interface (22/10): em HTTP 429, exibir mensagem em português com tempo de espera de Retry-After (60 segundos se ausente/inválido); no teste push, desabilitar o botão com contagem regressiva até o prazo terminar. O limite de três tentativas/minuto da API permanece inalterado.

- GET /api/push-config informa configured, publicKey e subscriptionRegistered. Disponível quando as chaves locais VAPID e subject são válidos; isso não testa conectividade/recebimento remoto.
- POST /api/push-test exige autenticação, CSRF e endpoint igual ao navegador vinculado à conta; três tentativas/minuto. Sem inscrição/incompatibilidade: 422; indisponibilidade/falha: 503; aceite HTTP 2xx pelo serviço: 202. Não cria/conclui alertas e não comprova recebimento no navegador.
- Redirecionamentos não são seguidos nem considerados envio aceito. Em falhas, preservar pendente. Expiração 404/410 limpa somente a inscrição que falhou, sem apagar uma inscrição nova.
- php artisan push:configurar gera/valida chaves VAPID locais, preserva chaves válidas existentes, não imprime segredos nem envia mensagem. Arquivo privado padrão: storage/app/private/webpush-vapid.json; fora do Git/pacote. WEBPUSH_SUBJECT define o contato; WEBPUSH_KEY_PATH permite outro local privado.
- PUT de manutenção substitui campos: descrição e km opcionais omitidos são limpos; km continua obrigatório para concluído.

## Substituição de push autorizada em 19/10/2026

A decisão mais recente remove Firebase/FCM da implementação ativa. A T1 passa a usar Push API/Service Worker e VAPID local com minishlink/web-push. Sem SDK externo, projeto ou conta Firebase. O navegador utiliza seu serviço de push e exige permissão/contexto seguro; isso não exige credenciais de serviço do MotoCare em outro provedor.

Alteração estrutural aditiva em usuarios: push_subscription TEXT nullable (cast criptografado pela APP_KEY) e push_endpoint_hash CHAR(64) nullable UNIQUE. Uma inscrição por usuário, último navegador ativado. fcm_token permanece somente como coluna legada sem uso, oculta e sem atribuição pela API. DER e migration correspondentes atualizados. Preservar T1/T2; o adiamento da T3 vigente naquela etapa foi substituído pela retomada autorizada de 23/10.

Endpoints ativos: GET /api/push-config (configured/publicKey/subscriptionRegistered), PUT/DELETE /api/push-subscription e POST /api/push-test. Inscrição contém endpoint HTTPS e keys.p256dh/auth. Só aceitar domínios oficiais de push dos navegadores, sem portas customizadas, credenciais na URL, fragmentos ou redirecionamentos; evitar SSRF. Endpoint é capacidade privada, não retornar em /user. Logout remove vínculo. Teste só aceita o endpoint inscrito na conta. Assinaturas expiradas 404/410 são removidas condicionalmente e preservam alertas pendentes para reativação. Envio aceito não equivale a confirmação de recebimento.
