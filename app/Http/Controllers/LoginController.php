<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\services\ApiService;

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

        $response = $this->api->post('login', [
            'login' => $request->login,
            'senha' => $request->senha,
        ]);

        if (isset($response['id'])) { // se retornou id, login foi bem-sucedido
            session([
                'user_login' => $request->login,
                'user_id' => $response['id'],
                'user_role' => $response['role']
            ]);

            return redirect('/')->with('success', 'Login realizado com sucesso!');
        }

        return back()->with('error', $response['message'] ?? 'Falha no login.');
    }

    public function logout()
    {
        session()->forget('user_login');
        return redirect('/')->with('success', 'Logout realizado com sucesso!');
    }

    public function update(Request $request)
    {
        // pega usuário logado da sessão
        $userId = session('user_id');
        $currentRole = session('user_role');

        // alterna o role
        $newRole = $currentRole === 'MASTER' ? 'PLAYER' : 'MASTER';

        // chama a API passando o novo role
        $response = $this->api->put("/login/update", [
            'id' => $userId,
            'role' => $newRole,
        ]);

        // atualiza sessão também
        session(['user_role' => $newRole]);

        return response()->json($response);
    }
}
