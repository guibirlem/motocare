<?php

use App\Http\Middleware\PrivateApiHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(PrivateApiHeaders::class);
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request, $exception) => $request->is('api/*') || $request->expectsJson());
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if ($request->is('api/*')) {
                $status = $response->getStatusCode();
                if ($status >= 400 && $status !== 422) {
                    $message = match ($status) {
                        401 => 'Entre na sua conta para continuar.',
                        404 => 'Recurso não encontrado.',
                        419 => 'Sua sessão expirou. Atualize a página e entre novamente.',
                        429 => 'Muitas tentativas. Aguarde antes de tentar novamente.',
                        default => $status >= 500 ? 'O serviço está indisponível. Tente novamente em instantes.' : 'Solicitação inválida.',
                    };
                    $response = response()->json(['message' => $message], $status, $response->headers->all());
                }
                $response->headers->set('Cache-Control', 'no-store, private');
                $response->headers->set('X-Content-Type-Options', 'nosniff');
            }

            return $response;
        });
    })->create();
