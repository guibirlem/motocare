# Retomada da T3 - Financeiro

Preparado em 22/10/2026. Este roteiro prepara trabalho futuro; não ativa nem declara entregue a T3. A aplicação continua em T1/T2, com `FEATURE_FINANCEIRO=false`. Relatórios/exportações são trabalho preservado associado ao financeiro; confirmar seu escopo ao iniciar a próxima etapa.

Base de aceite atualizada em 22/10: revisão mobile/PWA no iPhone concluída pelo usuário, incluindo telas/formulários, app instalado, offline/reconexão e push pelo botão/scheduler em segundo plano. Detalhes do cenário isolado e limites em CONTINUACAO. Nenhum scheduler contínuo iniciado. Fontes atuais, revisão de negócio/segurança e PDF de oito páginas integram a atualização Git `update task1 e task2 funcionando` em master.

## Ponto de partida

Incorporar também a revisão de 22/10 em [REVISAO_LOGICA_SEGURANCA.md](REVISAO_LOGICA_SEGURANCA.md): há migration aditiva de alertas.km_base, regra de manutenção/odômetro e cálculo de 90% do intervalo, além de correções de segurança/dependências. Não restaurar os contratos antigos de odômetro sem atualização ou de 90% absoluto. T3 permanece desligada; opcional posto foi corrigido no código preservado, com teste.

1. Ler `AGENTS.md`, `CONTINUACAO.md`, `TASKS.md`, `SPEC.md` e `MEMORY.md`, depois este roteiro e `ENTREGA.md`.
2. Clonar/puxar a versão atual de [guibirlem/motocare](https://github.com/guibirlem/motocare), identificada pela mensagem `update task1 e task2 funcionando` no [histórico](https://github.com/guibirlem/motocare/commits/master/). A base antiga `7f5b9cb` isolada não contém a integração. Como alternativa local, usar `entrega/MotoCare-Retomada-T3-2026-10-22.zip` atualizado; o ZIP de 19/10 permanece histórico. O PDF atual de oito páginas já inclui este planejamento.
3. Em clone com Git, conferir alterações do colega, incorporar os fontes atuais com revisão e criar `feature/t3-financeiro`. Preservar histórico; não sobrescrever `.git`, `.env`, APP_KEY ou chaves existentes. A publicação desta edição preserva o histórico remoto; iniciar a T3 a partir dessa base atualizada.
4. Seguir instalação no [README](README.md): MySQL/XAMPP, `composer install`, `npm ci`, `php artisan migrate`, `php artisan push:configurar`; iniciar API, frontend (`npm start`) e scheduler em terminais separados. Nunca usar `migrate:fresh` no MySQL com dados.
5. Conferir `php artisan migrate:status` antes de decidir por novas migrations. A migration `2026_10_19_000001_add_descricao_to_despesas_table.php` já existe; não duplicá-la. Alteração estrutural exige SPEC, DER e migration correspondente.
6. Reexecutar a base de testes abaixo antes de editar; registrar resultados atuais, sem reutilizar os números históricos como se fossem novos. Testes Laravel usam SQLite em memória conforme `backend/phpunit.xml`; conferir que esse isolamento continua válido, pois há `RefreshDatabase`.

## Inventário confirmado por leitura do código em 22/10

| Área | Arquivos / pontos de entrada | Situação |
| --- | --- | --- |
| API financeira | `backend/routes/history.php`; `backend/app/Http/Controllers/WorkspaceController.php`: `expenseData`, `expenses`, `storeExpense`, `updateExpense`, `deleteExpense`, `reports` | Rotas existentes, com sessão/CSRF e bloqueio financeiro |
| Chave de ativação | `backend/config/features.php`; `backend/app/Http/Middleware/EnsureFinanceEnabled.php`; `backend/.env.example` | Padrão false; requisições financeiras autenticadas retornam 404 |
| Dados e precisão | `backend/app/Models/Despesa.php`; `backend/app/Support/Money.php`; migrations `2026_09_24_000003_create_despesas_table.php` e `2026_10_19_000001_add_descricao_to_despesas_table.php` | Preservados; não recriar tabela nem apagar dados |
| Interface de gastos | `frontend/src/views/ExpensesView.vue`; `frontend/src/components/RecordsView.vue` | Componente existe com `kind="despesas"`, mas sem rota/menu |
| Relatórios | `frontend/src/views/ReportsView.vue`; `frontend/src/services/csv.js` | Consulta API, resumo mensal, CSV e impressão existentes; sem rota/menu |
| Integração visual | `frontend/src/router/index.js`; `frontend/src/App.vue`; `frontend/src/views/HomeView.vue` | Recolocar navegação quando retomar; Dashboard atual usa T1/T2 diretamente |
| Estado e formatos | `frontend/src/services/workspace.js`, `api.js`, `format.js`; `frontend/src/styles/main.css` | Sessão, moto selecionada, cálculos e estilos existentes |
| Testes | `backend/tests/Feature/WorkspaceTest.php`; `frontend/tests/workspace.test.mjs` | Casos financeiros habilitam config apenas dentro do teste; não provam integração visual ativa |

## Contrato preservado para revisar antes de implementar

- Despesas por moto própria, tipos `combustivel` e `reparo`. `valor` entre 0 e 99999999.99, até duas casas; API retorna decimais como strings. Data em AAAA-MM-DD. Descrição opcional até 5.000 caracteres, obrigatória em reparo.
- Combustível: litros positivos até 999999.99 e preço por litro positivo até 99999999.99, ambos com até duas casas. Total é produto arredondado a centavos, tolerância de um centavo. Não somar dinheiro com ponto flutuante sem controle de centavos.
- Reparo limpa litros/preço/posto. Revisar também substituição dos opcionais omitidos em PUT de combustível (por exemplo, `posto`), pois a normalização T2 não garante a mesma semântica no código financeiro.
- CRUD em `/api/motocicletas/{moto}/despesas`; filtro opcional `mes=AAAA-MM`, ordem por data e ID decrescentes. PUT/DELETE incluem `/{despesa}`. Não confiar em IDs de usuário enviados pelo cliente.
- Manutenção não cria despesa automaticamente. Resumo soma despesas e apenas manutenções concluídas; não repetir um serviço nos dois cadastros. Pendentes podem aparecer na listagem, mas ficam fora dos totais.
- Relatórios preservados: `/api/relatorios?inicio=AAAA-MM-DD&fim=AAAA-MM-DD`, `motocicleta_id` opcional. Datas inclusivas, intervalo máximo de dez anos, meses vazios com zero; resposta com `motocicletas`, `despesas`, `manutencoes`, `alertas`, `mensal` e `resumo`.
- CSV e impressão/Salvar PDF são executados no navegador; não existe geração PDF da API nem autorização para criar Blade. Preservar escape de CSV e proteção contra fórmulas.
- Qualquer decisão de mudar contrato deve aparecer na SPEC e nos testes. O roteiro não introduz nova regra de negócio.

## Sequência incremental sugerida

1. **T3.0 - Revisar base e escopo:** validar instalação, banco e testes; confirmar se a entrega inclui apenas gastos ou também relatórios/exportações. Registrar divergências entre código preservado e SPEC.
2. **T3.1 - Revisar API:** CRUD, limites/precisão, filtros, opcionais em PUT, trocas combustível/reparo e propriedade. Corrigir casos demonstrados por testes, preservando T1/T2. Manter feature desativada por padrão até decidir a entrega.
3. **T3.2 - Integrar gastos:** revisar `RecordsView` compartilhado com T2; recolocar rota/menu de Gastos e ligar ao estado real. Validar troca de moto, período, estados vazio/erro/carregamento, formulário e exclusão. Apenas mudar a variável de ambiente não integra a interface.
4. **T3.3 - Relatórios, se incluídos:** revisar totais, filtros e limites; recolocar rota/menu; conferir CSV e impressão no navegador. Não fazer Dashboard depender da API financeira quando a feature estiver desligada. Definir comportamento coerente do menu se a API continuar com chave de ativação.
5. **T3.4 - Aceite integrado:** teste autenticado HTTP/MySQL, navegação desktop/móvel e regressão T1/T2; registrar evidências, remover somente fixtures descartáveis e atualizar documentos/pacote. Se o padrão de ativação mudar, revisar `test_finance_is_deferred_by_default` e a configuração de exemplo de forma intencional.

## Validação para a próxima sessão

Em `backend` (ambiente de testes isolado):

~~~powershell
# Se necessário no XAMPP, apenas neste terminal:
$env:OPENSSL_CONF = 'C:/xampp/php/extras/ssl/openssl.cnf'
php artisan test --filter=WorkspaceTest
php artisan test
php vendor/bin/pint --test
composer validate --strict
~~~

Em `frontend`:

~~~powershell
npm test
npm run build
~~~

Com a T3 retomada em ambiente de desenvolvimento, habilitar `FEATURE_FINANCEIRO=true` apenas no ambiente pretendido, limpar cache de configuração com `php artisan config:clear` se necessário e reiniciar processos. Para a configuração atual T1/T2, manter false. Não versionar `.env`.

Aceite manual/HTTP a registrar:

- Criar/editar/excluir combustível (12,50 L × R$ 7,00 = R$ 87,50) e reparo; conferir persistência e arredondamento (1,01 × 1,50 = 1,52).
- Rejeitar negativos, excesso de casas, datas inválidas, reparo sem descrição e total incompatível. Conferir limpeza de campos na troca de tipo e opcionais omitidos em PUT.
- Dois usuários e duas motos: negar acesso cruzado e IDs de registros incompatíveis com a moto; testar CSRF real (fora do middleware relaxado dos testes), sessão expirada e falhas da API.
- Filtrar mês/período e conferir limites inclusivos, meses vazios e totais exatos. Exemplo isolado: reparos 0,10 + 0,20 e manutenção concluída 100,01 resultam em 100,31; manutenção pendente de 500,00 fica fora do total.
- Trocar moto/filtros sem exibir dados da seleção anterior; testar vazio, carregamento, erro, confirmação de exclusão e largura móvel.
- Se relatórios incluídos: CSV com acentos, aspas, separadores e texto semelhante a fórmula; impressão/PDF com cabeçalhos, tabelas e quebras legíveis.
- Sem feature: API financeira autenticada 404, menu coerente e T1/T2 funcionais. PWA continua sem cache de API/dados pessoais.

## Limites herdados e encerramento

Última execução funcional registrada em 22/10 na revisão de negócio/segurança: 45 testes backend/391 verificações, 28 frontend/build e smoke HTTP/MySQL/HTTPS aprovados. Pint alterados e Composer validate aprovados; Pint global sinaliza apenas formatação legada de quatro migrations. Nesta preparação documental/publicação não houve nova execução dessas suítes nem ativação financeira.

Em22/10, usuário confirmou recebimento real com página aberta, Chrome minimizado e alerta pelo scheduler (14:59); fixtures removidas e ausência de reenvio conferida. Também aprovou iPhone instalado, telas/formulários, offline/reconexão, tema e scheduler em segundo plano (15:47:51). Fontes e PDF integram a atualização Git desta edição. Banner flutuante e Chrome encerrado não foram validados separadamente. A revisão final das novas regras não repetiu os aceites manuais; consultar REVISAO_LOGICA_SEGURANCA.md.

Ao finalizar a próxima etapa, atualizar TASKS, CONTINUACAO, MEMORY, README e ENTREGA; SPEC se houver regra/contrato novo, DER/migrations se houver mudança estrutural. Gerar novo pacote e checksum. O PDF de 22/10 é um retrato da entrega T1/T2; regenerar uma edição nova se a entrega acadêmica passar a incluir T3.
