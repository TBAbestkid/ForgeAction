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
        $response = $this->api->put('login/update', [
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
            'email' => session('user_email'),
            'role' => $newRole,
        ];

        Log::info('UpdateRole Payload:', $payload);

        $response = $this->api->put('login/update', $payload);

        // log completo da resposta
        Log::info('UpdateRole Response:', ['response' => $response]);

        if (($response['status'] ?? '') === 'success') {
            session(['user_role' => $newRole]);
        }

        return response()->json($response);
    }

   public function updatePassword(Request $request)
    {
        $payload = [
            'email' => session('user_email'),
            'senha' => $request->senha,
        ];

        // Log do payload
        Log::info('UpdatePassword Payload:', $payload);

        // Chama a API
        $response = $this->api->put('login/forgot-password', $payload);

        // Log da resposta da API
        Log::info('UpdatePassword Response:', ['response' => $response]);

        if (($response['status'] ?? '') === 'success') {
            return ApiResponse::success($response['data'] ?? null, $response['message'] ?? 'Senha atualizada!');
        }

        return ApiResponse::error($response['message'] ?? 'Erro ao atualizar senha', $response['code'] ?? 400);
    }
}
