# Alertas T1 com Web Push

Complemento de 23/10: as categorias reais de moto de `App\Support\MotorcycleServices` também podem ter alertas por KM/data, incluindo lubrificação, tensão da corrente, kit, componentes dianteiros/traseiros, filtros, suspensão, arrefecimento, cabos e vela. Óleo e revisão mantêm o mesmo comportamento. O atalho “Peguei chuva / Lavei a moto” cria um alerta de lubrificação com data de hoje; repetir enquanto ativo reutiliza seu ID. O destaque é imediato no dashboard e a entrega push depende do scheduler/inscrição. Concluir esse alerta continua sendo explícito. Sugestão de ciclo de 500 km na interface é editável conforme manual/uso. Nenhuma inscrição ou chave é substituída por essa extensão.

Revisão de 22/10: usuário escolheu 90% do intervalo. Atualizar com `php artisan migrate` para acrescentar km_base, sem recriar tabelas. Exemplo: referência 66.779/limite71.779, aviso71.279. Pendentes antigos futuros recebem odômetro atual; vencidos permanecem vencidos e status terminais são preservados. Manutenção concluída pode avançar o odômetro; alertas continuam com conclusão/reprogramação explícitas. Referência é editável em pendentes e deve representar o último serviço. Evidências e segurança em [REVISAO_LOGICA_SEGURANCA.md](../REVISAO_LOGICA_SEGURANCA.md).

Aceite iPhone em 22/10/2026: após reativação pelo usuário, `schedule:run` real às 15:47:51 (America/Sao_Paulo) notificou óleo em 900/1.000 km e houve confirmação de recebimento com app em segundo plano. Em 899 km permaneceu pendente; repetição/verificação zero envios/zero falhas. Para preservar alerta elegível no MySQL, usado SQLite descartável com migrations existentes e push real; cenário removido, dados originais preservados. WEBPUSH_SUBJECT HTTPS definido no processo; nenhum scheduler contínuo iniciado. Conferência de layout/formulários e offline/reconexão no iPhone também aprovada pelo usuário. Detalhes em CONTINUACAO/TASKS na raiz.

A T1 usa Push API/Service Worker e VAPID local, com minishlink/web-push no PHP. Não exige projeto, SDK, conta de serviço ou console Firebase. O navegador fornece seu próprio serviço de entrega; Chrome pode usar um endpoint Google internamente, sem configurar uma conta Firebase no MotoCare.

## Preparar

Dentro de backend, com .env/APP_KEY e dependências restauradas:

~~~powershell
php artisan migrate
php artisan push:configurar
~~~

O comando gera e valida as chaves locais em storage/app/private/webpush-vapid.json. Não imprime os valores e preserva um arquivo válido existente. Execute uma vez em cada instalação independente. Não publique esse arquivo nem gere novas chaves a cada atualização. Preserve também APP_KEY, usada para criptografar as inscrições no banco.

WEBPUSH_SUBJECT no .env identifica o contato VAPID. O padrão atende desenvolvimento local; em produção use seu email (mailto:voce@seu-dominio.com) ou site HTTPS. WEBPUSH_KEY_PATH é opcional para guardar o arquivo privado em outro local do servidor. Depois de alterar configuração, executar php artisan config:clear.

Compatibilidade Safari/iPhone confirmada em 22/10: o contato local foi rejeitado pela Apple com HTTP 403/BadJwtToken. Usar site HTTPS acessível ou e-mail externo válido; para o túnel atual, WEBPUSH_SUBJECT foi definido no processo com o HTTPS do MotoCare. API móvel reiniciada preservando chaves/inscrição; HTTP 201 e recebimento pelo botão no iPhone confirmados pelo usuário. Aplicar o mesmo contato no terminal do scheduler; o valor local do .env não foi alterado automaticamente. `configured=true` valida configuração local, não a aceitação pelo provedor.

No XAMPP, habilite as extensões exigidas pelo Composer, incluindo openssl, curl, mbstring e zip; pdo_mysql para execução e pdo_sqlite para testes. O código detecta openssl.cnf no caminho padrão do XAMPP/Windows; em instalação diferente, configurar OPENSSL_CONF no ambiente.

## Usar

1. Iniciar API/frontend conforme o [README](../README.md) e entrar na conta.
2. Abrir Configurações → Ativar notificações; permitir notificações no navegador.
3. Usar Enviar teste de notificação e conferir o recebimento real, com a página aberta e em segundo plano.
4. Manter o scheduler em outro terminal backend:

~~~powershell
php artisan schedule:work
~~~

Verificação avulsa: php artisan alertas:verificar. Em produção, executar schedule:run a cada minuto e usar cache compartilhado com suporte a locks.

Push exige conexão, permissão e navegador compatível em HTTPS (localhost/127.0.0.1 para desenvolvimento). Bloqueios do sistema/navegador ou fechamento forçado podem impedir a entrega. Se a permissão tiver sido negada, liberá-la nas configurações do navegador antes de ativar novamente.

O último navegador ativado recebe os avisos da conta. Desativar remove o vínculo somente se corresponder ao navegador atual e tenta cancelar a inscrição local. Logout remove o vínculo da conta e cancela a inscrição local. Uma troca de conta não reutiliza a inscrição automaticamente. Quem usava a versão anterior precisa ativar de novo.

## Regras

- Óleo/revisão: km_limite é a leitura absoluta prevista do odômetro, positiva; km_base é a referência do serviço. Disparo inclusivo em km_atual * 10 >= km_limite * 9 + km_base OU data_limite <= hoje, no fuso APP_TIMEZONE (America/Sao_Paulo por padrão). O progresso é do intervalo km_limite - km_base, não do odômetro desde zero.
- Estados: pendente → notificado após aceite HTTP 2xx do serviço do navegador → concluido após serviço. Urgência média antes do limite e alta no limite/acima ou na data.
- Sem inscrição ou em falha, preservar pendente. Erros de envio produzem saída 1 sem impedir outros alertas. Inscrições expiradas (404/410) são desvinculadas; reativar o navegador para nova tentativa.
- Não reenviar notificados. Concluir e criar o próximo limite explicitamente; registrar manutenção concluída pode avançar odômetro, mas não conclui/reinicia alertas automaticamente.
- Locks evitam concorrência. Há uma janela entre aceite remoto e gravação local na qual interrupção pode causar reenvio; tag estável identifica o alerta na notificação.
- O teste não cria/conclui alertas; aceite não comprova entrega/leitura no dispositivo.

## Contrato e segurança

GET /api/push-config fornece configured, publicKey e subscriptionRegistered, sem chave privada. PUT /api/push-subscription recebe endpoint HTTPS e keys.p256dh/auth do navegador; DELETE recebe endpoint. POST /api/push-test exige o endpoint vinculado ao usuário, sessão/CSRF e respeita três tentativas/minuto. Todos exigem autenticação.

A inscrição é criptografada em usuarios.push_subscription; push_endpoint_hash é único e permite desvincular o mesmo navegador de uma conta anterior. Não retornar esses campos em /user. Somente destinos oficiais de navegadores são permitidos; sem credenciais/fragmentos/portas customizadas nem redirecionamentos no transporte. Logs não imprimem inscrição/chaves. fcm_token permanece apenas como coluna legada oculta, sem uso.

## Evidências e conferência final

Em 22/10/2026, o usuário confirmou recebimento real em primeiro plano, do teste “MotoCare: teste em segundo plano” com Chrome minimizado e, em seguida, do alerta “MotoCare: Troca de óleo” enviado pelo scheduler real às 14:59 (America/Sao_Paulo), da moto temporária Teste MotoCare T1 em 900 km/limite 1.000 km. O alerta persistiu notificado/urgência média; uma nova verificação enviou zero alertas. O cenário anterior em 899 km permaneceu pendente/zero envios. Todas as fixtures foram removidas. Não há confirmação separada de banner flutuante ou de funcionamento com Chrome encerrado. O aceite remoto não foi usado sozinho como comprovação: houve confirmação do usuário.

A execução real foi avulsa via schedule:run. Manter schedule:work no uso diário. PWA geral, layout móvel e Chrome encerrado não estão aprovados por esse teste.

Diagnóstico operacional: ambiente restrito falhou com cURL 7; usar processo da API com acesso externo. Não desabilitar TLS. No XAMPP, OPENSSL_CONF deve estar definido antes de iniciar PHP quando a geração EC falhar. Backend completo revalidado: 29 testes/246 verificações; após ajuste 429, 8 testes WebPush/74 verificações e 20 testes frontend/build aprovados. Nenhuma suíte nova na confirmação manual final.

O teste permite três tentativas/minuto. HTTP 429 é exibido em português com Retry-After e contagem regressiva no botão. Não clicar repetidamente nem limpar cache para contornar o limite. Para repetir em outra instalação: conferir abaixo de 90%, atingir a fronteira, executar scheduler, observar o recebimento e repetir o verificador para checar ausência de duplicação. Registrar evidências em TASKS/CONTINUACAO.

Referências: [PushManager.subscribe (MDN)](https://developer.mozilla.org/en-US/docs/Web/API/PushManager/subscribe) e [Web Push PHP](https://github.com/web-push-libs/web-push-php).
