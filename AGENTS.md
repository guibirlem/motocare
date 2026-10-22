# AGENTS.md - Constituição do Projeto MotoCare

## Propósito
Este arquivo define a governança do projeto MotoCare para agentes de IA e desenvolvedores humanos. As regras aqui descritas têm prioridade sobre decisões pontuais de implementação.

## Visão do Projeto
MotoCare é um Progressive Web App (PWA) para acompanhamento de manutenção preventiva, avisos de revisão e controle financeiro de motociclistas.

## Stack Tecnológica
- Frontend: Vue.js 3 com Vite, Axios, Service Workers e suporte a PWA.
- Backend: PHP 8.2+ com Laravel 12.x (versão definida no `backend/composer.json`) como API RESTful desacoplada.
- Banco de dados: MySQL 8.0 com InnoDB e integridade referencial.
- Notificações: Web Push padrão com VAPID gerado localmente, sem conta/SDK Firebase (decisão do usuário em 19/10/2026).
- Ambiente local do banco: XAMPP em 127.0.0.1:3306, usuário root, senha vazia.

## Comandos Padrão de Execução
- Instalação em outro computador: seguir o [README.md](README.md), usando `composer install` no backend e `npm ci` no frontend.
- Backend: `cd backend && php artisan serve`
- Frontend: `cd frontend && npm run dev`
- Scheduler da T1, em outro terminal dentro de `backend`: `php artisan schedule:work`.
- Dependências devem ser restauradas a partir de `backend/composer.lock` e `frontend/package-lock.json`; o gerenciador padrão do frontend é npm.

## Configuração do Backend
O arquivo `backend/.env` deve ser configurado para o MySQL do XAMPP com os seguintes valores mínimos:
- `DB_DATABASE=motocare`
- `DB_USERNAME=root`
- `DB_PASSWORD=`

## Convenções de Código
- Use `camelCase` para variáveis e funções.
- Use `PascalCase` para componentes e classes.
- Prefira métodos assíncronos com `async/await` quando houver operações assíncronas.

## Convenções de Git
- Commits semânticos: `feat:`, `fix:`, `docs:`, `refactor:`, `test:`.
- Branches: `main`, `develop` e `feature/nome-da-tarefa`.

## Restrições de Arquitetura
- O backend é exclusivamente uma API RESTful.
- É proibido criar views em Blade.
- Não alterar o schema do banco sem atualização correspondente do DER e da SPEC.

## Critérios de Trabalho
- Antes de mudar estrutura de dados, validar impacto nas migrations e na SPEC.
- Preservar separação clara entre frontend e backend.
- Priorizar entregas incrementais, rastreáveis e testáveis.
- Antes de retomar uma tarefa, ler `CONTINUACAO.md`, `TASKS.md`, `SPEC.md` e `MEMORY.md`.
- Ao entregar uma tarefa, registrar estado e validações em `TASKS.md`, `CONTINUACAO.md` e `MEMORY.md`. Atualizar `SPEC.md` quando houver mudança de regra ou contrato; atualizar o DER e as migrations quando houver mudança estrutural.
- Não inferir implementação pela existência de uma tela: confirmar integração, persistência e validações em TASKS.md. A entrega integrada de 19/10/2026 substitui os dados demonstrativos nas telas.
- Não versionar `.env`, chaves privadas e credenciais, dependências instaladas, caches, logs ou arquivos de build. Versionar os exemplos de configuração e os lockfiles.
