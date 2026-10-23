# Validação da T3 - 23/10/2026

Escopo: financeiro completo integrado nesta retomada - combustível, reparos, consulta/filtros, relatórios, CSV e impressão/Salvar PDF. A decisão anterior de adiar T3 foi superada pelo pedido de iniciá-la. Nenhuma alteração estrutural do banco: migrations existentes conferidas aplicadas.

## Implementação e ativação

FEATURE_FINANCEIRO=true como padrão e no exemplo; .env privado não editado nesta máquina. GET autenticado /api/features retorna somente financeiro:boolean. Cliente espera configuração antes de expor a conta/menu e limpa ao sair; resposta antiga não altera sessão nova. Desligar mantém despesas/relatórios 404 e navegação indisponível, preservando Dashboard/T1/T2.

Corrigidos: relatório com motocicleta_id=0 interpretado como todas; dados antigos visíveis ao trocar/falhar filtro; período/moto mutáveis durante consulta; limpeza dos campos de combustível no cliente ao trocar tipo; formato decimal do CSV; largura das colunas móveis. Configurações/Garagem não remontam ao mudar a seleção global, preservando confirmação do primeiro cadastro. Ícones decorativos não compõem nomes acessíveis do menu.

## Testes automatizados

- Base antes da mudança: 45 testes backend / 391 verificações e 28 frontend aprovados.
- Final: 54 testes backend / 486 verificações e 34 frontend aprovados. Backend usa SQLite em memória, com RefreshDatabase isolado; não roda migrate:fresh no MySQL do usuário.
- Regressões: tolerância de um centavo, limites/precisão/produtos altos, campos malformados, tipos/opcionais, filtros inclusivos e ordenação, propriedade entre motos/contas, totais e pendências, feature desligada e CSRF fora do bypass dos testes.
- Cliente: payload pt-BR, chave única entre tabelas, status no CSV, escape/fórmulas, navegação por flag, configuração tardia/sessão e regressões de push/offline/odômetro.
- Build Vite 6.4.5 aprovado. Pint dos arquivos alterados e Composer validate --strict aprovados. A pendência histórica de formatação global de quatro migrations não alteradas permanece independente.

## Navegador, HTTP e MySQL

Chrome headless com perfil isolado e contas descartáveis próprias, sem usar sessão/dados do usuário. Navegação real e API/MySQL; dados persistidos consultados após alterações. Tela desktop 1440x1000 e viewport móvel 390x844, tema claro/noturno, formulário, rolagem e ações de tabela conferidos por capturas. Nenhuma exceção JavaScript no fluxo aprovado.

| Cenário | Evidência |
| --- | --- |
| Conta/rota | Cadastro pela interface, acesso direto/gastos e configuração autenticada recebida |
| Combustível | 12,50×7,00=87,50; edição 1,01×1,50=1,52 persistida |
| Troca de tipo | Combustível→reparo→combustível; litros/preço/posto irrelevantes nulos |
| Filtros/totais | Reparos 0,10+0,20=0,30; mês/tipo, histórico, troca de duas motos sem vazamento de registros |
| Falha de consulta | Resposta 503 controlada no navegador mantém aviso e descarta resultado de período anterior |
| Regressão T2 | Manutenção concluída 100,01 avança odômetro 1000→1100; pendente 500 não altera total |
| Relatórios | 187,81 na primeira moto, 2,00 na segunda e 189,81 em todas; meses vazios retornam zero |
| CSV | Download real, BOM UTF-8, vírgula decimal, aspas e fórmula=SUM neutralizada; pendente rotulado |
| Impressão | Botão aciona window.print; PDF A4 paisagem gerado pelo Chrome, renderizado e conferido sem corte das tabelas e sem navegação |
| Proteções reais | Outra conta 404; gravação sem CSRF 419; inválidos 422; logout seguido de 401 |
| Exclusão | Confirmação de exclusão na UI e remoção persistida; despesas não alteram alerta/odômetro |
| Flag desligada | API real coberta pelos testes Laravel. UI testada simulando apenas resposta/api/features=false, ocultando menu e redirecionando rota |

Todas as contas/sessões/motos/registros descartáveis desta execução removidos por rotina limitada a email aleatório conhecido e nome de fixture. Cascatas do domínio preservadas; nenhum dado da conta do usuário reconciliado/excluído. Nenhuma notificação adicional enviada nem scheduler contínuo iniciado.

## HTTPS móvel da T3

Túnel renovado para o acesso registrado em ACESSO_CELULAR.md. HTML/build/manifest 200, CSRF 200 com cookies Secure/HttpOnly/no-store, features anônimo 401 e caminho privado 404. Smoke real de cadastro/sessão, financeiro=true, combustível 1,52 e relatório 1,52, CSRF ausente 419 e logout seguido de 401 aprovado. Conta/sessões descartáveis removidas; APP_URL/WEBPUSH_SUBJECT e feature definidos só no processo móvel. Sem .env/chaves ou push alterados.

## Reprodução e limites

Backend: OPENSSL_CONF do XAMPP no terminal e php artisan test. Frontend: npm ci pelos lockfiles, npm test e npm run build. Instalar/iniciar conforme README. Roteiro de manutenção em RETOMADA_T3.md.

O teste 390px usa Chromium, não Safari nem iPhone físico. Aceite complementar do usuário para esta T3 no iPhone/PWA, compartilhamento de CSV e impressão do aparelho ainda não registrado. Testes anteriores de push/instalação/offline reais de 22/10 não foram repetidos. Não houve teste de carga nem concorrência multiprocesso MySQL. Auditoria de dependências anterior está datada em REVISAO_LOGICA_SEGURANCA.md; esta T3 não acrescentou dependências ou chaves.
