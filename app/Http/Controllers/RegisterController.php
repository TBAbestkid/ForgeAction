<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\services\ApiService;
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
        $request->validate([
            'login' => 'required|string|min:4',
            'senha' => 'required|string|min:6',
            'email' => 'nullable|email'
        ]);

        $data = $request->only(['login','senha','email']);

        $response = $this->api->post('login/register', $data);

        if (isset($response['id'])) {
            session([
                'user_login' => $data['login'],
                'user_id' => $response['id'] ?? null,
                'user_role' => $response['role'] ?? 'PLAYER'
            ]);

            return redirect('/')->with('success', 'Cadastro realizado e login efetuado com sucesso!');
        }

        return redirect()->route('login')->with('success', $response['message'] ?? 'Usuário criado com sucesso!');
    }

}
