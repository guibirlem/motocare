# Recuperação de senha — 23/10/2026

Integração aditiva à base da landing/T1/T2/T3. O login contém **Esqueci minha senha**; as telas `/esqueci-senha` e `/redefinir-senha` usam os temas existentes e funcionam sem uma sessão autenticada. Nenhuma tabela, migration ou dependência nova.

## Estado do envio

O usuário informou que ainda não possui SMTP e pediu para preparar a integração. O ambiente de uso continua com `MAIL_MAILER=log`, sem credenciais de envio. **E-mails externos ainda não são enviados.** A solicitação retorna 503 enquanto a entrega não estiver configurada, sem criar token nem registrar links nos logs. O restante do sistema continua disponível.

Atualização: usuário escolheu Gmail e informou seu remetente. Host `smtp.gmail.com`, porta587, esquema smtp e endereço foram preparados somente no `.env` privado, preservando os demais valores. Ainda falta a senha de app: MAIL_PASSWORD vazio, MAIL_MAILER=log e nenhum envio externo. O endereço pessoal não é incluído nestes documentos ou no Git.

## Concluir com Gmail

1. Entrar na Conta Google que será remetente e ativar a verificação em duas etapas.
2. Abrir [Senhas de app](https://myaccount.google.com/apppasswords), criar uma para MotoCare e copiar o valor. A opção pode não estar disponível em contas com certas restrições; consultar [ajuda oficial](https://support.google.com/accounts/answer/185833?hl=pt-BR).
3. Colar o valor **somente em `MAIL_PASSWORD` do `backend/.env` local** e definir `MAIL_MAILER=smtp`. Não usar a senha normal do Gmail nem enviar a senha de app ao chat. Usuário e remetente devem corresponder à conta autorizada.
4. Conferir FRONTEND_URL, executar `php artisan config:clear`, reiniciar a API e testar com uma conta própria. O envio/recebimento Gmail real ainda está pendente, sem alterar o aceite dos testes isolados anteriores.

## Configurar quando houver um provedor

Preencher **apenas no `backend/.env` local**, usando os dados fornecidos pelo serviço de e-mail. Não enviar credenciais pelo chat e não versioná-las:

```dotenv
FRONTEND_URL=https://seu-endereco-publico
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=host-fornecido-pelo-provedor
MAIL_PORT=587
MAIL_USERNAME=usuario-fornecido-pelo-provedor
MAIL_PASSWORD=senha-ou-chave-fornecida-pelo-provedor
MAIL_FROM_ADDRESS=remetente-validado@seu-dominio
MAIL_FROM_NAME="MotoCare"
```

Usar esquema/porta indicados pelo provedor; SMTP na porta 587 negocia STARTTLS quando anunciado pelo servidor. Para TLS implícito na porta 465, usar `MAIL_SCHEME=smtps`. O timeout SMTP é de dez segundos. O remetente deve estar autorizado pelo serviço; o exemplo `hello@example.com` é recusado para recuperação.

`FRONTEND_URL` precisa apontar para a **origem do frontend**, sem caminho/query/fragmento. Localmente: `http://127.0.0.1:5173`. Em acesso externo/iPhone: a origem HTTPS pública atual. HTTP é aceito somente em loopback. O link nunca usa Host, Origin ou endereço enviado pelo visitante. Ao trocar de túnel, atualizar essa configuração e reiniciar a API móvel correspondente. Não alterar APP_KEY/VAPID.

Depois de configurar, dentro de `backend`, executar `php artisan config:clear` e reiniciar a API se ela herdou variáveis antigas. Não é necessário iniciar fila de e-mails: o envio usa SMTP diretamente. Testar com uma conta própria, conferir spam, abrir o link, salvar a senha e entrar novamente. A confirmação em uma caixa postal real permanece pendente até essa configuração.

## Contratos e proteção

- POST `/api/forgot-password`: `{email}`. CSRF obrigatório. Com entrega habilitada, resposta 202 genérica para conta existente, inexistente ou solicitação repetida. Uma nova mensagem por minuto por conta, no máximo três solicitações por hora por e-mail e cinco por minuto por IP. Envio não altera a senha nem encerra sessões.
- POST `/api/reset-password`: `{email, token, password, password_confirmation}`. CSRF obrigatório, dez tentativas por minuto por IP e dez por hora por e-mail. Sucesso 200, entrada/link inválido 422, excesso 429 com Retry-After. Falhas de infraestrutura seguem o JSON saneado/no-store atual.
- Broker Laravel existente: token aleatório armazenado como hash em `password_reset_tokens`, expiração de 60 minutos, uso único. Solicitar novo link após o intervalo invalida o anterior. E-mail é comparado sem diferença de maiúsculas/minúsculas.
- Requisição e redefinição bloqueiam a linha do usuário em transação. Senha, revogação de sessões e consumo do token são concluídos juntos no banco padrão. Senhas mantêm as regras atuais: mínimo oito caracteres, máximo 72 bytes, sem NUL e confirmação igual. Não é criado `remember_token`, pois esse campo não existe neste schema.
- Sucesso encerra sessões de banco da conta e sua sessão atual, caso conectada; `auth.session` continua verificando o hash nos acessos protegidos. A inscrição push antiga é removida como no logout: reativar notificações após entrar novamente. Não altera motos, registros, alertas, postos, valores ou contas de terceiros; não faz login automático.
- Alterar senha nas Configurações também invalida links pendentes. Alterar e-mail invalida links do endereço antigo e do novo; editar apenas nome/telefone preserva o link. Cadastro de endereço reutilizado remove tokens órfãos. Contratos anteriores dessas operações preservados.
- Segredo no fragmento do link, removido imediatamente da URL ao abrir a tela; permanece somente na memória do formulário. Não entra em query, localStorage, sessionStorage, logs ou cache offline. Recarregar a tela limpa esse estado e orienta solicitar/reabrir o link do e-mail.
- Transporte `log`/fallbacks que possam gravar o conteúdo são recusados. `array` só é permitido no ambiente de testes. Erro SMTP não expõe conta/credenciais: token é removido e registra-se apenas aviso fixo administrativo. Resposta permanece genérica para evitar descoberta de contas. Falha de entrega deve ser investigada pelo administrador no serviço SMTP.
- Há um tempo mínimo de resposta para reduzir diferenças entre contas; envio síncrono depende da latência do SMTP e não representa garantia de tempo constante. Offline bloqueia os envios e não enfileira/reexecuta senhas.

## Validação executada

- **77 testes backend / 771 verificações**: suíte completa, incluindo 16 testes novos de recuperação, regressões de cadastro/login/perfil, CSRF, limites, hash/expiração/reutilização, tokens de terceiros, remetente/configuração insegura, falha de transporte, revogação e reuso de e-mail.
- **43 testes frontend** e build de produção aprovados, sem novas dependências.
- Chrome com perfil descartável, SQLite isolado e SMTP TCP apenas em `127.0.0.1:2526`: cadastro/login reais, solicitação, recebimento da mensagem no capturador, abertura do link, confirmação inválida, nova senha, senha antiga rejeitada, sessão de outro navegador revogada e reutilização rejeitada.
- Formulários conferidos em 320/390/768/1440px, claro/noturno; screenshots revisadas. Sem overflow horizontal nem exceção JavaScript. Recarga sem segredo, serviço503 e offline/reconexão tratados.
- Pint nos PHP alterados e Composer validate aprovados. Fixtures removidas; nenhum e-mail externo, push real, scheduler contínuo, alteração de dados MySQL de uso ou configuração privada.

O teste de transporte local comprova a integração SMTP, não a entrega por um provedor externo. Sem novo aceite em Safari/iPhone físico. PDF acadêmico anterior permanece histórico e não foi regenerado nesta alteração.

Acesso local mantido em `http://127.0.0.1:5173/esqueci-senha`, com API na porta8000. Na checagem final o MySQL do XAMPP ficou indisponível: reativá-lo para usar login/contas/API. Essa checagem de disponibilidade não substitui o fluxo integrado aprovado no ambiente isolado. Servidores descartáveis8010/5190 encerrados.

Referências de implementação: [broker Laravel](https://laravel.com/docs/12.x/passwords) e [orientações OWASP](https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html).
