# MotoCare

PWA Vue 3/Vite com API JSON Laravel 12/PHP 8.2+ e MySQL do XAMPP. Escopo atual: T1 (alertas) e T2 (histórico de manutenções), com conta e garagem. T3/financeiro e relatórios ficam para outra etapa.

## Entrega e histórico

- [Repositório e fontes](https://github.com/guibirlem/motocare) e [histórico de commits](https://github.com/guibirlem/motocare/commits/master/).
- Edição de 22/10/2026, mensagem de commit: `update task1 e task2 funcionando`, com histórico anterior preservado.
- [PDF único da Parte 2](output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf): código, arquitetura, DER, backlog, atas e evidências em oito páginas.
- [Entrega](ENTREGA.md), [continuidade](CONTINUACAO.md) e [retomada da T3](RETOMADA_T3.md).

## O que está entregue

Revisão de negócio/segurança de 22/10: manutenção feita atualiza o odômetro se o KM for maior, sem regressão por histórico/edição/exclusão; concluído futuro é rejeitado. Alertas usam 90% do intervalo entre KM de referência e próximo limite (66.779→71.779 avisa em 71.279), por escolha do usuário. Atualizar com `php artisan migrate` para incluir km_base, preservando .env/chaves. Vite/transitivas corrigidos, dependências sem avisos conhecidos, proteção de sessão/erros/segredos revisada. Evidências e limites em [REVISAO_LOGICA_SEGURANCA.md](REVISAO_LOGICA_SEGURANCA.md): 45 testes backend/391 verificações, 28 frontend/build e smoke HTTP/MySQL aprovados.

Modo noturno disponível no computador e no celular, inclusive login e tela offline. Use o seletor no cabeçalho (lua/sol no celular); a escolha fica salva neste navegador. Sem escolha anterior, o app segue a aparência do sistema ao abrir. No iPhone já instalado, carregar com internet após fechar completamente pela multitarefa para receber esta versão; no computador, Ctrl+F5. Usuário confirmou “Sim, tema escuro funcionando”; aceite visual manual e testes/build aprovados.

Documentação de continuidade atualizada em 22/10/2026: para fazer a próxima etapa, começar por [CONTINUACAO.md](CONTINUACAO.md) e [RETOMADA_T3.md](RETOMADA_T3.md). O financeiro preservado precisa ser revisado e integrado; esta preparação não o ativa. Pacote atual: `entrega/MotoCare-Retomada-T3-2026-10-22.zip`, com manifesto e checksum. A atualização Git desta edição incorpora a integração e a revisão de 22/10 sobre `7f5b9cb` (08/10). Para iniciar em outro computador, clonar/puxar a versão atual antes de criar a branch da T3. O pacote ZIP, manifesto e checksum são cópias locais auxiliares; os fontes e o PDF estão no Git.

- Conta com cadastro, login/logout, perfil e alteração de senha; isolamento por usuário.
- Garagem com cadastro, edição, exclusão confirmada e atualização de odômetro.
- T1: alertas de óleo/revisão aos 90% do intervalo entre referência e próximo limite ou na data; scheduler e Web Push com VAPID local.
- T2: histórico real de óleo, kit relação, pneus e freios, com edição, exclusão e filtros.
- T3 adiada: código anterior de despesas/resumo preservado; API financeira desativada por padrão (FEATURE_FINANCEIRO=false).
- Relatórios/exportações adiados; telas retiradas da navegação e do roteador.
- PWA com ícones e cache do shell público; APIs e dados pessoais não entram no cache offline. Offline não permite consultar/gravar dados da conta.

Correção offline de 22/10: o shell abre uma tela “Modo offline”, inclusive antes do login, com orientação para reconectar; o cliente bloqueia novas chamadas quando o navegador indica ausência de rede e não enfileira gravações. O evento online tenta carregar novamente. Carregar a nova versão com internet antes de testar sem Wi-Fi/dados móveis. Testes frontend: 22 aprovados e build aprovado; usuário confirmou mensagem e retorno online no iPhone após fechar completamente o app e reabrir atualizado.

Notificações T1 confirmadas pelo usuário em 22/10: primeiro plano, Chrome minimizado e alerta real pelo scheduler. Revisão mobile/PWA concluída no iPhone: abertura pelo app instalado, telas/formulários, offline/reconexão e push pelo botão/scheduler em segundo plano. Fontes e documentação desta edição consolidados na atualização Git indicada acima. Não é necessário configurar Firebase. Detalhes em [CONTINUACAO.md](CONTINUACAO.md).

## Instalar em outro computador

Para testes no celular, seguir [ACESSO_CELULAR.md](ACESSO_CELULAR.md). O usuário autorizou o túnel Cloudflare em 22/10; endereço temporário atual registrado no roteiro. HTTPS público, assets/PWA, sessão/login/CSRF e cookies seguros validados, com conta descartável removida. Exige computador e processos móveis ligados. Instalação/abertura e funcionamento dos fluxos conferidos no iPhone aprovados pelo usuário.

Notificações no iPhone confirmadas em 22/10 pelo botão e pelo scheduler real às 15:47:51, em segundo plano: corrigido contato VAPID com WEBPUSH_SUBJECT HTTPS no processo móvel após rejeição Apple 403/BadJwtToken. O scheduler deve receber o mesmo contato. Teste isolado com banco SQLite descartável/migrations existentes para preservar alerta elegível no MySQL; cenário removido e originais preservados. Sem scheduler contínuo iniciado: manter `php artisan schedule:work` ativo para uso diário.

Instale Git, PHP 8.2+ e extensões exigidas pelo Composer (incluindo pdo_mysql, pdo_sqlite para testes, openssl, curl, mbstring e zip), Composer 2, Node.js 22 com npm e XAMPP/MySQL. Coloque PHP, Composer e Node no PATH. Não copie dependências instaladas ou .env de outra máquina.

Use os fontes atualizados desta entrega. A pasta original é uma cópia sem .git; a publicação foi preparada em clone separado. O estado da entrega está em CONTINUACAO.md. Ao trabalhar pelo Git, preserve o histórico de [guibirlem/motocare](https://github.com/guibirlem/motocare) e crie uma branch de trabalho a partir da versão recebida.

### Backend

Inicie MySQL no XAMPP e crie o banco com [create-local-database.sql](backend/database/create-local-database.sql). Padrão: 127.0.0.1:3306, banco motocare, root, senha vazia.

Na pasta backend:

~~~powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
composer install
# Somente na primeira instalação, se APP_KEY ainda estiver vazia:
php artisan key:generate
php artisan migrate
php artisan push:configurar
php artisan serve --host=127.0.0.1 --port=8000
~~~

Preserve APP_KEY e o .env existentes em atualizações. Confira as configurações DB_* do .env. As migrations de despesas e inscrição Web Push são aditivas e mantêm os registros anteriores; não use migrate:fresh em banco com dados. Raiz da API: http://127.0.0.1:8000, resposta JSON.

### Frontend

Em outro terminal, na pasta frontend:

~~~powershell
npm ci
npm start
~~~

Abra [MotoCare](http://127.0.0.1:5173). Esse comando compila e serve o build com proxy /api para o backend local. Foi criado para evitar a falha do esbuild ao carregar configuração/otimizar dependências neste ambiente. Após editar fontes, encerre com Ctrl+C e execute npm start novamente.

Para desenvolvimento com recarga automática, use npm run dev em um ambiente onde a otimização do Vite funcione. O proxy de dev também aponta para 127.0.0.1:8000. Não é necessário .env no frontend.

~~~powershell
npm run build
npm run preview
~~~

O build/preview usa a API programática do Vite e a configuração existente, sem depender do runtime interno do assistente. Porta do preview: 5173, com strictPort (encerre o processo anterior para liberar a porta). Se houver tela antiga, use Ctrl+F5. O shell é atualizado por versão do build.

### Primeiro uso

Crie sua conta, abra Garagem e cadastre sua moto. Selecione a moto no topo; depois registre manutenções e alertas. Atualize o odômetro na Garagem para verificar os limites. Não há conta ou senha padrão, nem dados demonstrativos inseridos.

| Tela | Rota |
| --- | --- |
| Dashboard | / ou /dashboard |
| Histórico | /manutencoes ou /historico |
| Garagem | /garagem |
| Conta e notificações | /configuracoes |
| Alertas | /alertas |

O Dashboard consulta diretamente o histórico e os alertas. O total de manutenções soma apenas serviços concluídos. Manutenção feita pode avançar o odômetro, nunca reduzi-lo. Conclua o alerta e cadastre o próximo limite explicitamente; informe o KM de referência do serviço para calcular o intervalo de aviso.

### Scheduler e notificações

Validação real em 22/10: teste e alerta de óleo do scheduler recebidos com Chrome minimizado, além do primeiro plano, confirmados pelo usuário. Limite inclusivo de 90% e ausência de reenvio conferidos no MySQL; fixtures removidas. O teste da interface continua limitado a três tentativas/minuto, com Retry-After em português e espera regressiva. API precisa de acesso externo ao serviço de push; não desabilitar TLS nem remover o limite. Reiniciar servidores se as portas 5173/8000 recusarem conexão; após novo build, Ctrl+F5. Foi executado schedule:run avulso; para uso diário manter o scheduler abaixo.

Em outro terminal na pasta backend:

~~~powershell
php artisan schedule:work
~~~

Verificação manual: php artisan alertas:verificar. O comando php artisan push:configurar gera as chaves locais uma única vez, sem conta externa; preserva chaves existentes. Em Configurações, ative este navegador e use Enviar teste de notificação para conferir recebimento. Operação em [ALERTAS.md](backend/ALERTAS.md). Preserve APP_KEY e storage/app/private/webpush-vapid.json em atualizações; não publique chaves privadas. Em produção: HTTPS, cookies seguros, reverse proxy /api na mesma origem e WEBPUSH_SUBJECT com seu contato válido.

## Testes

Na pasta backend:

~~~powershell
# Se necessário no XAMPP:
$env:OPENSSL_CONF = 'C:/xampp/php/extras/ssl/openssl.cnf'
php artisan test
~~~

Na pasta frontend:

~~~powershell
npm test
npm run build
~~~

Validação do escopo T1/T2 em 19/10/2026: 29 testes backend/246 verificações, 19 testes frontend e build aprovados. Smoke HTTP no MySQL confirmou configuração Web Push pronta, inscrição/remoção, CRUD dos quatro tipos de serviço, ciclo de alerta e financeiro desativado (404); dados de teste removidos. Testes financeiros históricos habilitam a funcionalidade apenas no próprio caso, para preservar o trabalho futuro. Testes frontend cobrem push/ativação/logout e cálculos/CSV/cache; isso não significa que financeiro está ativo.

## Publicação e continuidade

Versionar fontes, documentação, migrations, exemplos e lockfiles. Não versionar .env, chaves privadas, dependências instaladas, caches, logs ou dist. Preservar o histórico remoto, sem force push. A integração GitHub recusou gravação com403; esta edição foi publicada pelo Git instalado, contornando esse bloqueio. Conteúdo e procedimento de atualização em [ENTREGA.md](ENTREGA.md).

- [AGENTS.md](AGENTS.md): regras do projeto.
- [SPEC.md](SPEC.md): regras e contrato da API.
- [DER.md](DER.md): entidades e relações.
- [TASKS.md](TASKS.md): tarefas e validações.
- [CONTINUACAO.md](CONTINUACAO.md): estado atual e operação.
- [MEMORY.md](MEMORY.md): histórico das entregas.
- [RETOMADA_T3.md](RETOMADA_T3.md): mapa do financeiro preservado, sequência futura e critérios de aceite.

Arquivo acadêmico consolidado: [PDF da entrega T1/T2](output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf), retrato de 22/10/2026. Atualizar sua edição quando uma nova entrega incluir a T3; ele não substitui o roteiro técnico atual.

Para confirmar a T1 no navegador: Configurações → Ativar notificações → permitir → Enviar teste de notificação. As chaves locais já foram geradas nesta máquina. Testes automatizados e HTTP não comprovam recebimento real.
