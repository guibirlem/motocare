# CONTINUACAO.md - Handoff do Projeto MotoCare

## Estado Atual
O repositório foi iniciado do zero e a etapa de governança SDD foi concluída. O scaffold base do backend e do frontend já existe e o frontend foi validado com build de produção.

## O que foi feito

### Governança SDD
- Criados os arquivos de contexto e governança na raiz:
  - [AGENTS.md](AGENTS.md)
  - [SPEC.md](SPEC.md)
  - [TASKS.md](TASKS.md)
  - [MEMORY.md](MEMORY.md)
- Criado [\.gitignore](.gitignore) com exclusões para `backend/vendor`, `backend/node_modules`, `frontend/node_modules`, `.env` e arquivos de build/log.

### Backend
- Criado o projeto Laravel em [backend](backend).
- Ajustado [backend/.env](backend/.env) para MySQL do XAMPP:
  - `DB_CONNECTION=mysql`
  - `DB_HOST=127.0.0.1`
  - `DB_PORT=3306`
  - `DB_DATABASE=motocare`
  - `DB_USERNAME=root`
  - `DB_PASSWORD=`
- Adaptado o modelo autenticável para usar a tabela `usuarios` em [backend/app/Models/User.php](backend/app/Models/User.php).
- Adaptada a factory de usuário em [backend/database/factories/UserFactory.php](backend/database/factories/UserFactory.php).
- Atualizada a migration base de usuários para `usuarios` em [backend/database/migrations/0001_01_01_000000_create_users_table.php](backend/database/migrations/0001_01_01_000000_create_users_table.php).
- Criadas as migrations do domínio:
  - [backend/database/migrations/2026_09_24_000001_create_motocicletas_table.php](backend/database/migrations/2026_09_24_000001_create_motocicletas_table.php)
  - [backend/database/migrations/2026_09_24_000002_create_manutencoes_table.php](backend/database/migrations/2026_09_24_000002_create_manutencoes_table.php)
  - [backend/database/migrations/2026_09_24_000003_create_despesas_table.php](backend/database/migrations/2026_09_24_000003_create_despesas_table.php)
  - [backend/database/migrations/2026_09_24_000004_create_alertas_table.php](backend/database/migrations/2026_09_24_000004_create_alertas_table.php)

### Frontend
- Criado o projeto Vue 3/Vite em [frontend](frontend).
- Adicionadas dependências:
  - `vue`
  - `vue-router`
  - `axios`
- Estrutura inicial criada em:
  - [frontend/index.html](frontend/index.html)
  - [frontend/vite.config.js](frontend/vite.config.js)
  - [frontend/src/main.js](frontend/src/main.js)
  - [frontend/src/App.vue](frontend/src/App.vue)
  - [frontend/src/router/index.js](frontend/src/router/index.js)
  - [frontend/src/views/HomeView.vue](frontend/src/views/HomeView.vue)
  - [frontend/src/styles/main.css](frontend/src/styles/main.css)
  - [frontend/public/manifest.webmanifest](frontend/public/manifest.webmanifest)
  - [frontend/public/sw.js](frontend/public/sw.js)
- O build de produção do frontend passou com sucesso usando `npm run build`.

### Validação
- O backend foi gerado com Laravel 12.x porque a versão mais recente exigia PHP 8.3, incompatível com o ambiente atual.
- Houve bloqueio temporário de extração no Windows durante a instalação inicial do Composer, mas o projeto foi concluído e o frontend ficou validado.
- Foi removido o aviso de import não utilizado no modelo [backend/app/Models/User.php](backend/app/Models/User.php).

## Pendências para continuar depois
- Criar o banco `motocare` no MySQL do XAMPP, se ainda não existir.
- Executar as migrations do backend com `php artisan migrate` dentro de [backend](backend).
- Implementar autenticação com Laravel Sanctum.
- Criar controllers, models e endpoints REST para:
  - usuários
  - motocicletas
  - manutenções
  - despesas
  - alertas
- Implementar a lógica de alertas automáticos quando `km_atual` atingir 90% do limite.
- Implementar histórico de manutenções e resumo financeiro mensal.
- Integrar envio de push com FCM.
- Evoluir o frontend para consumir a API real do backend.

## Comandos de retomada

### Backend
```bash
cd backend
php artisan serve
php artisan migrate
```

### Frontend
```bash
cd frontend
npm run dev
```

## Observações importantes
- O backend deve permanecer exclusivamente como API RESTful.
- Não criar views Blade.
- Qualquer mudança no schema precisa ser refletida na SPEC e nas migrations correspondentes.
- Para trabalhar no Windows, use caminho absoluto ao chamar o terminal quando o diretório atual puder ficar preso em um estado anterior.