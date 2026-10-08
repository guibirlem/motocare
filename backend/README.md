# API MotoCare

Backend Laravel 12/PHP 8.2+, com MySQL local do XAMPP. A configuração completa para instalar em outro computador está no [README da raiz](../README.md).

## Comandos

Dentro de `backend`, com dependências instaladas, `.env` configurado e banco criado:

```powershell
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

Em outro terminal, para verificar alertas continuamente:

```powershell
php artisan schedule:work
```

Validação:

```powershell
php artisan test
```

## Estado

- A T1 contém models de moto/alerta, verificador aos 90% do limite, scheduler e envio FCM HTTP v1.
- [ALERTAS.md](ALERTAS.md) descreve configuração, estados, tentativas e limitações do push.
- A raiz da API responde JSON; não existem endpoints de manutenção, usuário ou autenticação prontos.
- A tabela `manutencoes` já está nas migrations. A implementação funcional do histórico é a T2.
- As telas Vue são servidas exclusivamente pelo projeto `frontend`; não usar Blade para implementar funcionalidades.

Leia o [handoff](../CONTINUACAO.md), a [SPEC](../SPEC.md) e o [backlog](../TASKS.md) antes de continuar.
