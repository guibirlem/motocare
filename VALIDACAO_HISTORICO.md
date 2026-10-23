# Histórico em linha do tempo — 23/10/2026

Referência enviada adaptada à identidade MotoCare: trilha vertical com marcadores, ícones de abastecimento/manutenção/despesa, título real, valor/data à direita, odômetro/litros/preço e consumo quando disponível. Separação por mês com total e média ponderada; claro/noturno e responsividade.

Integrado ao Dashboard e às telas Gastos/Manutenções, incluindo alias `/historico`. Formulários, filtros, favoritos, cálculos, edição/exclusão e regras de negócio preservados. Nas telas de registros, botão **Tabela** mantém a tabela anterior; **Linha do tempo** volta ao visual novo. Ações também disponíveis nos itens da linha do tempo, com foco no formulário. Dashboard conserva gráficos/categorias/período/Mostrar mais.

## Dados e regras

- Único acréscimo à API: `ciclos[].abastecimento_id`, identificando o abastecimento que fecha cada ciclo. Campos/cálculos anteriores preservados, sem schema/migration/rotas/dependências novas. Autorização permanece a da moto/conta.
- Consumo do item usa esse ID, nunca só a data: há suporte a vários abastecimentos no mesmo dia sem trocar suas médias. Primeiro tanque, parcial, lacuna ou ciclo inválido não recebe média inventada. Mantidas as regras de cheio a cheio, incluindo parciais e precisão atual.
- Gastos obtém ciclos do painel autenticado existente. Falha dessa estatística mantém registros/ações e mostra indisponibilidade de consumo. Manutenções não faz essa consulta financeira.
- Total mensal soma centavos dos registros no filtro, excluindo manutenções pendentes e mostrando seu valor separadamente. Média mensal é soma das distâncias dividida pela soma dos litros dos ciclos válidos encerrados nos itens do filtro. Comparação apenas com o mês de calendário imediatamente anterior, se ambos têm dados válidos no mesmo filtro; não compara com mês ausente.
- Mostrar mais limita itens visíveis, preservando o total do mês inteiro no filtro e a contagem correspondente. Ordenação cronológica/data/id e chaves por origem preservam registros com IDs iguais em tabelas diferentes.
- Dashboard aguarda todas as fontes antes de exibir o resumo mensal. Falha do histórico não finge lista vazia nem total parcial; Atualizar permite recuperar. Offline/reconexão preservados, sem cache ou fila de dados pessoais.

## Evidências

- **82 testes backend / 878 verificações** aprovados. Novo teste confirma dois ciclos distintos no mesmo dia e os IDs expostos em painel/relatórios. Pint dos PHP alterados aprovado.
- **55 testes frontend**, incluindo seis novos de agrupamento/centavos/pendentes, consumo por ID, média ponderada, comparação entre meses, paginação e datas. Build aprovado, sem pacote novo/lockfile alterado.
- Chrome isolado + SQLite descartável: linha do tempo/tabela, filtros, editar/excluir em Gastos e editar manutenção, alias/historico e persistência. Cenário com dois cheios no mesmo dia mostra30 e25km/L nos itens corretos; parcial/primeiro cheio sem média; outubro28,57km/L e comparação42,9% com setembro.
- Dashboard total166,00 e41,00 pendentes separados após operações da fixture. Espera proposital na consulta de manutenções não mostra resumo parcial;503 de histórico/painel, recuperação e offline/reconexão aprovados. Odômetro permanece1600. Nenhuma exceção JavaScript.
- Layout320/390/768/1024/1440px claro/noturno conferido, sem overflow horizontal; capturas de Gastos, Manutenções e Dashboard revisadas. Fixtures removidas e QA8010/5190 encerrado.

Sem alteração de dados reais MySQL, `.env`, APP_KEY/VAPID, configuração Gmail, envio push/e-mail ou novo túnel/aceite Safari físico. PDF acadêmico anterior histórico, sem regeneração. SMTP segue conforme [RECUPERACAO_SENHA.md](RECUPERACAO_SENHA.md).

Fontes: `feature/historico-timeline`, sobre728fd50, mantendo branches anteriores. Pacote local `entrega/MotoCare-Historico-2026-10-23.zip`, manifesto/checksum próprios. Atualizar online e abrir Dashboard, Gastos ou Manutenções; acesso local `http://127.0.0.1:5173/dashboard`.
