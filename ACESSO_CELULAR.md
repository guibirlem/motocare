# Acesso temporário pelo celular - preparado em 22/10/2026

## Situação

Revisão de negócio/segurança posterior em 22/10: Vite6.4.5, cabeçalhos CSP/nosniff/referrer/frame e caminhos privados404; API preserva cookiesseguros/sessão/CSRF. Previews atuais local18208/móvel18524. Manutenção concluída pode avançar odômetro; alertas usam referência/intervalo, conforme [REVISAO_LOGICA_SEGURANCA.md](REVISAO_LOGICA_SEGURANCA.md). Atualizar o app online após fechar completamente; os aceites visuais/push abaixo são anteriores e não foram repetidos manualmente nesta revisão. Testes/HTTP atuais constam do relatório.

Modo noturno adicionado em 22/10: lua/sol no cabeçalho do app; seletor também em login e offline. Escolha salva neste navegador/origem; sem escolha válida usa aparência do sistema ao abrir. Para receber a versão nova no iPhone, fechar completamente pela multitarefa e reabrir online. Usuário confirmou “Sim, tema escuro funcionando” após receber esse roteiro; aceite visual específico do tema concluído por confirmação manual. Revisão mobile/PWA anterior permanece registrada abaixo.

**Túnel autorizado explicitamente pelo usuário e aberto em 22/10/2026.** A recusa automática inicial foi superada pela autorização “autorizo pode abrir o tunel” para o destino Cloudflare/trycloudflare.com e trânsito do aplicativo/API/login/sessão pelo provedor.

Endereço temporário atual: **https://select-pond-spouse-diy.trycloudflare.com**. Funciona enquanto computador, MySQL, API móvel, preview móvel e cloudflared estiverem ativos. Não é uma URL permanente. Processos atuais: túnel PID 16344, API móvel PID 15812, preview móvel PID 18524; estado local em `tmp/mobile-access.json`. Conferir caminhos e processos antes de encerrá-los; PIDs podem mudar.

Correção offline de 22/10: tela “Modo offline” aparece antes do login e orienta conectar para consultar/salvar. Nova versão deve ser carregada com internet antes de repetir teste sem Wi-Fi/dados móveis. Ao reconectar há tentativa de recarga da sessão; não existe replay de gravações. JS/precache conferidos por HTTPS e 22 testes frontend/build aprovados. Usuário confirmou a mensagem/botão no iPhone após fechar completamente pela multitarefa e reabrir online; confirmou também que voltou a carregar ao reconectar. Atualização do shell não exige desativar notificações, remover o app ou trocar as chaves.

Correção iPhone de 22/10: Apple rejeitou o subject VAPID local com HTTP 403/BadJwtToken. Configurado `WEBPUSH_SUBJECT` no processo da API móvel para o endereço HTTPS atual acima, preservando chaves e inscrição. Apple passou a aceitar HTTP 201 e usuário confirmou recebimento no iPhone, inclusive pelo botão de teste. **Usar esse mesmo subject HTTPS no processo do scheduler ao testar alertas no iPhone**, pois um terminal novo que herde o valor local do .env voltará a receber a rejeição. Não é necessário gerar chaves ou inscrição novas.

O usuário informou que usará iPhone. Este documento prepara um acesso de teste, não uma publicação de produção. Financeiro/T3 continua desativado. Banco/schema, .env, APP_KEY e chaves VAPID não foram alterados.

## Estrutura preparada

- `frontend/scripts/mobile.mjs` (`npm run mobile`): serve o build em `127.0.0.1:5180` e encaminha `/api` para `127.0.0.1:8001`; exige `MOTOCARE_MOBILE_HOST` com o hostname exato gerado pelo túnel. Não aceita todos os hosts nem muda o preview local da porta 5173.
- API móvel separada, somente loopback, com `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_COOKIE=motocare-mobile-session`, `APP_URL` e `WEBPUSH_SUBJECT` HTTPS definidos no processo, sem editar .env. Não desativa sessão/CSRF/autorização e não publica MySQL.
- Ferramenta oficial cloudflared 2026.10.3 baixada em `tmp/tools/cloudflared.exe`; SHA-256 conferido: `76b2805253d0d6ffacb092a7acfbee99e7af6917835c0c5410e23f3ef5052da2`. Binário e temporários estão fora do pacote/Git.
- Revisão local usou hostname fictício `mobile-review.trycloudflare.com` apenas no cabeçalho Host. **Ele não é um endereço disponível para acessar.** Os processos dessa revisão foram encerrados e substituídos pelas instâncias com hostname real acima; servidores locais 5173/8000 preservados.

## Verificações realizadas

- Aceite final mobile/PWA de 22/10: usuário confirmou Dashboard, Manutenções, Garagem, Alertas, Configurações e formulários normais no app instalado. Offline/reconexão e push pelo botão já aprovados.
- Scheduler real às 15:47:51 (America/Sao_Paulo): alerta “MotoCare: Troca de óleo”, moto “Teste MotoCare iPhone”, 900/1.000 km, recebido com app em segundo plano e confirmado pelo usuário. Em 899 km permaneceu pendente; repetição/verificação zero envios e zero falhas. Inscrição Apple reativada antes do envio; mesmo subject HTTPS definido no processo.
- Isolamento: como havia alerta elegível no MySQL de uso, teste do scheduler iPhone executado em SQLite descartável com migrations existentes e inscrição criptografada/push real. Banco temporário/fixtures removidos, dados originais de alertas/motos/inscrições conferidos preservados. Não foi alteração estrutural nem nova validação de persistência no MySQL. Nenhum scheduler contínuo iniciado.
- Conferência visual no iPhone feita pelo usuário; a automação visual do Chrome foi interrompida por não identificar a URL atual com segurança. Aceites valem para o aparelho/fluxos conferidos.
- Página e `/api/csrf` pela porta 5180: HTTP 200.
- Cookie de sessão com Secure/HttpOnly; API com no-store/private.
- `/api/user` sem autenticação: 401; rota inexistente: 404 sem trace/exception/file/line.
- `/.env` e `/backend/.env`: 404; Host não autorizado: 403.
- Sintaxe do script Node validada. Sem novos testes de funcionalidades ou validação em iPhone nesta preparação.
- Pelo HTTPS público: página, JS/CSS, manifest, ícones, sw.js e push-worker.js retornaram 200; CSRF 200 com cookies Secure/HttpOnly e no-store.
- Smoke autenticado pelo HTTPS público aprovou cadastro, logout/login, sessão, privacidade de /user e CSRF ausente retornando 419. Conta descartável removida; nenhum dado da conta do usuário usado no smoke.
- Notificação real no iPhone confirmada pelo usuário após corrigir WEBPUSH_SUBJECT, tanto no diagnóstico quanto pelo botão do app; scheduler confirmado posteriormente conforme acima. Abertura pelo app instalado, layout/formulários e ciclo offline/reconexão também confirmados. Não foram reexecutadas suítes completas nesta confirmação manual.

## Reabrir o acesso após encerramento

O usuário já autorizou este teste pela Cloudflare em 22/10. A URL muda em nova execução; atualizar o estado local e os comandos com o novo hostname, sem reutilizar URL encerrada.

1. Conferir MySQL, dependências e build atual (`npm ci` se necessário, depois `npm run build`). Preservar execução local em 5173/8000.
2. Encerrar apenas os processos de revisão móvel deste roteiro, se ainda estiverem ativos e seus caminhos/portas corresponderem. Não encerrar os servidores locais nem processos de terceiros.
3. Abrir o túnel com a ferramenta oficial: `cloudflared tunnel --no-autoupdate --url http://127.0.0.1:5180 --protocol http2`. Ler o hostname HTTPS gerado. O tráfego do aplicativo, inclusive login/sessão, passará pela Cloudflare. Quem possuir a URL poderá acessar a tela pública de login/cadastro; dados de cada conta continuam exigindo autenticação.
4. Em um terminal dedicado ao backend móvel, definir as variáveis abaixo no processo e executar o servidor. Substituir o marcador pelo endereço efetivamente gerado; não copiar uma URL antiga de outra sessão.

~~~powershell
$env:APP_DEBUG = 'false'
$env:SESSION_SECURE_COOKIE = 'true'
$env:SESSION_COOKIE = 'motocare-mobile-session'
$env:APP_URL = 'https://HOSTNAME-GERADO.trycloudflare.com'
$env:WEBPUSH_SUBJECT = $env:APP_URL
$env:OPENSSL_CONF = 'C:/xampp/php/extras/ssl/openssl.cnf'
php artisan serve --host=127.0.0.1 --port=8001 --no-reload
~~~

5. Em outro terminal dedicado ao frontend móvel:

~~~powershell
$env:MOTOCARE_MOBILE_HOST = 'HOSTNAME-GERADO.trycloudflare.com'
npm run mobile
~~~

6. Conferir por HTTPS público página, assets, manifest, workers, API/sessão/CSRF e cookies; só depois entregar a URL real ao usuário. Esses itens e o login já foram validados no túnel atual; repetir a conferência em um novo endereço.
7. Para encerrar, parar cloudflared e os processos móveis dedicados; não precisa alterar roteador/firewall. O endereço é temporário e muda ao criar outro túnel. O teste requer computador e processos ligados.

Para o scheduler, em um terceiro terminal dedicado no backend, definir `WEBPUSH_SUBJECT` para o mesmo HTTPS e `OPENSSL_CONF` conforme acima antes de executar `php artisan schedule:work`. Nesta sessão nenhum scheduler contínuo foi iniciado. Para instalação permanente, usar um contato VAPID público estável (site HTTPS ou e-mail válido) e preservá-lo na configuração privada apropriada; não depender da URL temporária de túnel.

## Roteiro para iPhone

1. Abrir a URL HTTPS efetiva no Safari. Para notificações, usar iOS 16.4 ou posterior.
2. Compartilhar → Adicionar à Tela de Início. Se houver opção de abrir como aplicativo, mantê-la ativada.
3. Abrir pelo ícone MotoCare instalado; entrar na conta. A sessão do app instalado pode exigir novo login.
4. Configurações → Ativar notificações → permitir → Enviar teste de notificação, uma vez. Verificar primeiro plano e segundo plano.
5. O último navegador ativado recebe avisos: ativar no iPhone substitui o vínculo da conta com o Chrome do computador. Para voltar ao computador, reativar nele.
6. Conferir navegação/formulários/tabelas em tela pequena, instalação e abertura pelo ícone. Testar offline: apenas shell/mensagem de indisponibilidade; dados da conta não devem ser consultados ou gravados offline.
7. Não marcar revisão mobile/PWA concluída apenas porque o endereço abre. Registrar resultados reais em TASKS/CONTINUACAO/MEMORY.

Fontes oficiais consultadas em 22/10/2026: [Cloudflare Quick Tunnels](https://developers.cloudflare.com/tunnel/get-started/quick-tunnels/), [release oficial cloudflared](https://github.com/cloudflare/cloudflared/releases/tag/2026.10.3) e [Web Push no iOS/WebKit](https://webkit.org/blog/13878/web-push-for-web-apps-on-ios-and-ipados/).
