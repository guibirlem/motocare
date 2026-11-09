# Correção do botão Entrar — 23/10/2026

## Problema e correção

Reproduzido no Chrome isolado em `http://127.0.0.1:5173/`: clicar em Entrar apontava para `/dashboard`, mas a URL permanecia `/` e o formulário não aparecia. `/dashboard` era alias da raiz; o Vue Router considerava essa navegação duplicada.

Raiz e `/dashboard` agora possuem registros distintos, reutilizando HomeView e os mesmos metadados. A raiz continua mostrando a apresentação para visitantes e o painel para contas conectadas. Login/cadastro continuam no AccountGate existente. O menu Dashboard conserva destaque e `aria-current` nos dois endereços. Nenhuma alteração no backend, schema, dados, estilos, credenciais ou regras de negócio.

## Validações desta correção

- 55 testes frontend e build aprovados.
- Chrome isolado no endereço local5173, 390 e1440px: Entrar abre `/dashboard`; CTA gratuito abre cadastro; voltar restaura apresentação; acesso direto ao painel, recuperação e apresentação preservados. Sem exceções JavaScript.
- Chrome/SQLite isolado, API8010/preview5190: cadastro real, raiz autenticada, destaque do Dashboard, apresentação autenticada, relatórios, logout e proteção de acessos diretos aprovados. Conta descartável e sessões removidas.
- Apresentação conferida em320/390/768/1024/1440/1920px, claro/noturno, âncoras/menu sem overflow; API503, offline/reconexão e movimento reduzido preservados.

Build local atualizado; se uma aba antiga ainda usar o JavaScript anterior, recarregar com Ctrl+F5. Sem novo teste físico no iPhone, envio de e-mail/push ou execução do scheduler. Os resultados backend anteriores permanecem históricos; não houve alteração de backend nesta correção.
