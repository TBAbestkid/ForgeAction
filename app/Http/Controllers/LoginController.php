<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExternalApiService;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    // Exibe a view de login
    public function login(){
        return view('login');
    }

    // Exibe a view de registro
    public function cadastro(){
        return view('registertwo');
    }
    
    // Métodos para processar o POST dos formulários
    public function postLogin(Request $request, ExternalApiService $apiService)
    {
        $request->validate([
            'login' => 'required|string',
            'senha' => 'required|string',
        ]);

        $response = $apiService->loginUser($request->login, $request->senha);

        \Log::debug('Resposta da API no login:', ['response' => $response]);

        if (isset($response['message']) && str_contains(strtolower($response['message']), 'bem-sucedido')) {
            session(['user_login' => $request->login]);
            return redirect('/')->with('success', 'Login realizado com sucesso!');
        }

        return back()->with('error', $response['message'] ?? 'Falha no login.');
    }

    public function post(Request $request)
    {
        $apiService = new ExternalApiService();

        // Validação simples para evitar erro
        $request->validate([
            'chapLogin' => 'required|string',
            'chapSenha' => 'required|string|min:6',
        ]);
        
        // tenta c
        $response = $apiService->registerUser($request->chapLogin, $request->chapSenha);
        
        Log::info('JSon: ', $request);
        return response()->json([
            'message' => 'Usuário criado com sucesso!',
            'user' => $request->all()
        ]);
    }
    public function dashboard()
    {
        return view('dashboard');
    }

}
