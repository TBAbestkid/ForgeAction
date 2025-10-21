<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Mail\ResetMail;
use App\Services\ApiService;
use App\Services\ApiMailer;

class AuthController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function profile()
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado.');
        }

        return view('profile');
    }

    public function forgotpassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Gera token e guarda temporariamente
        $token = Str::random(64);
        Cache::put('reset_password_' . $token, $request->email, now()->addMinutes(60));

        $resetLink = route('password.reset', ['token' => $token]);

        // Renderiza o Blade com os dados do reset
        $html = view('emails.reset', ['resetLink' => $resetLink])->render();
        dd($html)

        // Envia para a sua API via ApiService
        $response = $this->api->post("/api/email/enviar", [
            'assunto' => 'Redefinição de senha',
            'corpo' => $html,
            'destinatarios' => [$request->email],
        ]);

        // Opcional: você pode checar $response para ver se deu certo
        if (!$response['success'] ?? true) {
            return back()->withErrors(['email' => 'Falha ao enviar e-mail.']);
        }

        return back()->with('status', 'Link de redefinição enviado para seu e-mail!');
    }

    public function showResetForm($token)
    {
        $email = Cache::get('reset_password_' . $token);

        if (!$email) {
            return redirect()->route('forgot-password')->withErrors(['token' => 'Link expirado ou inválido.']);
        }

        return view('auth.reset-password', compact('token'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $email = Cache::get('reset_password_' . $request->token);

        if (!$email) {
            return back()->withErrors(['token' => 'Token inválido ou expirado.']);
        }

        // Aqui você chama sua API para atualizar a senha
        $response = $this->api->post("/login/reset-password", [
            'email'   => $email,
            'senha'   => $request->password,
        ]);

        if ($response['success'] ?? false) {
            Cache::forget('reset_password_' . $request->token);
            return redirect()->route('home')->with('success', 'Senha redefinida com sucesso!');
        }

        return back()->withErrors(['error' => 'Erro ao redefinir senha.']);

    }
}
