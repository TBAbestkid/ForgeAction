<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    protected $baseUrl;
    protected $user;
    protected $pass;

    public function __construct()
    {
        $this->baseUrl = config('services.api.base_url');
        $this->user = config('services.api.user');
        $this->pass = config('services.api.pass');
    }

    protected function withAuth()
    {
        return Http::withBasicAuth($this->user, $this->pass);
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

        $response = $this->withAuth()->post("{$this->baseUrl}/login", [
            'login' => $request->login,
            'senha' => $request->senha,
        ])->json();

        if (isset($response['message']) && str_contains(strtolower($response['message']), 'bem-sucedido')) {
            session(['user_login' => $request->login]);
            return redirect('/')->with('success', 'Login realizado com sucesso!');
        }

        return back()->with('error', $response['message'] ?? 'Falha no login.');
    }

    public function register(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'senha' => 'required|string|min:6',
        ]);

        $response = $this->withAuth()->post("{$this->baseUrl}/login/register", [
            'login' => $request->login,
            'senha' => $request->senha,
        ])->json();

        Log::info('Registro de usuário:', ['request' => $request->all(), 'response' => $response]);

        return response()->json([
            'message' => $response['message'] ?? 'Usuário criado com sucesso!',
            'user' => $request->only(['login', 'senha']),
        ]);
    }

    public function logout()
    {
        session()->forget('user_login');
        return redirect('/')->with('success', 'Logout realizado com sucesso!');
    }

    public function dashboard()
    {
        return view('dashboard');
    }
}
