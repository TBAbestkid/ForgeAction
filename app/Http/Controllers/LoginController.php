<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\ApiService;
use App\Helpers\ApiResponse;

class LoginController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function login(Request $request){
        // se usuario estiver logado, pq el vai fazer login?
        if ($request->session()->has('user_login')) {
            return redirect()->route('home')->with('error', 'Você já está logado!');
        }

        return view('login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'senha' => 'required|string',
        ]);

        $response = $this->api->post('api/login', [
            'login' => $request->login,
            'senha' => $request->senha,
        ]);

        // Garante que o retorno é um array
        if (!is_array($response)) {
            return back()->with('error', 'Erro inesperado na comunicação com o servidor.');
        }

        // Garante que o login foi bem-sucedido
        $isSuccess = (
            (($response['status_code'] ?? 200) === 200) &&
            (($response['status'] ?? '') === 'success') &&
            isset($response['data'])
        );

        if ($isSuccess) {
            $data = $response['data'];

            // Limpa sessões antigas antes de registrar novo login
            $request->session()->forget(['user_login', 'user_id', 'user_role', 'user_email']);

            // Registra sessão do usuário autenticado
            session([
                'user_login' => $data['login'] ?? $request->login,
                'user_id'    => $data['id'] ?? null,
                'user_role'  => $data['role'] ?? 'PLAYER',
                'user_email' => $data['email'] ?? null,
            ]);

            return redirect('/')->with('success', $response['message'] ?? 'Login realizado com sucesso!');
        }

        return back()->with('error', $response['message'] ?? 'Falha no login.');
    }

    public function logout()
    {
        session()->forget('user_login');
        session()->forget('selected_character');
        return redirect('/')->with('success', 'Logout realizado com sucesso!');
    }

}
