# Painel de lembretes — 23/10/2026

## Complemento visual e acesso em Alertas

Após nova solicitação da mesma referência, o componente existente foi complementado: sugestões em lista vertical, relógio e botões roxos com variante legível no modo noturno, ícones circulares por categoria. Alertas passa a mostrar ativos e sugestões juntos; controles detalhados originais continuam abaixo e Ver lembrete os foca mesmo na própria tela. Ver todos é oculto nessa tela, mantendo o acesso do Dashboard. Pneus dianteiro/traseiro e todos os cálculos/contratos existentes permanecem.

Validação desta revisão:55 testes frontend/build aprovados e Chrome/SQLite isolado320/390/768/1024/1440px, nos dois temas, com capturas inspecionadas. Configurar óleo → salvar → recarregar; IPVA por data → editar → concluir → voltar à sugestão → mostrar concluído → excluir; abastecimento230km/56dias, ordem de prioridade, atalhos/foco na mesma tela, limpeza de KM por tipo, lista vertical/controles44px/cores por tema,503/recuperação e offline/reconexão aprovados. Nenhuma exceção JavaScript; conta e sessões descartáveis removidas e servidores QA encerrados. Sem alteração de backend/schema/dados reais/configuração privada ou novo teste físico Safari/push/e-mail. Evidências temporárias em tmp/reminders-*.png e tmp/reminders-e2e-results.json; build de uso5173 atualizado. Publicação incremental na branch feature/correcao-acesso, preservando entregas anteriores.

## Implementação e validações anteriores

Referência enviada pelo usuário adaptada ao MotoCare: Dashboard com Lembretes/ativos, ícones, prazo por KM/data, quilômetros/dias restantes e Sugeridos com botão Configurar. Identidade verde/preto e temas preservados; cards arredondados, grade desktop e mobile, ícones SVG existentes e CSS escopado. Sem biblioteca ou assets externos.

Sugestões: próximo abastecimento, troca de óleo, revisão, pneus dianteiro/traseiro, seguro, IPVA e licenciamento. Tipos ativos deixam as sugestões e voltam ao concluir/excluir; pneus genéricos legados são reconhecidos. Configurar abre o formulário existente em Alertas com tipo/foco; atalho para tipo ativo edita o pendente ou aponta o notificado, sem reativá-lo. Ver lembrete foca o registro correspondente. Alertas conserva CRUD, indicadores e filtro de concluídos, com sugestões quando o formulário está fechado.

## Contrato e preservação

Acrescentados `abastecimento`, `seguro`, `ipva` e `licenciamento` ao campo string tipo_alerta, separados dos tipos de manutenção. **Sem schema/migration nova.** Seguro/IPVA/licenciamento exigem data válida e proíbem KM/base preenchidos na API. Usuário informa o vencimento; sem dedução de prazo oficial ou renovação automática. Abastecimento mantém KM/data ou ambos e aviso90% do intervalo ou data. Trocar de tipo no formulário limpa o KM quando necessário.

Todos usam API/ownership/CSRF e scheduler/Web Push existentes, preservando locks, tentativas, aceite e não repetição. Concluir não altera odômetro nem cria manutenção, abastecimento, pagamento ou próximo ciclo. Demais telas/rotas, estilos globais, T1/T2/T3, landing, recuperação de senha e PWA preservados. Falha de carga não é apresentada como lista vazia com sugestões; Atualizar recupera. Dias calculados por calendário, com hoje em São Paulo; atenção90% continua separada do vencimento real.

## Verificação

- **81 testes backend / 867 verificações**, incluindo quatro novos (96 verificações): CRUD, datas/conversão de tipo, ownership/CSRF e scheduler com transporte mockado. Sem push real.
- **49 testes frontend**, incluindo seis novos: prazos/calendário, limiar90% em KM alto, ordenação sem mutação, sugestões e payload. Build e Pint aprovados; lockfiles preservados.
- A suíte revelou flutuação no helper de inscrição de testes: OpenSSL pode omitir zeros iniciais das coordenadas EC. Corrigido padding32 bytes por coordenada P-256, sem alterar push de produção.
- Chrome/SQLite isolado: cadastro/moto, dashboard→configurar óleo→salvar→recarregar, card persistente e foco no registro; IPVA por data criado/editado/concluído/excluído e retorno às sugestões; troca de tipo limpa KM; atalho de tipo existente não duplica.
- Cenário descartável semelhante à imagem: odômetro66.488, limite66.718 e data28/08/2026 mostra230km restantes e56dias de atraso em23/10. Nenhum desses registros foi criado na conta real.
- Layout320/390/768/1024/1440px, claro/noturno, sem overflow; título abaixo do cabeçalho e todos os botões Configurar acessíveis sem sobreposição. Capturas superiores/inferiores mobile e desktop revisadas.
-503/recuperação e offline/reconexão aprovados, sem erro JavaScript. Nenhum gasto/manutenção/odômetro alterado por lembrete. Fixtures removidas e QA8010/5190 encerrado ao concluir.

Sem dados MySQL do usuário, `.env`, APP_KEY/VAPID ou credenciais alterados; sem e-mail externo, túnel novo, scheduler contínuo ou novo aceite Safari/iPhone físico. SMTP Gmail ainda exige senha de app local: [instruções](RECUPERACAO_SENHA.md). PDF acadêmico anterior histórico, sem regeneração.

Abrir Dashboard/Alertas após atualizar online. Local: `http://127.0.0.1:5173/dashboard`. Fontes em `feature/lembretes` sobre89cffc1, branches anteriores preservadas. Pacote próprio `entrega/MotoCare-Lembretes-2026-10-23.zip`, manifesto e checksum correspondentes.
