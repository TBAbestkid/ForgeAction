<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExternalApiService;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    // Exibe a primeira parte do registro
    public function parteUm(Request $request, ExternalApiService $apiService){
        // Verifica se o usuário já está logado
        if ($request->session()->has('user_login')) {
            return redirect('/')->with('error', 'Você já está logado!');
        }
        return view('register');
    }

    // Processa o registro do usuário
    public function primeiroCadastro(Request $request, ExternalApiService $apiService)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'chapLogin' => 'required|string',
            'chapSenha' => 'required|string|min:4',
        ]);

        // Tenta registrar na API
        $response = $apiService->registerUser($request->chapLogin, $request->chapSenha);
        Log::info('Resposta da API no registro:', ['response' => $response]);

        if (isset($response['message']) && str_contains(strtolower($response['message']), 'sucesso')) {
            // Recupera chap_id
            $checkResponse = $apiService->checkChapId($request->chapLogin);
            Log::info('Resposta do check chap_id:', ['response' => $checkResponse]);

            if (isset($checkResponse['chap_id'])) {
                // Agora salva no seu DB
                $user = \App\Models\User::create([
                    'name' => $request->chapLogin,
                    'email' => $request->email,
                    'password' => bcrypt($request->chapSenha),
                ]);

                session([
                    'user_login' => $request->chapLogin,
                    'chap_id' => $checkResponse['chap_id'],
                ]);

                return redirect()->route('registertwo')->with('success', 'Usuário criado com sucesso!');
            }

            return back()->with('error', 'Usuário criado na API, mas falhou ao recuperar o chap_id.');
        }

        Log::warning('Falha no registro de usuário', ['response' => $response]);
        return back()->with('error', $response['message'] ?? 'Falha ao criar usuário.');
    }


    // Exibe a segunda parte do registro
    public function parteDois(){
        return view('registertwo');
    }

    // Processa a segunda parte do registro do usuário
    public function segundoCadastro(Request $request, ExternalApiService $apiService)
    {
        // Validação dos dados do formulário
        $request->validate([
            'chapEmail' => 'required|email',
            'chapNome' => 'required|string',
        ]);

        // Tenta atualizar o usuário via API
        $response = $apiService->updateUser($request->chapEmail, $request->chapNome);

        // Log de depuração
        Log::info('Resposta da API na atualização do usuário:', ['response' => $response]);

        if (isset($response['message']) && str_contains(strtolower($response['message']), 'sucesso')) {
            return redirect()->route('registerthree')->with('success', 'Dados atualizados com sucesso!');
        }

        return back()->with('error', $response['message'] ?? 'Falha ao atualizar dados.');
    }

    // Exibe a terceira parte do registro
    public function parteTres(){
        return view('registerthree');
    }

    // Processa a terceira parte do registro do usuário
    public function terceiroCadastro(Request $request, ExternalApiService $apiService)
    {
        // Validação dos dados do formulário
        $request->validate([
            'chapPersonagem' => 'required|string',
            'chapClasse' => 'required|string',
        ]);

        // Tenta criar o personagem via API
        $response = $apiService->createCharacter($request->chapPersonagem, $request->chapClasse);

        // Log de depuração
        Log::info('Resposta da API na criação do personagem:', ['response' => $response]);

        if (isset($response['message']) && str_contains(strtolower($response['message']), 'sucesso')) {
            return redirect('/')->with('success', 'Personagem criado com sucesso!');
        }

        return back()->with('error', $response['message'] ?? 'Falha ao criar personagem.');
    }
}
