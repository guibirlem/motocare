# Entrega MotoCare - 22/10/2026

## Fontes e histórico

Repositório público: [guibirlem/motocare](https://github.com/guibirlem/motocare). Branch de entrega: `master`. Atualização desta edição com a mensagem solicitada pelo usuário: **update task1 e task2 funcionando**. O SHA e os arquivos são verificáveis no [histórico de commits](https://github.com/guibirlem/motocare/commits/master/).

Código funcional de referência confirmado: [5d554ed](https://github.com/guibirlem/motocare/commit/5d554ed7b5ee68c903c86a2bc6b6d062d7e58def). O PDF consolidado após a publicação fixa esse commit, acrescenta a ata de publicação e detalha segurança/continuidade; as alterações desta consolidação são documentais.

A base remota anterior era `7f5b9cb` de 08/10/2026. A edição atual incorpora fontes integrados de 19/10 e revisão/aceites de 22/10, preservando o histórico e sem force push. O conector GitHub retornou HTTP 403 na tentativa de gravação; publicação pelo Git instalado, com autenticação de escrita disponível. O registro histórico desse bloqueio não representa o estado desta edição.

## Arquivo único da Parte 2

[MotoCare - Arquivo único de entrega](output/pdf/MotoCare-Arquivo-Unico-Entrega.pdf), edição atual de nove páginas:

1. Código-fonte e links clicáveis do repositório/histórico.
2. Arquitetura da solução Vue/PWA, API Laravel, MySQL, scheduler e Web Push.
3. DER atualizado, incluindo alertas.km_base e financeiro preservado/desativado.
4. Sprint Backlog de T1/T2, suporte e planejamento futuro da T3.
5. Atas retrospectivas dos ciclos: realizado, decisões, bloqueios e encaminhamentos.
6. Regras atuais, evidências e limites das validações.

Todas as nove páginas foram renderizadas e conferidas; extração de texto, quatro links e marcadores verificados. Esta edição substitui os PDFs anteriores de seis e oito páginas. As atas são consolidações dos registros de trabalho, sem inventar reuniões, participantes ou datas.

## Escopo e evidências

T1: alertas reais, Web Push com VAPID local, scheduler por minuto e ausência de repetição. T2: histórico de óleo, relação, pneus e freios; manutenção concluída eleva o odômetro somente se maior, em transação. Alertas usam 90% do intervalo entre referência e próximo limite. Conclusão/reprogramação de alerta permanece explícita.

Aceites do usuário em 22/10: notificações no computador e iPhone em segundo plano, inclusive scheduler real; app instalado, telas/formulários, offline/reconexão e modo noturno. A revisão posterior de negócio/segurança validou 45 testes backend/391 verificações e 28 frontend/build, smoke HTTP/MySQL/HTTPS, dependências sem avisos conhecidos e fontes/build sem segredos privados encontrados. Esses aceites manuais não foram repetidos durante a preparação do PDF/publicação. Pint global acusa somente formatação legada de quatro migrations. Evidências e limites em [REVISAO_LOGICA_SEGURANCA.md](REVISAO_LOGICA_SEGURANCA.md).

Financeiro/T3 e relatórios continuam adiados, com `FEATURE_FINANCEIRO=false`; código/schema preservados, menu/rotas de interface retirados e API financeira 404 por padrão. Esta entrega não reativa T3 nem inicia scheduler contínuo.

## Atualizar ou instalar

Seguir [README.md](README.md). Em clone existente, conferir alterações locais e atualizar a branch preservando o trabalho do colega. Restaurar dependências por `composer install` e `npm ci`, executar `php artisan migrate` (inclui km_base) e validar antes de operar. Nunca usar `migrate:fresh` no banco com dados. Preservar `.env`, APP_KEY e chaves VAPID existentes; não transferi-los por Git ou pacote. Em instalação nova, seguir a geração local de chaves no README.

O Git inclui fontes, exemplos de configuração, lockfiles, documentação e PDF; exclui dependências instaladas, builds, logs, caches, credenciais e chaves privadas. Os quatro legados remotos retirados são FcmService.php, config/firebase.php, FcmServiceTest.php e frontend/src/data/demo.js, substituídos pela integração real/Web Push.

Scheduler: `php artisan schedule:work` em terminal no backend, com contato VAPID válido. Safari/iPhone exige contato externo válido, também no processo do scheduler. Túnel temporário não é hospedagem de produção.

## Continuidade e cópia local

Antes de retomar, ler [CONTINUACAO.md](CONTINUACAO.md), [TASKS.md](TASKS.md), [SPEC.md](SPEC.md) e [MEMORY.md](MEMORY.md); depois [RETOMADA_T3.md](RETOMADA_T3.md). Criar `feature/t3-financeiro` a partir da base atual e executar o checklist futuro.

Cópia auxiliar local: `entrega/MotoCare-Retomada-T3-2026-10-22.zip`, manifesto `entrega/fontes-retomada-t3-2026-10-22.json` e `entrega/SHA256SUMS-Retomada-T3-2026-10-22.txt`. O diretório entrega é ignorado no Git porque duplica os fontes; no GitHub usar o clone ou Download ZIP. A cópia local inclui os mesmos fontes/documentos/PDF atuais. O pacote de 19/10 é histórico.

Conferir o checksum local com `Get-FileHash entrega/MotoCare-Retomada-T3-2026-10-22.zip -Algorithm SHA256`. A preparação documental não alterou aplicação, schema, chaves ou configuração privada e não executou novamente as suítes funcionais.
