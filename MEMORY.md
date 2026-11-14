# MEMORY.md - Diário de Bordo da IA

## README reescrito - 14/11/2026

- README agora explica a ideia e o problema que o MotoCare resolve, funcionalidades já implementadas, arquitetura e stack.
- Passos locais para Windows/XAMPP cobrem Composer, migrations, geração inicial de chaves, execução Vue/Laravel, scheduler, notificações e testes; SMTP é indicado como necessário para envio real de recuperação.
- Atualização documental em resposta ao pedido do usuário. Sem mudanças de aplicação, configuração privada, dados ou schema; suítes não foram reexecutadas.

## Plano de testes do MVP - 13/11/2026

- PDF atualizado com 11 casos, 5 unitarios, 4 integracao, 2 aceitacao, rastreabilidade e roteiros. Todos passaram.
- Suite backend completa: 89 testes/898 assercoes, PHP 8.2.12 / SQLite em memoria. TC09/TC10 navegados e verificados no servidor temporario com SQLite descartavel; TC09 confirmou isolamento ao acessar com segunda conta.
- Cobertura inclui Money, placa, tanque/reserva, categorias de servico, alertas 90%, manutencao API, relatorio financeiro, autorizacao/CSRF, cadastro/historico e CSV protegido.
- Testes inicialmente publicados em `test/plano-testes-mvp` e incorporados na `master` em 14/11/2026.
- Documentos nao listam nomes do squad; plano usa Squad MotoCare.

## Estado atual - 09/11/2026

- MVP consolidado na branch `master` do repositório `https://github.com/guibirlem/motocare`.
- Commit de consolidação publicado: `54907f3` (`feat: consolidar MVP na master`).
- O conteúdo inclui T1/T2/T3, landing, lembretes, recuperação de senha, histórico, complementos de motocicletas, migrations e lockfiles.
- `.env`, chaves privadas, VAPID privado, dependências, caches, logs e builds permanecem ignorados e fora do repositório.
- Build/testes locais não foram reexecutados nesta sessão porque Node/npm não está disponível no PATH; o PHP do XAMPP está instalado.
- O clone Git local usado para publicação está limpo na branch `master` após o push.
