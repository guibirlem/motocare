# T1 — Alertas de manutenção

O comando `php artisan alertas:verificar` verifica os registros existentes em `alertas` a cada execução. O scheduler o executa a cada minuto; mantenha `php artisan schedule:work` em execução no ambiente local. Em produção, configure `schedule:run` a cada minuto e um cache compartilhado com suporte a locks.

## Configuração do Firebase

1. Habilite a API Firebase Cloud Messaging no projeto Firebase e obtenha o JSON de uma conta de serviço autorizada a enviar mensagens.
2. Guarde o JSON fora da pasta pública, por exemplo em `backend/storage/app/firebase-service-account.json` (ignorado pelo Git).
3. Configure `FIREBASE_CREDENTIALS` no `backend/.env` com o caminho absoluto desse arquivo. Execute `php artisan config:clear` após alterar a configuração.
4. O usuário precisa ter um token de registro FCM válido em `usuarios.fcm_token`, obtido por um cliente Firebase com permissão de notificações. Cadastro de usuário, autenticação e obtenção do token no frontend ainda dependem da implementação dessas funcionalidades.

O servidor utiliza OAuth 2.0 e a API HTTP v1, conforme a [documentação Firebase](https://firebase.google.com/docs/cloud-messaging/send/v1-api). A chave privada fica exclusivamente no backend.

## Contrato dos alertas

- Tipos aceitos na T1: `troca_oleo` e `revisao`.
- Crie o alerta com `status=pendente`, `nivel_urgencia=baixa` e `km_limite` positivo, vinculado à motocicleta do usuário.
- `km_limite` é a leitura absoluta do odômetro prevista para o serviço. O gatilho é `km_atual * 10 >= km_limite * 9`, inclusive em 90% e acima do limite.
- Após aceite do FCM, o status passa a `notificado`; urgência `media` antes do limite e `alta` ao atingir ou ultrapassar o limite. Aceite do FCM não confirma leitura ou entrega no dispositivo.
- Sem token ou com erro de envio, o alerta permanece pendente para nova tentativa. Falhas geram código de saída 1 e não impedem o processamento dos demais alertas.
- Um alerta notificado não é enviado novamente. Após o serviço, marque o registro anterior como `concluido` e crie outro alerta com o novo limite.
- Locks evitam envios simultâneos. Existe uma janela entre o aceite remoto e a gravação local em que uma interrupção pode causar reenvio; a tag web identifica o mesmo alerta.
- Alertas por data, histórico e financeiro não fazem parte da T1. Não houve alteração no schema.

## Validação

Execute `php artisan test` dentro de `backend`, com o PHP configurado no PATH. No Windows com instalação padrão do XAMPP, também é possível usar `C:/xampp/php/php.exe artisan test`. Os testes usam SQLite em memória e FCM simulado, sem acessar o banco MySQL local nem enviar push real. A instalação em outro computador está no [README da raiz](../README.md).

No PowerShell com XAMPP, caso o OpenSSL não encontre sua configuração, defina `$env:OPENSSL_CONF='C:/xampp/php/extras/ssl/openssl.cnf'` antes dos testes. Inicie o MySQL do XAMPP antes de executar o comando ou o scheduler com o cache padrão em banco.
