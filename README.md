# MotoCare 🏍️

O **MotoCare** é um aplicativo web progressivo (PWA) para ajudar motociclistas a cuidar da moto e acompanhar os custos de uso. O sistema reúne manutenção, abastecimentos, despesas e lembretes em um só lugar, acessível pelo computador ou celular.

## Nossa ideia

Quem depende da moto no dia a dia precisa lembrar quando fazer revisões e trocar componentes, mas essas informações muitas vezes ficam espalhadas em anotações, recibos ou na memória. Também é difícil perceber quanto se gasta com combustível, reparos e manutenção ao longo do tempo.

O MotoCare busca tornar esse cuidado mais simples: a pessoa cadastra sua moto, registra o que foi feito e acompanha os próximos cuidados. Com o histórico e os gastos organizados, fica mais fácil planejar a manutenção e entender o custo de uso da motocicleta.

## Funcionalidades

- **Conta e garagem:** criar uma conta e cadastrar uma ou mais motocicletas, com seus dados e odômetro.
- **Painel e histórico:** acompanhar atividades recentes em linha do tempo ou tabela, com resumos por período.
- **Manutenções:** registrar serviços preventivos ou corretivos, como troca de óleo, freios, pneus, corrente e revisão; guardar valor, data, quilometragem e status.
- **Alertas e lembretes:** configurar avisos por data ou quilometragem para serviços e itens como seguro, IPVA e licenciamento. O sistema verifica os limites e pode enviar notificações Web Push ao navegador inscrito.
- **Gastos e abastecimentos:** registrar combustível, reparos e outras despesas; acompanhar litros, preço, posto e quilometragem. É possível guardar postos favoritos e preços por combustível.
- **Consumo e autonomia:** consultar estimativas com base nos abastecimentos registrados, incluindo ciclos entre tanques cheios e uma estimativa de reserva quando há dados suficientes.
- **Relatórios:** filtrar lançamentos por período e motocicleta, consultar totais e exportar CSV ou imprimir/salvar pelo navegador em PDF.
- **PWA e uso móvel:** instalar o app em dispositivos compatíveis, alternar entre temas claro e escuro e abrir o shell da interface offline. Dados pessoais e operações de gravação precisam de conexão.
- **Recuperação de senha:** solicitar um link temporário de redefinição. O envio depende da configuração de um serviço de e-mail (SMTP).

O projeto não inclui dados demonstrativos ou uma conta padrão. Cada integrante cria sua própria conta na primeira execução.

## Tecnologias

| Camada | Tecnologias |
| --- | --- |
| Interface | Vue 3, Vite, JavaScript, Axios e Service Worker/PWA |
| API | PHP 8.2+, Laravel 12 e respostas JSON |
| Banco local recomendado | MySQL 8 / XAMPP, InnoDB |
| Testes | PHPUnit no backend e Node.js Test Runner no frontend |

O frontend e a API são separados. Durante o desenvolvimento local, o frontend encaminha as chamadas `/api` para a API Laravel.

## Como executar localmente

### Requisitos

- Windows 10/11 (os comandos abaixo usam PowerShell), ou ambiente equivalente;
- PHP 8.2 ou superior com extensões `curl`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite` e `zip`;
- Composer 2;
- Node.js 22 e npm;
- XAMPP com MySQL/MariaDB para o banco local.

Adicione PHP, Composer e Node.js ao `PATH`. No XAMPP, inicie o MySQL. A configuração de exemplo usa `127.0.0.1:3306`, banco `motocare`, usuário `root` e senha vazia. Se sua instalação usa outra credencial, ajuste o `.env`.

### 1. Preparar o banco e o backend

No PowerShell, a partir da pasta do projeto:

```powershell
cd backend
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
composer install
```

Confira no `backend/.env` os valores de `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD`. Crie o banco `motocare` (por exemplo, no phpMyAdmin) ou execute o arquivo [`backend/database/create-local-database.sql`](backend/database/create-local-database.sql).

Na primeira instalação, gere a chave da aplicação somente se `APP_KEY` estiver vazia. Não execute `key:generate` novamente em uma instalação com dados, pois trocar a chave pode tornar dados criptografados inacessíveis. Depois, aplique as migrations e prepare as chaves locais de Web Push:

```powershell
# Execute somente se APP_KEY estiver vazia na primeira instalação:
php artisan key:generate
php artisan migrate
php artisan push:configurar
```

Preserve `.env`, `APP_KEY` e `backend/storage/app/private/webpush-vapid.json` em atualizações. O comando `push:configurar` cria as chaves VAPID locais apenas quando necessário. Não use `migrate:fresh` em um banco que contenha dados: esse comando apaga as tabelas.

Inicie a API:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

A API fica em `http://127.0.0.1:8000` e entrega JSON.

### 2. Preparar e iniciar o frontend

Abra outro terminal PowerShell na raiz do projeto:

```powershell
cd frontend
npm ci
npm start
```

Abra [http://127.0.0.1:5173](http://127.0.0.1:5173). O comando `npm start` compila a interface e serve o build local, encaminhando `/api` para o backend em `127.0.0.1:8000`. Para interromper, use `Ctrl+C`. Depois de alterar fontes, reinicie `npm start` para gerar e servir o build atualizado. Em ambientes onde a otimização do Vite funciona normalmente, `npm run dev` também inicia o servidor de desenvolvimento com recarga automática.

### 3. Iniciar os lembretes agendados

Para que a verificação automática de alertas continue rodando durante o uso local, abra um terceiro terminal na pasta `backend` e mantenha este comando ativo:

```powershell
php artisan schedule:work
```

Se não iniciar o scheduler, os recursos de cadastro e consulta continuam disponíveis, mas os avisos que dependem da verificação agendada não serão processados automaticamente. Para testar uma execução manual, use `php artisan alertas:verificar`.

### 4. Primeiro acesso

Crie sua conta na tela inicial, cadastre uma moto em **Garagem** e selecione-a no painel. Depois, registre uma manutenção, configure alertas e adicione abastecimentos ou despesas. Não existe usuário ou senha padrão.

| Área | Rota |
| --- | --- |
| Apresentação pública / painel | `/` ou `/dashboard` |
| Histórico e manutenções | `/manutencoes` ou `/historico` |
| Gastos e abastecimentos | `/gastos` |
| Relatórios | `/relatorios` |
| Garagem | `/garagem` |
| Conta e notificações | `/configuracoes` |
| Alertas | `/alertas` |

Para testar notificações, use **Configurações → Ativar notificações**, permita o acesso do navegador e escolha **Enviar teste de notificação**. Em uma implantação pública, Web Push exige HTTPS e um `WEBPUSH_SUBJECT` válido. O `.env.example` usa `MAIL_MAILER=log`; portanto, recuperação de senha não envia e-mail real até que um SMTP seja configurado. Veja [`backend/ALERTAS.md`](backend/ALERTAS.md) e [`RECUPERACAO_SENHA.md`](RECUPERACAO_SENHA.md).

## Testes e build

Backend, na pasta `backend`:

```powershell
# No XAMPP/Windows, defina isto se a geração de chaves OpenSSL apresentar erro:
$env:OPENSSL_CONF = 'C:/xampp/php/extras/ssl/openssl.cnf'
php artisan test
```

Frontend, na pasta `frontend`:

```powershell
npm test
npm run build
```

O financeiro e os relatórios podem ser controlados pela configuração `FEATURE_FINANCEIRO` no `.env` do backend; por padrão, estão habilitados.

## Estrutura do repositório

```text
backend/        API Laravel, migrations, comandos e testes PHPUnit
frontend/       Aplicação Vue, PWA, testes e build Vite
SPEC.md         Regras funcionais e contratos
DER.md          Modelo de dados e relacionamentos
TASKS.md        Backlog e estado das tarefas
CONTINUACAO.md  Orientações para retomar o desenvolvimento
MEMORY.md       Histórico de entregas e decisões
```

## Documentação do projeto

- [Especificação funcional](SPEC.md)
- [Diagrama entidade-relacionamento](DER.md)
- [Backlog e validações](TASKS.md)
- [Alertas e Web Push](backend/ALERTAS.md)
- [Recuperação de senha e SMTP](RECUPERACAO_SENHA.md)
- [Continuidade e operação](CONTINUACAO.md)
- [Histórico de validações da T3](VALIDACAO_T3.md)
- [Repositório GitHub](https://github.com/guibirlem/motocare)

## Segurança e dados

Não versionamos `.env`, chaves privadas, dependências instaladas, caches, logs ou arquivos de build. Não compartilhe credenciais ou dados pessoais em issues ou commits. As migrations atualizam o schema mantendo os dados existentes; antes de qualquer mudança estrutural, consulte a SPEC e o DER.
