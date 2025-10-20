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
        // Validação
        $request->validate([
            'login' => 'required|string|min:4',
            'senha' => 'required|string|min:6',
            'email' => 'required|email'
        ]);

        $data = $request->only(['login', 'senha', 'email']);

        try {
            $response = $this->api->post('login/register', $data);

            if (isset($response['id'])) {
                session([
                    'user_login' => $data['login'],
                    'user_id' => $response['id'],
                    'user_email' => $data['email'],
                    'user_role' => $response['role'] ?? 'PLAYER'
                ]);

                // Se for AJAX, retorna JSON
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Cadastro realizado e login efetuado com sucesso!',
                        'redirect' => route('/')
                    ]);
                }

                return redirect('/')->with('success', 'Cadastro realizado e login efetuado com sucesso!');
            }

            // Caso não tenha ID retornado
            $msg = $response['message'] ?? 'Usuário criado com sucesso!';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg
                ]);
            }

            return redirect()->route('login')->with('success', $msg);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar usuário: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('login')->with('error', 'Erro ao criar usuário: ' . $e->getMessage());
        }
    }

}
