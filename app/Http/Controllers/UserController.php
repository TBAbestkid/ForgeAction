<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function get()
    {
        return response()->json(
            $this->api->get('api/usuario')
        );
    }

    public function getById($usuarioId)
    {
        if ((int) $usuarioId !== (int) session('user_id')) {
            return ApiResponse::error('Voce nao tem permissao para acessar este usuario.', 403);
        }

        return response()->json(
            $this->api->get("api/usuario/{$usuarioId}")
        );
    }

    public function profile(Request $request)
    {
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
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $response = $this->api->put('api/login/update', [
            'login' => session('user_login'),
            'email' => $validated['email'],
        ]);

        if (($response['status'] ?? '') === 'success') {
            session(['user_email' => $validated['email']]);
            Log::info('Email atualizado na sessao: ' . $validated['email']);

            return ApiResponse::success($response['data'] ?? null, $response['message'] ?? 'Email atualizado!');
        }

        return ApiResponse::error($response['message'] ?? 'Falha ao atualizar email', $response['code'] ?? 400);
    }

    public function updateRole(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:PLAYER,MASTER'],
        ]);

        $response = $this->api->put('api/login/update', [
            'login' => session('user_login'),
            'role' => $validated['role'],
        ]);

        if (($response['status'] ?? '') === 'success') {
            session(['user_role' => $validated['role']]);

            return ApiResponse::success($response['data'] ?? null, $response['message'] ?? 'Papel atualizado com sucesso!');
        }

        return ApiResponse::error($response['message'] ?? 'Falha ao atualizar papel', $response['code'] ?? 400);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'senhaAtual' => 'required|string',
            'senha' => 'required|string|min:6',
        ]);

        $loginResponse = $this->api->post('api/login', [
            'login' => session('user_login'),
            'senha' => $validated['senhaAtual'],
        ]);

        if ($loginResponse === null) {
            Log::error('Falha na comunicacao com a API ao validar a senha atual.');
            return ApiResponse::error('Erro de comunicacao com a API', 500);
        }

        if (($loginResponse['status'] ?? '') !== 'success') {
            Log::warning('Senha incorreta ou erro na resposta da API.', [
                'status' => $loginResponse['status'] ?? '(sem status)',
            ]);

            return ApiResponse::error('Senha atual incorreta', 401);
        }

        $updateResponse = $this->api->put('api/login/forgot-password', [
            'login' => session('user_login'),
            'senha' => $validated['senha'],
        ]);

        if (($updateResponse['status'] ?? '') === 'success') {
            return ApiResponse::success(null, $updateResponse['message'] ?? 'Senha atualizada com sucesso!');
        }

        return ApiResponse::error($updateResponse['message'] ?? 'Erro ao atualizar senha', $updateResponse['code'] ?? 400);
    }
}
