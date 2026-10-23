# Revisão de formatação do frontend — 23/10/2026

Correção solicitada após captura da Visão Geral: o botão Atualizar era um filho direto de uma coluna flexível, ocupava toda a largura e, junto com os espaçamentos/blocos anteriores, empurrava os indicadores para baixo. A captura já mostrava dados carregados; não foi tratada como prova de falha da API.

- Título, descrição e botão compacto ficam na mesma faixa; indicadores principais vêm antes dos cuidados pendentes. Todos os alertas, históricos e módulos anteriores continuam disponíveis.
- Atualização acompanha tanto histórico/alertas quanto painel financeiro, bloqueando novos cliques até terminar. Indicadores existentes permanecem durante consulta; primeiro carregamento tem placeholders e estado de espera. Falha limpa o resultado inválido, preserva os alertas independentes e oferece nova tentativa.
- Seletor de moto e botões têm altura44px e alinhamento pela base no desktop; no celular, seletor ocupa uma linha e ações dividem a seguinte.
- Cabeçalho com marca/descrição, busca com ícone, identificação da conta e tema. Menu mobile posicionado pela altura real do cabeçalho, com área externa para fechar.
- Rodapé com marca, frase de apoio e links reais para garagem, alertas e conta. Mantido fora da impressão; espaço reservado para o atalho flutuante no celular.
- Espaçamentos, quebra de textos e cards ajustados em folha complementar `frontend/src/styles/shell.css`. Estilo preto/verde, temas, navegação e funcionalidades existentes preservados; sem mudança de API, regras de negócio, schema, `.env` ou chaves.

## Validação desta revisão

- 38 testes frontend aprovados e build de produção atualizado.
- Chrome isolado:1920x1000,1440x900,768x1024,390x844 e320x740. Sem rolagem horizontal da página/cabeçalho; botão Atualizar menor que180px; bases do seletor/botões alinhadas com diferença máxima1px no desktop; cards principais visíveis no primeiro viewport desktop. Claro/noturno e capturas conferidos visualmente.
- Consulta do painel atrasada de propósito: cards mantidos e Atualizar desabilitado até finalizar. Falha503 simulada preserva cuidados pendentes e recupera os indicadores pelo botão de tentativa. Navegação do rodapé e fechamento do menu aprovados; nenhuma exceção JavaScript.
- XAMPP/MySQL e servidores de uso estavam desligados na verificação final. Testes visuais executados em API8010/preview5190 com SQLite isolado em tmp, usando migrations existentes e contas descartáveis próprias. Nenhum banco/dado real utilizado ou modificado. Isso não representa nova validação no MySQL ou Safari/iPhone físico.
- Regressão T3 no mesmo ambiente isolado: CRUD de gastos, troca de tipo, odômetro/manutenção, filtros/erros, duas motos/contas, relatórios, CSV, impressão/PDF, temas, CSRF/isolamento e logout aprovados após o novo shell. Fixtures e sessões próprias removidas.

Para visualizar, iniciar XAMPP/MySQL e API/frontend habituais conforme README e atualizar com Ctrl+F5. No PWA, fechar completamente e reabrir online para carregar o build. Aceites anteriores continuam limitados aos fluxos e versões registrados; esta revisão não enviou push nem iniciou scheduler.
