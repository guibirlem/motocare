# Complementos do MotoCare — 23/10/2026

Base preservada: T1/T2/T3 da branch `feature/t3-financeiro`, commit `b3c70e9ff93c411d434deda7d964cbf95cbdb3a3`. Esta extensão adiciona dados opcionais e integra recursos às telas existentes. Não remove rotas, categorias antigas, temas, autenticação, exportações ou regras de alertas/odômetro.

## Mapeamento do pedido

| Item | Base encontrada | Complemento integrado |
| --- | --- | --- |
| Web responsiva | Sidebar, grid, formulários e tema | Botão flutuante de registro rápido; desktop com feed ao lado dos gráficos |
| Dashboard | Resumo de manutenção e alertas | KM atual, gasto mensal, consumo, custo/KM, histórico unificado e filtros |
| Abastecimento | Litros, preço, total, nome livre do posto | Favoritos por conta e combustível, memória de preços, cálculo inverso, KM e tanque cheio |
| Manutenção | Óleo, relação, pneus e freios | Lubrificação/tensão, eixos independentes, filtros, suspensão, arrefecimento, cabos, vela e natureza preventiva/corretiva |
| Alertas | KM/data pelo primeiro vencimento | Novos serviços, sugestão de ciclo de corrente e atalho de chuva/lavagem sem duplicação |
| Corrente e reserva | Ausentes | Contador de ajustes desde a troca, parcial virtual, barra e KM estimados até reserva/esvaziar |
| Estatísticas | Relatório mensal, CSV e impressão/PDF | Curvas de consumo/preço por posto, divisão dos gastos e coluna mensal de demais despesas |

## Regras e preservação

- Migration aditiva `2026_10_23_000001_add_motorcycle_insights.php`, aplicada no MySQL local. SPEC e DER atualizados. Nenhum rollback, recriação de tabela ou conversão dos históricos.
- Comparação antes/depois por contagem e SHA-256 dos campos originais em usuários, motos, manutenções, despesas e alertas confirmou preservação integral dos registros existentes. O snapshot local contém apenas nomes de campos, contagens e hashes, sem valores pessoais/chaves.
- Odômetro de abastecimento também avança atomicamente quando maior; edição histórica, troca de tipo e exclusão não reduzem. Falha ao atualizar memória do posto desfaz lançamento e KM na mesma transação.
- Postos alheios nunca podem ser associados. Preço por posto/combustível usa último lançamento por data/id; históricos antigos não sobrescrevem o mais recente. Remover favorito preserva texto/valor do gasto. Exclusão de moto recalcula memória compartilhada com outras motos da conta.
- Consumo exige ciclos cheios válidos, soma parciais e usa média ponderada. Lacunas/KM regressivo não geram consumo inventado. Reservas usam últimos cinco ciclos e limites de capacidade em cada parcial. Sem dados suficientes, interface orienta completar cadastro/histórico.
- Categorias antigas pneus/freios continuam válidas. Manutenções antigas ficam sem natureza até edição explícita; não são classificadas por suposição. Pendentes continuam fora de custos.
- Chuva/lavagem cria lembrete, não serviço realizado. Scheduler conserva locks, tentativas e prevenção de reenvio; conclusão/próximo ciclo continuam explícitos.
- FEATURE_FINANCEIRO=false mantém T1/T2 e painel de manutenção/corrente; não expõe despesas, consumo ou preços. API, CSRF, sessão, isolamento, offline e tema continuam ativos.

## Verificações

- Backend: **61 testes / 647 verificações**, incluindo a suíte anterior e sete cenários novos com SQLite isolado. Consumo ponderado com volumes diferentes, parciais, lacunas/KM regressivo, reserva, atomicidade, todos os tipos de serviço/alerta, categorias e propriedade.
- Frontend: **38 testes aprovados**; cálculo inverso em centavos, novos campos opcionais, filtros/ordenação do feed e regressões anteriores. Build de produção aprovado.
- Chrome isolado + API/MySQL real: suíte T3 anterior aprovada (CRUD, duas motos/contas, odômetro, filtros/erros, CSV/impressão, tema, desligamento visual da feature). Contas e sessões de teste removidas.
- Novo fluxo pelo navegador: cadastro do tanque/favorito, preço persistido e separado por combustível, total antes dos litros (10/3 → preço3,33 e total conciliado9,99), tanques/parciais com média30km/L, odômetro1350, reserva250km/esvaziar310km, gasto353,50, um ajuste desde a troca e chuva sem duplicação. HTTP confirmou propriedade404 e CSRF419; nenhuma exceção JavaScript. Fixtures próprias removidas.
- Capturas em desktop1600px e mobile390px conferidas visualmente: cards, formulários, feed/gráficos, reserva/corrente e tema. Tabelas mantêm rolagem interna. Testes em Chromium não representam aceite novo no Safari/iPhone físico.
- Pint dos arquivos PHP alterados e Composer validate --strict aprovados. Auditoria de177 arquivos (fontes elegíveis/build) sem APP_KEY, chave privada VAPID ou padrões de credenciais encontrados. Nenhuma dependência nova foi adicionada; `.env`, chaves e fontes de T1/T2/T3 preservados.

## Fontes da extensão

Branch dedicada [feature/complementos-motocicletas](https://github.com/guibirlem/motocare/tree/feature/complementos-motocicletas), baseada em b3c70e9. Preservar master e feature/t3-financeiro; nenhuma integração automática entre branches faz parte desta extensão. O manifesto/pacote local de fontes dos complementos identifica o PDF incluído como retrato histórico anterior.

## Usar e retomar

1. Em outra instalação, restaurar os lockfiles e executar `php artisan migrate` no backend; preservar `.env`, APP_KEY e VAPID.
2. Atualizar a página com Ctrl+F5; no PWA, fechar completamente e reabrir online. Garagem permite informar tanque/reserva, sem obrigar a preencher motos antigas.
3. Em Gastos, cadastrar favorito pelo formulário de abastecimento; selecionar posto/combustível e conferir preço. Registrar KM, marcar cheios/parciais e não omitir abastecimentos entre referências.
4. Manutenções separa os serviços da corrente e cada eixo. Registrar a troca do kit para iniciar contador. Alertas permite programar KM e data juntos.
5. Para push diário manter `php artisan schedule:work` e inscrição ativa. Nenhum scheduler contínuo ou push real foi disparado nesta revisão; o transporte dos testes foi simulado. Os aceites reais anteriores permanecem históricos.

O PDF/pacote acadêmico anterior retrata a T3 antes destes complementos; não é prova da extensão atual. Este relatório, SPEC, DER, TASKS e fontes descrevem a versão nova. Aceite físico adicional no iPhone permanece pendente.
