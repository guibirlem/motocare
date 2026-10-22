# Revisão de lógica e segurança - 22/10/2026

Escopo: T1/T2 e suporte de conta, garagem, notificações, PWA e entrega local. Financeiro permanece desativado. Revisão por leitura, testes de regressão, HTTP/MySQL com contas descartáveis e auditorias de dependências; não é garantia de ausência de toda vulnerabilidade possível. Aceites visuais/push anteriores não foram repetidos manualmente nesta revisão.

## Correções verificadas

| Área | Problema ou regra revisada | Resultado |
| --- | --- | --- |
| Manutenção e odômetro | Histórico concluído podia ficar acima do odômetro usado pelo scheduler | Usuário escolheu avanço automático. POST/PUT concluídos elevam o odômetro somente quando o KM é maior, na mesma transação com bloqueio da moto. Histórico menor, pendência, edição para KM menor e exclusão não reduzem o odômetro |
| Data de serviço | Registro futuro podia ser marcado como feito | Concluído exige data até hoje; pendente pode ser planejado no futuro. Interface usa data de São Paulo, como o backend |
| Atomicidade | Falha ao sincronizar KM poderia deixar gravação parcial | Teste injeta falha e confirma rollback de manutenção e odômetro |
| Intervalo de alertas | 90% do odômetro absoluto podia avisar imediatamente numa moto com KM alto | Usuário escolheu 90% entre KM de referência do serviço e próximo limite. Campo km_base persistido, formulário/progresso/scheduler alinhados e migration aditiva aplicada com SPEC/DER |
| Concorrência de alertas | ID com zeros à esquerda podia usar nome de lock diferente do scheduler | API resolve primeiro o ID real do alerta pertencente à moto; edição/exclusão usam o mesmo lock canônico do scheduler |
| Interface/sessão | Resposta atrasada podia restaurar dados depois de sair, trocar de conta ou atualizar | Respostas são vinculadas à geração da sessão; antigas são descartadas, inclusive erros 401/419. Atualização mais lenta da garagem não substitui a mais nova |
| Garagem | Normalização de placa dependia do cliente | API também normaliza caixa, espaços e hífen antes da validação/unicidade |
| Senhas novas | Bcrypt pode truncar acima de 72 bytes; caractere nulo pode causar falha | Cadastro/troca validam até 72 bytes e rejeitam NUL. Senhas existentes não foram regravadas. Campos de senha atual inválidos geram 422, sem alterar a conta |
| Conta | Tentativas de senha atual com sessão autenticada sem limite específico | Perfil e senha têm limites separados de dez tentativas/minuto; login/cadastro e teste push mantêm seus limites |
| Erros da API | Debug podia expor mensagem interna, caminho e stack trace | Erros são sanitizados mesmo com debug ligado; 422 mantém os erros de validação. Retry-After e códigos HTTP preservados |
| Entrega HTTP | Defesa de conteúdo dependia dos padrões do preview | CSP, nosniff, Referrer-Policy e bloqueio de frame no preview local/móvel; caminhos privados/dotfiles recebem 404 explícito, antes do fallback da SPA |
| Dependências | Avisos em Vite/esbuild, follow-redirects e source-map-js | Vite 6.4.5 e plugin Vue 5.2.4 compatíveis; transitivas corrigidas e lockfile atualizado. npm audit oficial: zero vulnerabilidades conhecidas; Composer audit: zero avisos e zero pacotes abandonados |
| Financeiro preservado | PUT de combustível mantinha posto antigo se omitido | Opcional normalizado para null com regressão; feature continua false |

Chave pública VAPID é necessária ao navegador para Web Push e não equivale à chave privada de envio. A chave privada, APP_KEY, senhas e inscrição push não são retornadas em `/api/user`; testes cobrem ocultação e criptografia da inscrição. Rotas de cada moto e registro continuam vinculadas ao dono da sessão, com CSRF nas gravações. Não foram criadas credenciais públicas nem alteradas chaves/.env.

## Dados existentes

Usuário confirmou explicitamente as duas reconciliações encontradas no banco: Kawasaki Ninja, 66.777 → 66.779 km; Moto Duas rodas, 0 → 122 km. Aplicadas em transação apenas aos IDs/valores previamente conferidos. Consulta posterior: zero motos abaixo do maior KM de manutenção concluída não futura, zero manutenções concluídas futuras e zero concluídas sem KM. Nenhum outro dado do usuário foi reconciliado ou excluído.

Registrar manutenção não conclui nem reinicia um alerta automaticamente: o próximo limite depende de uma decisão explícita do usuário em Alertas. O odômetro atualizado passa a ser usado na próxima verificação do scheduler. Nenhum envio adicional ou scheduler contínuo foi iniciado nesta auditoria.

## Evidências disponíveis

- Backend: 45 testes, 391 verificações aprovados, em SQLite isolado; incluem odômetro, rollback, intervalo/migration/locks de alertas, integração com scheduler usando transporte simulado, propriedade, CSRF real sem bypass, ocultação de erros e senhas.
- Frontend: 28 testes aprovados, incluindo intervalo/progresso, sessão atrasada, garagem fora de ordem, KM confirmado e virada da data em São Paulo. Build Vite 6.4.5 aprovado.
- HTTP/MySQL: contas descartáveis próprias da auditoria; 899 → 900 → 1.200 km, histórico menor/pendência/exclusão sem regressão, futuro concluído 422, alteração indevida de KM 422, outra conta 404, CSRF ausente 419, logout seguido de 401 e campos privados ocultos. Contas/sessões/registros descartáveis removidos; nenhum push enviado.
- HTTPS autorizado: HTML, JS, manifest, precache e workers 200; `/api/user` anônimo 401; `.env`, backend/.env, chave privada, .git/config e variantes codificadas/`@fs` 404. Cookies móveis Secure/HttpOnly, API no-store/private, nosniff conferidos.
- Fontes elegíveis/build comparados aos valores privados locais de APP_KEY/VAPID e a padrões de credenciais: nenhuma ocorrência encontrada. Relatórios completos temporários não integram o pacote; arquivos privados/dependências/build/logs continuam excluídos.
- Pint nos arquivos alterados e Composer validate aprovados. Pint global sinalizou somente formatação legada de quatro migrations não alteradas; não é alteração de schema nem falha funcional.

## Regra dos 90% escolhida e aplicada

Usuário escolheu “Usar 90% do intervalo entre revisões”. A regra anterior de 90% do odômetro absoluto foi substituída por `km_base + ceil((km_limite - km_base) * 9 / 10)`, com comparação inteira inclusiva no scheduler. Exemplo: referência 66.779 km e limite 71.779 km, aviso em 71.279 km. Teste confirma nenhum envio na criação/em 71.278 km, um envio em 71.279 e nenhum reenvio; data vencida e limite atingido continuam notificando.

Migration `2026_10_22_000001_add_km_base_to_alertas_table.php` aplicada no MySQL, com campo nullable unsigned integer e atualização do DER/SPEC. Alertas pendentes antigos com limite futuro partem do odômetro atual; vencidos usam base zero e seguem vencidos. Notificados/concluídos preservam status/cálculo histórico; apenas data usa null. Teste de migration cobre o backfill e consulta real encontrou zero referências pendentes inválidas. Nenhuma notificação foi disparada pela migration.

Ao cadastrar, confira o KM de referência do último serviço: padrão é o odômetro atual quando abaixo do próximo limite; uma referência explícita deve ser menor que o limite e não superar o KM atual. Isso não descobre automaticamente uma revisão sem registro. Concluir um alerta e definir o próximo limite continuam ações explícitas.

## Reprodução e limites

No backend, com OPENSSL_CONF do XAMPP: `php artisan test`, `php composer.phar audit --locked` (ou Composer instalado) e `composer validate`. No frontend: `npm ci`, `npm test`, `npm run build` e `npm audit`. Usar SQLite de testes; nunca `migrate:fresh` no MySQL com dados.

Auditorias de dependências refletem os avisos publicados no momento da consulta. Não foi feito teste de carga nem teste simultâneo com vários processos MySQL; os bloqueios/transações foram revisados e rollback/monotonicidade validados. A revisão em si não publicou fontes; a atualização Git posterior de 22/10, com mensagem `update task1 e task2 funcionando`, reúne esta revisão, documentos e PDF sem reexecutar os aceites manuais. Configuração de produção continua exigindo HTTPS, debug desligado, cookie seguro, servidor apontado para os diretórios públicos corretos e scheduler ativo com contato VAPID válido. O túnel é temporário; previews não são um servidor de produção.

Referências oficiais: [limite do bcrypt no PHP](https://www.php.net/manual/en/function.password-hash.php), [npm audit](https://docs.npmjs.com/cli/v10/commands/npm-audit), [Composer audit](https://getcomposer.org/doc/03-cli.md#audit). Resultados específicos desta cópia estão nos testes e nas verificações acima.
