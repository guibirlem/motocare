# Continuidade da T3 - Financeiro

Complemento atual: `feature/lembretes` sobre89cffc1, incluindo todas as etapas anteriores. [VALIDACAO_LEMBRETES.md](VALIDACAO_LEMBRETES.md): painel/atalhos/vencimentos integrados,81backend/867 verificações e49frontend/build. Sem migration nova; SMTP Gmail depende da senha de app no `.env` privado.

Estado mais recente de23/10: preservar também a landing e a recuperação de senha, na branch `feature/recuperacao-senha` sobre80b7b7f. Ler primeiro CONTINUACAO e [RECUPERACAO_SENHA.md](RECUPERACAO_SENHA.md).77backend/771 verificações,43frontend/build e SMTP local isolado aprovados; SMTP externo ainda não configurado por escolha do usuário. Pacote atual `entrega/MotoCare-Recuperacao-Senha-2026-10-23.zip`. As referências da T3 abaixo documentam a integração original, não a versão mais recente.

Atualizado em 23/10/2026. A T3 foi retomada por solicitação do usuário e integrada com gastos, abastecimentos, relatórios, CSV e impressão/Salvar PDF. O antigo adiamento de 19/22 de outubro foi superado. Código na branch `feature/t3-financeiro`, sobre 5cdce58; manter master e histórico preservados. Resultados em [VALIDACAO_T3.md](VALIDACAO_T3.md).

## Antes de continuar

1. Ler AGENTS, CONTINUACAO, TASKS, SPEC e MEMORY.
2. Usar a branch `feature/lembretes` ou o pacote local `entrega/MotoCare-Lembretes-2026-10-23.zip`, com manifesto/checksum. Incluem T1/T2/T3, complementos, landing, recuperação e lembretes; pacotes antigos são históricos.
3. Restaurar dependências com composer install/npm ci pelos lockfiles. Conferir migrations, incluindo `2026_10_23_000001_add_motorcycle_insights.php` dos complementos; recuperação não acrescenta schema. Nunca usar migrate:fresh no banco com dados.
4. FEATURE_FINANCEIRO=true agora é o padrão e o exemplo. Se o ambiente herdou false no .env, habilitar explicitamente, limpar config cache se houver e reiniciar. Preservar credenciais, APP_KEY e VAPID existentes.
5. Iniciar API 8000 e frontend 5173 pelo README. Scheduler é separado; só iniciar quando desejado, com contato VAPID válido. Testes de despesas não precisam disparar push.

## Mapa dos arquivos

| Área | Arquivos |
| --- | --- |
| API/configuração | backend/routes/history.php, config/features.php, middleware EnsureFinanceEnabled |
| Despesas/relatórios | WorkspaceController: expenseData, expenses/store/update/deleteExpense e reports |
| Precisão | backend/app/Support/Money.php, Models/Despesa.php, migrations existentes de despesas/descricao |
| Sessão/menu | frontend/src/services/workspace.js, navigation.js, App.vue e router/index.js |
| Gastos/T2 compartilhada | components/RecordsView.vue e views/ExpensesView.vue/MaintenanceView.vue |
| Relatórios/exportação | views/ReportsView.vue, services/finance.js, format.js e csv.js |
| Regressões | backend/tests/Feature/FinanceTest.php/WorkspaceTest.php; frontend/tests/finance.test.mjs/workspace.test.mjs |

## Regras que devem permanecer

- Combustível calcula litros×preço em centavos; API aceita tolerância de um centavo sobre o arredondamento. Limites/precisão na SPEC. Reparos exigem descrição e limpam campos de combustível.
- Manutenção não cria despesa automática. Totais combinados incluem despesas e somente manutenções concluídas; não existe deduplicação de registros manuais duplicados.
- IDs não podem atravessar motos/contas; CSRF nas gravações. Filtro de relatório exige ID positivo ou omissão para todas as motos próprias.
- Trocar filtro/moto não deve exibir dados da consulta anterior. Exportação segue o resultado gerado; campos bloqueados enquanto consulta.
- FEATURE_FINANCEIRO=false deve continuar desabilitando API e navegação sem impedir T1/T2. GET /api/features é autenticado e retorna somente a flag.
- PWA não cacheia API/dados pessoais nem permite gravações offline. Tema e push permanecem independentes do financeiro.

## Validação e pendência complementar

Base atual: 54 testes backend / 486 verificações e 34 frontend/build aprovados; Pint alterados e Composer validate. E2E Chromium headless em 1440x1000/390x844 com MySQL real, duas contas descartáveis, filtros, CRUD, totais, CSV, impressão/PDF e erros/isolamento aprovados; fixtures removidas. Sem migração nova ou mudança em dados da conta do usuário.

No backend: OPENSSL_CONF do XAMPP no processo, depois php artisan test. No frontend: npm test e npm run build. Mantenha SQLite isolado para os testes Laravel.

Aceite visual recebido em23/10: usuário confirmou “Abrem e estão normais” para Gastos/Novo gasto e Relatórios no Safari do iPhone. CSV/compartilhamento, impressão no aparelho, instalação PWA desta origem e os complementos posteriores ainda exigem confirmação específica. A viewport móvel do Chromium não substitui teste no aparelho; não ampliar o escopo desse aceite por inferência.

Ao editar regras, atualizar SPEC e testes; se mudar estrutura, DER/migration. Ao entregar, atualizar TASKS/CONTINUACAO/MEMORY e pacote/PDF conforme escopo. Publicar em branch de trabalho; não reativar demostrações nem substituir histórico com force push.
