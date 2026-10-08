# CONTINUACAO.md - Handoff do Projeto MotoCare

## Estado em 08/10/2026

O projeto está preparado para ser versionado e reinstalado em outro computador. O backend contém a lógica da T1; o frontend foi redesenhado com base no protótipo publicado e continua demonstrativo. A próxima entrega funcional é a T2.

A instalação completa está no [README.md](README.md). O ambiente do outro computador será criado com os lockfiles, exemplos de configuração e migrations; não depende de copiar o banco ou as dependências desta máquina.

## O que está implementado

### Backend

- Laravel 12.x, PHP 8.2+ e MySQL local do XAMPP.
- Migrations de `usuarios`, `motocicletas`, `manutencoes`, `despesas` e `alertas`, além das tabelas de infraestrutura do Laravel.
- Modelo autenticável [User.php](backend/app/Models/User.php) usando a tabela `usuarios`.
- Models [Motocicleta.php](backend/app/Models/Motocicleta.php) e [Alerta.php](backend/app/Models/Alerta.php).
- Comando [CheckMaintenanceAlerts.php](backend/app/Console/Commands/CheckMaintenanceAlerts.php): `php artisan alertas:verificar`.
- Scheduler em [routes/console.php](backend/routes/console.php), com verificação a cada minuto.
- Serviço [FcmService.php](backend/app/Services/FcmService.php): OAuth 2.0 e FCM HTTP v1.
- Tipos da T1: `troca_oleo` e `revisao`. Gatilho inclusivo aos 90% do limite absoluto do odômetro.
- O alerta passa de `pendente` para `notificado` após aceite do FCM. Falhas e ausência de token preservam `pendente`; locks evitam envios simultâneos.
- Rota raiz do backend responde JSON. Não há endpoints de CRUD ou autenticação implementados.

Configuração e limitações do push: [backend/ALERTAS.md](backend/ALERTAS.md). O envio real requer conta de serviço Firebase e `usuarios.fcm_token`. O frontend ainda não solicita permissão nem registra tokens.

### Frontend

- Vue 3, Vue Router, Vite e Axios instalado.
- Referência: [protótipo publicado](https://open-thumb-85089078.figma.site).
- Cabeçalho e sidebar pretos, destaque verde-lima `#AADF00`, conteúdo claro e fonte Arial.
- Componentes reutilizáveis em [frontend/src/components](frontend/src/components).
- Rotas em [router/index.js](frontend/src/router/index.js): `/`, `/dashboard`, `/alertas`, `/manutencoes`, `/historico`, `/gastos`, `/relatorios`, `/garagem` e `/configuracoes`.
- Dados de exemplo centralizados em [demo.js](frontend/src/data/demo.js).
- A navegação, a busca e os filtros servem para explorar o protótipo.
- Formulários não gravam, alteram ou excluem registros. Login, troca de senha, exportação PDF, finanças e histórico ainda não estão integrados à API.
- Manifest e service worker disponíveis; não houve validação completa de uso offline ou entrega de push no navegador.

## Orientação para quem vai fazer a T2

### Escopo

Implementar o histórico real de manutenções e peças de desgaste, cobrindo óleo, kit relação, pneus e freios. Use as regras de [SPEC.md](SPEC.md) e os critérios de [TASKS.md](TASKS.md).

### Pontos de partida

- Migration existente: [create_manutencoes_table.php](backend/database/migrations/2026_09_24_000002_create_manutencoes_table.php).
- A tabela já tem `motocicleta_id`, `tipo_servico`, `descricao`, `valor`, `status`, `data` e `km_servico`.
- Ainda não existem `Manutencao`, controller de manutenção ou `routes/api.php`. Ao criar a API, registre as rotas no bootstrap do Laravel 12.
- Tela visual: [MaintenanceView.vue](frontend/src/views/MaintenanceView.vue). Atualmente ela importa dados de `demo.js`, usa rótulos `Feito`/`Pendente` e não persiste as ações.
- `frontend/.env.example` reserva `VITE_API_URL=http://127.0.0.1:8000/api` para a futura integração. Atualmente nenhuma chamada utiliza essa variável.
- A autenticação com Sanctum ainda está pendente. Defina o acesso aos registros do próprio usuário antes de disponibilizar endpoints com dados reais; não aceite o dono da moto apenas por um ID enviado pelo cliente.
- Preserve o funcionamento e os estados dos alertas da T1. Não altere o limite de serviço ou reative alertas notificados automaticamente sem definir essa regra na SPEC.

### Sequência sugerida

1. Reinstalar o projeto seguindo o README e criar a branch `feature/t2-historico-manutencoes`.
2. Conferir a migration e implementar model, relacionamentos, validação e endpoints JSON do histórico.
3. Integrar a tela de manutenções à API, traduzindo os rótulos e datas visuais para o contrato definido.
4. Tratar carregamento, histórico vazio e erros de validação.
5. Testar a persistência, o vínculo com a moto e o isolamento dos dados por usuário, além de executar os testes da T1 e o build.
6. Atualizar TASKS, SPEC quando necessário, este handoff e MEMORY com o resultado real.

A T3 continua pendente; suas telas visuais não representam uma implementação do financeiro.

## Configuração em outro computador

- Copiar `backend/.env.example` para `backend/.env` apenas na primeira instalação.
- Padrão do banco: MySQL em `127.0.0.1:3306`, banco `motocare`, usuário `root`, senha vazia.
- Criar o banco usando [create-local-database.sql](backend/database/create-local-database.sql), gerar a chave Laravel e executar `php artisan migrate`.
- Restaurar dependências com `composer install` e `npm ci`.
- Iniciar API e frontend em terminais separados; executar o scheduler em um terceiro terminal se for testar alertas.
- Não copiar credenciais, tokens reais ou arquivos locais para o repositório.

## Validações conhecidas

- Backend da T1: 6 testes passaram, com 27 verificações, usando SQLite em memória e FCM simulado.
- Preparação para transferência: Composer válido, lockfile npm coerente, links locais conferidos e auditoria do Git com exemplos/lockfiles incluídos e arquivos locais protegidos.
- Frontend redesenhado: build de produção aprovado; sete rotas principais e os componentes das telas responderam HTTP 200 no servidor local.
- A verificação visual automatizada no navegador foi interrompida porque a ferramenta não conseguiu confirmar a URL. Não confundir build aprovado com revisão visual completa.
- Push real no Firebase e autenticação não foram validados de ponta a ponta.

## Preparação para o Git

A pasta local foi recebida como cópia sem `.git`. O destino autorizado desta entrega é [guibirlem/motocare](https://github.com/guibirlem/motocare), na branch padrão `master`, preservando os commits anteriores. Para continuar em outro computador, clone o repositório e crie `feature/t2-historico-manutencoes` a partir da versão atualizada de `master`. O envio desta cópia pela integração GitHub não cria um repositório Git na pasta local.

Versionar documentação, código, migrations, exemplos e lockfiles. O `.gitignore` preserva os arquivos locais nesta máquina, mas impede que `.env`, contas de serviço, dependências, caches, builds e a referência baixada do protótipo entrem no primeiro commit.
