<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\ApiService;
use App\Models\User;

class RegisterController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function register(Request $request){
        if ($request->session()->has('user_login')) {
            return redirect()->route('home')->with('error', 'Você já está logado!');
        }

        return view('register');
    }

    public function registerPost(Request $request)
    {
        // Validação dos campos
        $request->validate([
            'login' => 'required|string|min:4',
            'senha' => 'required|string|min:6',
            'email' => 'required|email'
        ]);

        $data = $request->only(['login', 'senha', 'email']);

        try {
            $response = $this->api->post('api/login/register', $data);

            // Verifica se o retorno é válido
            if (!is_array($response)) {
                $message = 'Erro inesperado na comunicação com o servidor.';
                return $this->handleRegisterResponse($request, false, $message);
            }

            // Checa sucesso da API (status_code + status + data)
            $isSuccess = (
                (($response['status_code'] ?? 200) === 200) &&
                (($response['status'] ?? '') === 'success') &&
                isset($response['data'])
            );

            if ($isSuccess) {
                $userData = $response['data'];

                // Limpa sessões anteriores
                $request->session()->forget(['user_login', 'user_id', 'user_role', 'user_email']);

                // Cria sessão do novo usuário
                session([
                    'user_login' => $userData['login'] ?? $data['login'],
                    'user_id'    => $userData['id'] ?? null,
                    'user_email' => $userData['email'] ?? $data['email'],
                    'user_role'  => $userData['role'] ?? 'PLAYER'
                ]);

                $message = $response['message'] ?? 'Cadastro realizado e login efetuado com sucesso!';
                return $this->handleRegisterResponse($request, true, $message, '/');
            }

            // Se a API não retornou sucesso, mostra a mensagem dela
            $message = $response['message'] ?? 'Falha ao criar usuário.';
            return $this->handleRegisterResponse($request, false, $message, 'login');
        } catch (\Exception $e) {
            $message = 'Erro ao criar usuário: ' . $e->getMessage();
            return $this->handleRegisterResponse($request, false, $message, 'login', 500);
        }
    }

    /**
     * Helper para unificar respostas de sucesso/erro do registerPost.
     */
    private function handleRegisterResponse(Request $request, bool $success, string $message, string $redirect = null, int $statusCode = 200)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success'  => $success,
                'message'  => $message,
                'redirect' => $success && $redirect ? url($redirect) : null
            ], $statusCode);
        }

        if ($success) {
            return redirect($redirect ?? '/')->with('success', $message);
        }

        return redirect()->route('login')->with('error', $message);
    }

}
