<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Timebox;
use Throwable;

class PasswordRecovery
{
    public function deliveryAvailable(): bool
    {
        $mailer = config('mail.default');
        $transport = config('mail.mailers.'.$mailer.'.transport');

        // O mailer log exporia o link secreto nos logs. Array só é permitido nos testes.
        return $this->frontendUrl() !== null && (
            (app()->environment('testing') && $transport === 'array') ||
            ($transport === 'smtp' && filled(config('mail.mailers.'.$mailer.'.host')) &&
                filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL) &&
                ! str_ends_with(strtolower(config('mail.from.address')), '@example.com'))
        );
    }

    private function frontendUrl(): ?string
    {
        $url = rtrim((string) config('password_recovery.frontend_url'), '/');
        $parts = parse_url($url);
        if (! $parts || ! filter_var($url, FILTER_VALIDATE_URL) ||
            isset($parts['user']) || isset($parts['pass']) ||
            isset($parts['query']) || isset($parts['fragment']) || ! empty($parts['path'])) {
            return null;
        }
        $scheme = $parts['scheme'] ?? '';
        $host = strtolower($parts['host'] ?? '');
        if ($scheme !== 'https' && ! ($scheme === 'http' && in_array($host, ['127.0.0.1', 'localhost', '[::1]'], true))) {
            return null;
        }

        return $url;
    }

    private function lockedUser(string $email): ?User
    {
        // Compatível também com endereços antigos cadastrados com letras maiúsculas.
        return User::whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])->lockForUpdate()->first();
    }

    public function sendLink(string $email): void
    {
        (new Timebox)->call(function () use ($email) {
            DB::transaction(function () use ($email) {
                $user = $this->lockedUser($email);
                if (! $user) {
                    return;
                }
                Password::broker()->sendResetLink(['email' => $user->email], function (User $user, string $token) {
                    $url = $this->frontendUrl().'/redefinir-senha#'.http_build_query([
                        'token' => $token, 'email' => $user->getEmailForPasswordReset(),
                    ], '', '&', PHP_QUERY_RFC3986);
                    $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
                    $text = "Olá!\n\nRecebemos uma solicitação para redefinir sua senha no MotoCare.\n\n".
                        "Abra o link abaixo para escolher uma nova senha:\n$url\n\n".
                        "O link pode ser usado uma única vez e expira em $minutes minutos.\n".
                        "Se não foi você, ignore este e-mail. Sua senha continua a mesma.\n\nEquipe MotoCare";
                    try {
                        Mail::mailer()->raw($text, fn ($message) => $message->to($user->email)->subject('MotoCare: redefina sua senha'));
                    } catch (Throwable) {
                        Password::broker()->deleteToken($user);
                        // Não registrar a exceção do transporte: pode conter destinatário, link ou credenciais.
                        Log::warning('Falha no envio de recuperação de senha. Confira o serviço SMTP.');
                    }
                });
            });
        }, 300000);
    }

    public function reset(#[\SensitiveParameter] array $credentials): ?User
    {
        return (new Timebox)->call(function () use ($credentials) {
            return DB::transaction(function () use ($credentials) {
                // Mesmo bloqueio usado no envio: validação, senha e consumo do token são atômicos.
                $user = $this->lockedUser($credentials['email']);
                if (! $user) {
                    return null;
                }
                $credentials['email'] = $user->email;
                $status = Password::broker()->reset($credentials, function (User $user, string $password) {
                    $user->forceFill([
                        'password' => $password,
                        'push_subscription' => null,
                        'push_endpoint_hash' => null,
                    ])->save();
                    if (config('session.driver') === 'database') {
                        DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                            ->where('user_id', $user->id)->delete();
                    }
                    event(new PasswordReset($user));
                });

                return $status === Password::PASSWORD_RESET ? $user : null;
            }, 3);
        }, 300000);
    }
}
