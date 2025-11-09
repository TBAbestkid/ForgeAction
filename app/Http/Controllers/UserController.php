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
            $this->api->get("api/usuario")
        );
    }

    /**
     * GET /usuario/{id}
     * Retorna o usuario pelo ID
     */
    public function getById($usuarioId) {
        return response()->json(
            $this->api->get("api/usuario/{$usuarioId}")
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
        ]);

        if (($response['status'] ?? '') === 'success') {
            session(['user_email' => $request->email]);
            Log::info('📧 Email atualizado na sessão: ' . $request->email);
            return ApiResponse::success($response['data'] ?? null, $response['message'] ?? 'Email atualizado!');
        }

        return ApiResponse::error($response['message'] ?? 'Falha ao atualizar email', $response['code'] ?? 400);
    }

    public function updateRole(Request $request)
    {
        $response = $this->api->put('api/login/update', [
            'login' => session('user_login'),
            'role'  => $request->role,
        ]);

        if (($response['status'] ?? '') === 'success') {
            session(['user_role' => $request->role]);
            return ApiResponse::success($response['data'] ?? null, $response['message'] ?? 'Role atualizado com sucesso!');
        }

        return response()->json($response);
    }

    public function updatePassword(Request $request)
    {
        $loginResponse = $this->api->post('api/login', [
            'login' => session('user_login'),
            'senha' => $request->senhaAtual,
        ]);

        if ($loginResponse === null) {
            Log::error('❌ Falha na comunicação com a API ao tentar validar a senha atual.');
            return ApiResponse::error('Erro de comunicação com a API', 500);
        }

        if (($loginResponse['status'] ?? '') !== 'success') {
            Log::warning('⚠️ Senha incorreta ou erro na resposta da API.', [
                'status' => $loginResponse['status'] ?? '(sem status)',
                'body'   => $loginResponse,
            ]);
            return ApiResponse::error('Senha atual incorreta', 401);
        }

        $updateResponse = $this->api->put('api/login/forgot-password', [
            'login' => session('user_login'),
            'senha' => $request->senha,
        ]);

        if (($updateResponse['status'] ?? '') === 'success') {
            Log::info('🎉 Senha atualizada com sucesso!');
            return ApiResponse::success(null, $updateResponse['message'] ?? 'Senha atualizada com sucesso!');
        }

        return ApiResponse::error($updateResponse['message'] ?? 'Erro ao atualizar senha', $updateResponse['code'] ?? 400);
    }
}
