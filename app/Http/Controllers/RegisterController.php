<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class RegisterController extends Controller
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

    public function register(Request $request){
        if ($request->session()->has('user_login')) {
            return redirect()->route('home')->with('error', 'Você já está logado!');
        }

        return view('register');
    }

    // Cadastro via AJAX
    public function registerAjax(Request $request)
    {
        $request->validate([
            'login' => 'required|string|unique:users,username',
            'senha' => 'required|string|min:6',
            'email' => 'required|email|unique:users,email'
        ]);

        // Chamada direta à API externa
        $response = $this->withAuth()->post("{$this->baseUrl}/login/register", [
            'login' => $request->login,
            'senha' => $request->senha,
            'email' => $request->email
        ])->json();

        if (isset($response['http_status']) && $response['http_status'] === 201) {
            // Cria usuário localmente
            $user = User::create([
                'name' => $request->login,
                'username' => $request->login,
                'email' => $request->email,
                'password' => bcrypt($request->senha),
            ]);

            session([
                'user_login' => $request->login,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso!',
                'user' => $user
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Erro ao criar usuário na API'
        ], 400);
    }
}
