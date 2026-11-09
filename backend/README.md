# API MotoCare

Complemento de23/10 requer `php artisan migrate`: novos campos opcionais e tabelas de postos/preços, sem recriar dados. Serviços por eixo/corrente, painel e chuva/lavagem complementam as rotas existentes; natureza preventiva/corretiva opcional. Ver [SPEC](../SPEC.md), [DER](../DER.md) e [validação](../VALIDACAO_COMPLEMENTOS.md). Preservar .env/APP_KEY/VAPID. A ausência de migration mencionada abaixo refere-se à integração inicial da T3, antes desta extensão.

Laravel 12/PHP 8.2+, MySQL/XAMPP e API JSON. Instalação completa no [README da raiz](../README.md).

Na pasta backend, com dependências e .env/APP_KEY configurados:

~~~powershell
php artisan migrate
php artisan push:configurar
php artisan serve --host=127.0.0.1 --port=8000
~~~

Em outro terminal: php artisan schedule:work. Verificação avulsa: php artisan alertas:verificar. Testes: php artisan test.

T1 usa alertas por km/data e Web Push com chaves VAPID locais, sem configurar Firebase. [ALERTAS.md](ALERTAS.md) explica ativação, estados e limites. T2 contém CRUD real de manutenção, com sessão/CSRF e isolamento por conta/moto. T3 integrada em 23/10/2026: despesas de combustível/reparo e relatórios financeiros; CSV/impressão ficam no frontend. FEATURE_FINANCEIRO=true por padrão; false desativa financeiro/relatórios, e GET autenticado /api/features informa a flag ao cliente. Nenhuma migration nova nesta retomada; [validação atual](../VALIDACAO_T3.md).

Apenas o frontend Vue serve telas; não criar Blade. Preservar .env/APP_KEY e chaves privadas em atualizações e mantê-los fora do versionamento.

Ler [CONTINUACAO](../CONTINUACAO.md), [SPEC](../SPEC.md), [TASKS](../TASKS.md) e [MEMORY](../MEMORY.md) antes de retomar.
