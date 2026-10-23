# Entrega MotoCare - T1/T2/T3 - 23/10/2026

## Código e histórico

[Repositório](https://github.com/guibirlem/motocare), [branch T3](https://github.com/guibirlem/motocare/tree/feature/t3-financeiro) e [histórico da branch](https://github.com/guibirlem/motocare/commits/feature/t3-financeiro/). Commit desta integração identificado pela mensagem `feat: integra financeiro T3 e relatorios`. Não fazer merge automático em master; histórico T1/T2 preservado, base 5cdce58. A mensagem anterior `update task1 e task2 funcionando` e seu commit 5d554ed permanecem no histórico.

## Entrega integrada

T1/T2 mantidos e T3 integrada: CRUD de combustível/reparos, filtros/totais, relatórios por período/moto, meses vazios, CSV pt-BR e impressão/Salvar PDF. FEATURE_FINANCEIRO=true por padrão; GET autenticado /api/features sincroniza menu/rotas, e false mantém financeiro 404 sem afetar T1/T2. Nenhuma migration nova, sem alteração de chaves/.env ou dados da conta do usuário.

Validação: 54 testes backend / 486 verificações e 34 frontend/build; Pint alterados/Composer validate; Chrome isolado desktop/móvel e HTTP/MySQL com duas contas próprias, CSV/PDF, CSRF 419, isolamento 404 e logout 401. Fixtures/sessões removidas. Detalhes e limites em [VALIDACAO_T3.md](VALIDACAO_T3.md); aceite novo no Safari/iPhone físico ainda complementar.

## Arquivo único da Parte 2

[PDF consolidado](output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf), edição de 23/10 com dez páginas: código/histórico, arquitetura, DER, backlog T1/T2/T3, atas retrospectivas, regras, segurança e evidências da integração T3. As atas derivam dos registros; não inventam reuniões ou participantes. A nova edição substitui o PDF de 22/10, que tratava T3 como adiada.

## Instalar e continuar

Seguir [README.md](README.md). Restaurar dependências com composer install/npm ci, conferir migrations e preservar APP_KEY/VAPID/.env existentes. Se herdou FEATURE_FINANCEIRO=false, habilitar no ambiente pretendido e limpar cache/reiniciar se necessário. O cadastro de manutenção não cria despesa automática: lançar cada serviço uma vez.

Ler [CONTINUACAO](CONTINUACAO.md), [TASKS](TASKS.md), [SPEC](SPEC.md), [MEMORY](MEMORY.md), depois [continuidade da T3](RETOMADA_T3.md). Túnel temporário e execução do scheduler são operações separadas; para notificações, manter scheduler ativo com contato VAPID válido.

## Cópia local e segurança de entrega

Pacote auxiliar `entrega/MotoCare-T1-T2-T3-2026-10-23.zip`, manifesto `entrega/fontes-t1-t2-t3-2026-10-23.json` e checksum `entrega/SHA256SUMS-T1-T2-T3-2026-10-23.txt`. Conferir com Get-FileHash -Algorithm SHA256. Pacotes anteriores são históricos; não sobrescrever fontes atuais com eles.

Git/pacote incluem fontes, documentação, exemplos, lockfiles e PDF. Não incluem .env, chaves privadas, dependências instaladas, build, logs, caches, binários auxiliares ou dados de teste. Diretório entrega é ignorado no Git por duplicar os fontes; usar clone/Download ZIP da branch para transferência pelo GitHub.
