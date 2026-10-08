# TASKS.md - Backlog Atômico da Sprint 1

## T0 [Must Have]

- Scaffold Laravel em `/backend`, Vue 3/Vite com base PWA em `/frontend` e migrations do domínio.
- Status: concluído no ambiente original.
- Reinstalação em outro computador: [README.md](README.md), usando MySQL do XAMPP, Composer e npm.

## T1 [US01 - Must Have]

- Alertas automatizados de Troca de Óleo e Revisões.
- Notificação push quando `km_atual` atingir 90% do limite.
- Status: backend implementado e validado; frontend demonstrativo entregue no escopo solicitado.
- Disponíveis: scheduler a cada minuto, FCM HTTP v1, prevenção de envio simultâneo, preservação de alertas pendentes em falhas e testes automatizados.
- Rota visual: `/alertas`, acessível pelo menu e Dashboard.
- Push real no cliente ainda depende de credenciais Firebase, permissão de notificações e registro de token. Autenticação e captura de token no frontend permanecem pendentes.
- Operação: [backend/ALERTAS.md](backend/ALERTAS.md).

## T2 [US02 - Must Have] — próxima entrega

- Histórico de manutenções e peças de desgaste.
- Cobrir óleo, kit relação, pneus e freios.
- Status: implementação funcional pendente. A tabela e a tela demonstrativa já existem.
- Base visual: `frontend/src/views/MaintenanceView.vue`, rota `/manutencoes` (alias `/historico`).
- Base de dados: migration `2026_09_24_000002_create_manutencoes_table.php`; não recriar a tabela sem necessidade.
- Entrega esperada:
  - Model e relacionamentos de manutenção.
  - Endpoints JSON e validação para registrar e consultar o histórico vinculado à motocicleta.
  - Acesso restrito aos registros do próprio usuário, com autenticação a definir.
  - Campos de serviço, descrição, valor, status, data e quilometragem conforme SPEC.
  - Integração da tela com persistência real, incluindo estados de carregamento, vazio e erro.
  - Testes de persistência e vínculo/isolamento por usuário; regressão dos alertas da T1 e build do frontend aprovados.
- Registrar o contrato final na SPEC e atualizar migrations/DER somente se houver mudança estrutural.

## T3 [US03 - Must Have]

- Registro e resumo financeiro mensal de gastos.
- Cobrir combustível e reparos.
- Status: implementação funcional pendente. A tela `/gastos` é demonstrativa.

## Telas do protótipo

- Referência: [Figma publicado](https://open-thumb-85089078.figma.site).
- Dashboard, Manutenções, Gastos, Relatórios, Garagem, Configurações e Alertas com rotas navegáveis.
- Busca e filtros locais disponíveis para explorar os exemplos.
- Formulários, exportação, conta e dados das telas não estão integrados ao backend.
- A presença de uma tela não conclui a tarefa funcional correspondente.
