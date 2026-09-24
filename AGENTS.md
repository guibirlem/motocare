# AGENTS.md - Constituição do Projeto MotoCare

## Propósito
Este arquivo define a governança do projeto MotoCare para agentes de IA e desenvolvedores humanos. As regras aqui descritas têm prioridade sobre decisões pontuais de implementação.

## Visão do Projeto
MotoCare é um Progressive Web App (PWA) para acompanhamento de manutenção preventiva, avisos de revisão e controle financeiro de motociclistas.

## Stack Tecnológica
- Frontend: Vue.js 3 com Vite, Axios, Service Workers e suporte a PWA.
- Backend: PHP 8.x com Laravel 10+ como API RESTful desacoplada.
- Banco de dados: MySQL 8.0 com InnoDB e integridade referencial.
- Notificações: Firebase Cloud Messaging (FCM).
- Ambiente local do banco: XAMPP em 127.0.0.1:3306, usuário root, senha vazia.

## Comandos Padrão de Execução
- Backend: `cd backend && php artisan serve`
- Frontend: `cd frontend && npm run dev`

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