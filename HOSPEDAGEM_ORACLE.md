# Hospedagem do MotoCare na Oracle Cloud — preparação de23/10/2026

## Estado real

Preparação iniciada. Nenhum recurso OCI foi criado, nenhum dado foi transferido e nenhum serviço local/túnel foi interrompido. Não há CLI/configuração OCI ou navegador conectado nesta sessão. A etapa seguinte depende da conta Oracle do usuário e de acesso autorizado à VM. Não enviar senha, cartão, código MFA ou chave privada pelo chat.

Os arquivos em [deploy/oracle](deploy/oracle) são modelos revisáveis, ainda não instalados em Linux. Preservam Vue/Vite, API Laravel12, MySQL8.0/InnoDB e Web Push VAPID. Não alteram rotas, telas, schema ou regras. A instalação e a validação real de Nginx/PHP-FPM/systemd/HTTPS ainda precisam acontecer na VM.

## 1. Conta e instância gratuita

1. Criar ou entrar na conta pelo [Oracle Cloud Free Tier](https://www.oracle.com/cloud/free/). Cadastro, cartão, senha e MFA ficam diretamente no site da Oracle; o usuário conclui a aceitação dos termos.
2. Usar a região principal da conta e conferir recursos existentes. Escolher somente opções **Always Free**, sem upgrade pago, marketplace licenciado ou consumo baseado apenas em créditos temporários.
3. Proposta inicial: VM `VM.Standard.A1.Flex`, Ubuntu24.04LTS, **2OCPUs,6GB de RAM e50GB de boot**. Confirmar rótulo de gratuidade e o total já utilizado na conta antes de criar. Os limites consultados para conta Always Free são2OCPUs/12GB no conjunto A1 e200GB de volumes; capacidade disponível na região não é garantida. Se faltar capacidade, parar e escolher com o usuário o próximo passo, sem mudar para um recurso pago.
4. Criar a rede/sub-rede pública necessária e endereço público para a VM. SSH/22 permitido somente a partir do IP administrativo; HTTP/80 e HTTPS/443 para o site. MySQL/3306 permanece em loopback, sem regra pública. Não modificar outras aplicações ou redes da conta.
5. Gerar/guardar a chave SSH em local privado; adicionar somente sua chave pública na instância. Confirmar a impressão digital do servidor antes de aceitar a primeira conexão. Informações necessárias para continuar: IP público, usuário SSH (normalmente ubuntu), região e confirmação da forma de acesso. A chave privada não deve aparecer no chat ou no Git.

Referências: [recursos Always Free e limites](https://docs.oracle.com/en-us/iaas/Content/FreeTier/freetier_topic-Always_Free_Resources.htm), [conta e verificação](https://www.oracle.com/cloud/free/faq/), [criar/conectar instância Linux](https://docs.oracle.com/en-us/iaas/Content/Compute/tutorials/first-linux-instance/overview.htm). Instâncias ociosas podem ser recuperadas pela Oracle; o plano gratuito não substitui backups.

## 2. Origem HTTPS

Escolher um domínio ou subdomínio estável apontando para o IP da VM; um subdomínio gratuito pode ser usado. Não é obrigatório comprar domínio. O provedor e o nome ainda precisam ser definidos; `motocare.example.com` nos modelos é apenas marcador e deve ser substituído.

A mesma origem servirá o frontend e `/api`, preservando sessão/CSRF. Configurar APP_URL, FRONTEND_URL e WEBPUSH_SUBJECT para esse HTTPS, sem usar o endereço do túnel em produção. PWA/notificações no iPhone exigem HTTPS válido; na origem nova, instalar pela Tela de Início e ativar notificações novamente.

## 3. Preparar a VM, sem publicar a aplicação ainda

Na VM Ubuntu confirmada, instalar Nginx, PHP8.3 CLI/FPM e extensões Laravel/Web Push, MySQL8.0, Composer2 e Certbot pelos canais oficiais. Confirmar versões e arquitetura disponíveis antes de instalar; não usar scripts de terceiros não revisados. Dependências PHP incluem pdo_mysql, curl, mbstring, xml, zip, bcmath, gmp e OpenSSL. Verificar com `composer check-platform-reqs --no-dev`.

O build do Vue pode ser feito no computador e transferido por SSH; Node não precisa ficar executando em produção. Usar npm ci e npm run build. O servidor usa Nginx/PHP-FPM; não usar artisan serve nem Vite dev como servidores públicos.

Estrutura proposta:

```text
/var/www/motocare/releases/<release>/    fontes, vendor e frontend/dist
/var/www/motocare/current               symlink para a release ativa
/var/www/motocare/shared/backend.env    configuração privada
/var/www/motocare/shared/private/       VAPID existente
/var/www/motocare/shared/storage/       storage persistente do Laravel
/var/backups/motocare/                  backups privados fora do repositório
```

Criar releases como usuário de implantação. O usuário www-data deve ler o código/configuração e escrever apenas em storage/bootstrap/cache. Configuração privada e VAPID com permissão640 e grupo www-data; diretórios privados750. Storage compartilhado e bootstrap/cache precisam permitir gravação por www-data. Não aplicar permissão777 nem tornar o código inteiro gravável pelo servidor web.

Instalar dependências a partir de backend/composer.lock com `composer install --no-dev --prefer-dist --optimize-autoloader`, sem composer update. A release deve conter o build feito pelo script local do projeto, incluindo precache.json e workers.

## 4. Preservar banco e chaves

Antes da transferência, fazer backup consistente do banco atual em arquivo privado e registrar contagens por tabela. Não colocar dump em Git, na pasta pública ou no pacote público de fontes. A cópia deve ser transferida por SSH para o servidor confirmado.

Criar banco dedicado e usuário MySQL próprio, restrito ao banco motocare e ao acesso local. Nunca usar o root sem senha do XAMPP em produção. Importar o backup em banco novo; não executar migrate:fresh, db:wipe ou seed em dados existentes. Conferir contagens e registros antes de ativar o site. Revisar migrate:status e rodar apenas migrations pendentes necessárias, após backup, com `migrate --force`; não gerar mudança estrutural nesta preparação.

Para levar os dados atuais, **preservar APP_KEY e o arquivo VAPID existentes** por transferência privada. APP_KEY protege dados cifrados; trocar a chave pode impedir leitura de inscrições push. Não executar key:generate nem substituir VAPID ao migrar o banco atual. Nenhum segredo existente foi exportado nesta etapa.

Usar [modelo de ambiente](deploy/oracle/.env.example) como roteiro, mantendo as chaves reais somente no arquivo privado do servidor. Ligar backend/.env a shared/backend.env e backend/storage a shared/storage, preservando previamente o conteúdo de storage. Novas releases reutilizam os mesmos arquivos privados. SMTP continua desativado até ser configurado e validado; esta preparação não envia e-mail.

## 5. Certificado e Nginx

1. Definir DNS e substituir o domínio nos dois modelos Nginx.
2. Criar `/var/www/letsencrypt` e instalar somente [nginx-bootstrap.conf.example](deploy/oracle/nginx-bootstrap.conf.example) como site inicial. Ele serve o desafio ACME e retorna503 para a aplicação ainda não publicada.
3. Executar nginx -t antes de recarregar Nginx. Na VM dedicada, conferir e desativar apenas o site padrão conflitante, sem apagar outras configurações.
4. Emitir certificado Certbot por webroot para o domínio, com `/var/www/letsencrypt`; aceitar os termos e informar o contato diretamente no servidor conforme a etapa concreta. Confirmar renovação automática e teste de renovação.
5. Instalar [nginx-https.conf.example](deploy/oracle/nginx-https.conf.example), ajustando caminhos/socket PHP-FPM/certificado. Executar nginx -t e só recarregar após sucesso. A raiz pública é frontend/dist; apenas a API executa backend/public/index.php.

Antes de publicar, executar `sudo -u www-data php /var/www/motocare/current/deploy/oracle/verify.php`. O [verificador](deploy/oracle/verify.php) lê configuração, build, estado VAPID e SELECT de metadados MySQL; não imprime segredos, não migra nem envia notificações. Deve terminar sem pendências. Não comprova sozinho HTTPS externo, entrega SMTP/push ou integridade da importação.

## 6. Validação e scheduler

Confirmar pelo HTTPS: frontend/build/PWA200, /api/csrf200 com no-store e cookies Secure/HttpOnly, /api/user anônimo401, caminhos de .env/storage/vendor/deploy404. Validar entrar/sair, uma conta isolada, isolamento de recursos, formularios/CRUD, relatórios, temas/mobile e o histórico importado sem alterar dados reais.

Somente depois de app/banco/origem/VAPID conferidos, instalar [service](deploy/oracle/motocare-scheduler.service) e [timer](deploy/oracle/motocare-scheduler.timer) em /etc/systemd/system. Conferir caminhos/permissões e executar systemd-analyze verify antes de ativar; systemctl daemon-reload e systemctl enable --now motocare-scheduler.timer. O timer executa schedule:run a cada minuto, como www-data, usando o mesmo .env da API. Não instalar também cron ou schedule:work para o mesmo app. A ativação poderá enviar os alertas reais já vencidos; preparar e conferir a transição antes de ligar.

Verificar list-timers e journalctl; fazer teste combinado de scheduler/push e confirmar recebimento no aparelho. O timer não está instalado ou executando nesta preparação. [Referências Laravel12](https://laravel.com/docs/12.x/deployment), [scheduler](https://laravel.com/docs/12.x/scheduling).

## 7. Entrada em produção e recuperação

Manter o ambiente atual disponível durante a validação. Fazer uma cópia final consistente dos dados antes da troca de uso, evitando gravações concorrentes em bancos separados. Trocar o acesso divulgado somente após conferir os dados e o HTTPS. Preservar backup e release anterior; rollback de código não desfaz migrations nem deve sobrescrever gravações novas com um dump antigo.

Definir e testar backup automático/restauração antes de considerar a migração concluída. Monitorar disco, erros e execução do scheduler. Estado nesta entrega: conta/VM/DNS/SSH, instalação, transferência privada e validação remota ainda pendentes, dependentes do acesso do usuário.
