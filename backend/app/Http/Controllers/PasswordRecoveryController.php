<?php

namespace App\Http\Controllers;

use App\Rules\NewPassword;
use App\Services\PasswordRecovery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PasswordRecoveryController extends Controller
{
    public function send(Request $request, PasswordRecovery $recovery)
    {
        $data = $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);
        abort_unless($recovery->deliveryAvailable(), 503);
        $recovery->sendLink($data['email']);

        return response()->json(['message' => 'Se houver uma conta com este e-mail, você receberá um link para redefinir sua senha. Confira também a pasta de spam.'], 202);
    }

    public function reset(Request $request, PasswordRecovery $recovery)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'token' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
            'password' => ['required', 'string', 'min:8', new NewPassword, 'confirmed'],
        ], [
            'token.required' => 'Abra o link recebido por e-mail ou solicite um novo.',
            'token.regex' => 'Este link é inválido ou expirou. Solicite um novo.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ]);
        $user = $recovery->reset($data);
        if (! $user) {
            throw ValidationException::withMessages(['token' => 'Este link é inválido ou expirou. Solicite um novo.']);
        }
        if (Auth::id() === $user->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Senha atualizada. Entre com sua nova senha. Reative as notificações nos dispositivos que você utiliza.']);
    }
}
