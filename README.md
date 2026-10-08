# MotoCare

PWA para acompanhar manutenção preventiva, alertas e gastos de motocicletas. Frontend Vue 3/Vite separado da API Laravel 12, com banco MySQL local no XAMPP.

## Estado da entrega

- T0: estrutura e migrations do domínio disponíveis.
- T1: verificador de alertas aos 90% do limite, scheduler, envio FCM HTTP v1 e testes automatizados no backend. Push real depende de credenciais Firebase e token do dispositivo.
- Frontend: telas demonstrativas inspiradas no [protótipo publicado](https://open-thumb-85089078.figma.site), com rotas e navegação. Os formulários não persistem dados; autenticação e consumo da API ainda não foram implementados.
- T2: próxima tarefa, implementar o histórico real de manutenções e peças de desgaste. Consulte o [handoff](CONTINUACAO.md) antes de começar.
- T3: resumo financeiro real ainda pendente.

## Preparar outro computador

Instale Git, PHP 8.2 ou superior compatível com o lockfile, Composer 2 e Node.js 22 com npm. Para o banco local, inicie o MySQL no XAMPP. O PHP precisa das extensões exigidas pelo Composer; os testes também usam `pdo_sqlite`, e a integração FCM usa `openssl`.

No Windows, configure o PHP no PATH (por exemplo, `C:\xampp\php`) para usar os comandos abaixo. `php --version`, `composer --version`, `node --version` e `npm --version` devem responder no terminal. Não é necessário copiar dependências ou executáveis do computador anterior.

### 1. Obter o projeto

```powershell
git clone https://github.com/guibirlem/motocare.git
cd motocare
```

O repositório do projeto é [guibirlem/motocare](https://github.com/guibirlem/motocare), cuja branch padrão atual é `master`. A pasta correta contém `backend`, `frontend` e este README. Para a T2, crie a branch de trabalho a partir da versão atualizada dessa branch.

### 2. Criar o banco local

Com o MySQL do XAMPP iniciado, use o phpMyAdmin ou outro cliente SQL para executar [backend/database/create-local-database.sql](backend/database/create-local-database.sql). O script cria o banco `motocare` se ele ainda não existir. As tabelas serão criadas pelas migrations.

O padrão de desenvolvimento é `127.0.0.1:3306`, usuário `root`, senha vazia. Nenhum banco ou registro do outro computador é necessário para instalar; as telas usam exemplos locais e os testes criam dados próprios.

### 3. Instalar e iniciar o backend

Na raiz do projeto, usando PowerShell:

```powershell
cd backend
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
composer install
php artisan key:generate
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

O `.env.example` já contém a configuração do MySQL local. Ajuste o `.env` se seu banco tiver outra porta, usuário ou senha. Cada computador deve gerar sua própria `APP_KEY`; não copie o `.env` de outra pessoa. O comando `key:generate` acima é para a primeira instalação; preserve a chave existente nas próximas atualizações.

API de verificação: [http://127.0.0.1:8000](http://127.0.0.1:8000), com resposta JSON. Não há endpoints de manutenção ou autenticação prontos nesta entrega.

### 4. Instalar e iniciar o frontend

Abra outro terminal na raiz do projeto:

```powershell
cd frontend
npm ci
npm run dev -- --host 127.0.0.1 --port 5173
```

Abra [http://127.0.0.1:5173](http://127.0.0.1:5173). Use npm e mantenha `package-lock.json`; não misture gerenciadores de dependências.

| Tela | Rota |
| --- | --- |
| Dashboard | `/` ou `/dashboard` |
| Alertas da T1 | `/alertas` |
| Manutenções | `/manutencoes` ou `/historico` |
| Gastos | `/gastos` |
| Relatórios | `/relatorios` |
| Garagem | `/garagem` |
| Configurações | `/configuracoes` |

`frontend/.env.example` reserva `VITE_API_URL` para a futura integração. Atualmente a interface não consulta essa variável nem a API; não é necessário criar um `.env` no frontend para visualizar as telas.

### 5. Executar alertas da T1, se necessário

Em outro terminal dentro de `backend`:

```powershell
php artisan schedule:work
```

Verificação manual: `php artisan alertas:verificar`. Consulte [backend/ALERTAS.md](backend/ALERTAS.md) para configurar Firebase e tokens. Credenciais não são necessárias para desenvolver o histórico da T2 ou executar os testes com FCM simulado.

## Validar alterações

Dentro de `backend`:

```powershell
php artisan test
```

Os testes usam SQLite em memória, sem modificar o MySQL local. Se o teste de FCM não conseguir gerar a chave OpenSSL no XAMPP, ajuste o caminho da configuração existente na sua instalação antes de executar os testes:

```powershell
$env:OPENSSL_CONF = 'C:/xampp/php/extras/ssl/openssl.cnf'
php artisan test
```

Dentro de `frontend`:

```powershell
npm run build
```

Últimas validações da implementação: backend com 6 testes e 27 verificações; frontend com build aprovado e sete rotas principais respondendo HTTP 200. A verificação visual automatizada no navegador não foi concluída.

## Continuar a T2

Leia [CONTINUACAO.md](CONTINUACAO.md), [AGENTS.md](AGENTS.md), [SPEC.md](SPEC.md) e [TASKS.md](TASKS.md). A tabela `manutencoes` já existe nas migrations, mas não há model, controller ou API de manutenções. A tela [MaintenanceView.vue](frontend/src/views/MaintenanceView.vue) e os dados em [demo.js](frontend/src/data/demo.js) são o ponto de partida visual.

Crie sua branch de trabalho antes de implementar:

```powershell
git switch -c feature/t2-historico-manutencoes
```

Não use `migrate:fresh` no banco compartilhado para preparar a T2; `php artisan migrate` aplica as migrations pendentes sem apagar as tabelas existentes.

## Publicar este projeto no Git

O envio do código fica a cargo do responsável pelo repositório. Caso esta pasta ainda não tenha `.git`, na raiz:

```powershell
git init -b main
git add .
git status --short
git diff --cached --stat
git commit -m "feat: implementa T1 e prototipo navegavel MotoCare"
git remote add origin <URL-DO-REPOSITORIO>
git push -u origin main
```

Esses comandos são para um repositório remoto vazio. Se ele já tiver histórico, faça o envio a partir de um clone desse repositório; preserve os commits existentes. Se esta pasta já for um clone, não repita `git init` ou `git remote add`.

Versione o código, migrations, documentação, `.env.example`, `backend/composer.lock` e `frontend/package-lock.json`. O `.gitignore` exclui os arquivos locais, incluindo `.env`, credenciais de conta de serviço, `vendor`, `node_modules`, caches, logs, builds, o Composer local e os arquivos auxiliares de pnpm. Ele não remove arquivos que já estejam rastreados; confira `git status` e os arquivos preparados antes do commit.

## Documentação

- [AGENTS.md](AGENTS.md): regras do projeto e da colaboração.
- [SPEC.md](SPEC.md): regras de negócio, entidades e DER.
- [TASKS.md](TASKS.md): backlog e estados reais das tarefas.
- [CONTINUACAO.md](CONTINUACAO.md): contexto e orientações para a próxima pessoa.
- [MEMORY.md](MEMORY.md): registro das entregas e validações.
