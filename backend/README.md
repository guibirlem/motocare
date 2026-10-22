# API MotoCare

Laravel 12/PHP 8.2+, MySQL/XAMPP e API JSON. Instalação completa no [README da raiz](../README.md).

Na pasta backend, com dependências e .env/APP_KEY configurados:

~~~powershell
php artisan migrate
php artisan push:configurar
php artisan serve --host=127.0.0.1 --port=8000
~~~

Em outro terminal: php artisan schedule:work. Verificação avulsa: php artisan alertas:verificar. Testes: php artisan test.

T1 usa alertas por km/data e Web Push com chaves VAPID locais, sem configurar Firebase. [ALERTAS.md](ALERTAS.md) explica ativação, estados e limites. T2 contém CRUD real de manutenção, com sessão/CSRF e isolamento por conta/moto. Conta/perfil/garagem dão suporte às duas tarefas. T3/financeiro e relatórios continuam adiados, com API desativada por padrão e código preservado.

Apenas o frontend Vue serve telas; não criar Blade. Preservar .env/APP_KEY e chaves privadas em atualizações e mantê-los fora do versionamento.

Ler [CONTINUACAO](../CONTINUACAO.md), [SPEC](../SPEC.md), [TASKS](../TASKS.md) e [MEMORY](../MEMORY.md) antes de retomar.
