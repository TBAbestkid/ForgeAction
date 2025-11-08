<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /usuario
     * Retorna todos os usuarios
     */
    public function get() {
        return response()->json(
            $this->api->get("view/usuario")
        );
    }

    /**
     * GET /usuario/{id}
     * Retorna o usuario pelo ID
     */
    public function getById($usuarioId) {
        return response()->json(
            $this->api->get("view/usuario/{$usuarioId}")
        );
    }

    public function profile(Request $request)
    {
        if (!$request->session()->has('user_login')) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado.');
        }

        $user = [
            'id' => $request->session()->get('user_id'),
            'login' => $request->session()->get('user_login'),
            'role' => $request->session()->get('user_role'),
            'email' => $request->session()->get('user_email'),
        ];

        return view('profile', compact('user'));
    }

    public function updateEmail(Request $request)
    {
        $response = $this->api->put('api/login/update', [
            'login' => session('user_login'),
            'email' => $request->email,
            'role'  => $request->role,
        ]);

        if (($response['status'] ?? '') === 'success') {
            session(['user_email' => $request->email, 'user_role' => $request->role]);
            return ApiResponse::success($response['data'] ?? null, $response['message'] ?? 'Email atualizado!');
        }

        return ApiResponse::error($response['message'] ?? 'Falha ao atualizar email', $response['code'] ?? 400);
    }

    public function updateRole(Request $request)
    {
        $newRole = $request->input('role');

        $payload = [
            'login' => session('user_login'),
            'role' => $newRole,
        ];

        Log::info('UpdateRole Payload:', $payload);

        $response = $this->api->put('api/login/update', $payload);

        // log completo da resposta
        Log::info('UpdateRole Response:', ['response' => $response]);

        if (($response['status'] ?? '') === 'success') {
            session(['user_role' => $newRole]);
        }

        return response()->json($response);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'senhaAtual' => 'required|string',
            'senha' => 'required|string|min:6',
        ]);

        $userEmail = session('user_email');
        $userLogin = session('user_login');

        $loginResponse = $this->api->post('login', [
            'login' => $userLogin,
            'senha' => $request->senhaAtual,
        ]);

        if (($loginResponse['status'] ?? '') !== 'success') {
            return ApiResponse::error('Senha atual incorreta', 401);
        }

        $updateResponse = $this->api->put('api/login/forgot-password', [
            'email' => $userEmail,
            'senha' => $request->senha,
        ]);

        if (($updateResponse['status'] ?? '') === 'success') {
            return ApiResponse::success(null, $updateResponse['message'] ?? 'Senha atualizada com sucesso!');
        }

        return ApiResponse::error($updateResponse['message'] ?? 'Erro ao atualizar senha', $updateResponse['code'] ?? 400);
    }
}
