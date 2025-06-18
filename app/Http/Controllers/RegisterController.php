<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\ExternalApiService;
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
    // MEU DEUS FUNCIONA
    public function primeiroCadastro(Request $request, ExternalApiService $apiService)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'chapLogin' => 'required|string|unique:users,username',
            'chapSenha' => 'required|string|min:4',
        ]);
        // Log de depuração
        // Log::info('Iniciando primeiroCadastro', [
        //     'email' => $request->email,
        //     'chapLogin' => $request->chapLogin
        // ]);

        // Chama API para registrar usuário
        $response = $apiService->registerUser($request->chapLogin, $request->chapSenha);
        // Log::info('Resposta registerUser API:', ['response' => $response]);

        if (isset($response['http_status']) && $response['http_status'] === 201) {
            Log::info('Registro na API confirmado via status 201');

            // Tenta pegar o chap_id com até 3 tentativas
            $checkResponse = null;
            for ($i = 1; $i <= 3; $i++) {
                $checkResponse = $apiService->getChapId($request->chapLogin);
                // Log::info('Tentativa getChapId', [
                //     'attempt' => $i,
                //     'response' => $checkResponse
                // ]);

                if (
                    isset($checkResponse['http_status']) &&
                    $checkResponse['http_status'] === 200 &&
                    isset($checkResponse['body']['chapId'])
                ) {
                    break;
                }

                sleep(1); // espera antes da próxima tentativa
            }

            if (
                isset($checkResponse['http_status']) &&
                $checkResponse['http_status'] === 200 &&
                isset($checkResponse['body']['chapId'])
            ) {
                // Salva no DB local
                $user = \App\Models\User::create([
                    'name' => $request->chapLogin,
                    'username' => $request->chapLogin, 
                    'email' => $request->email,
                    'password' => bcrypt($request->chapSenha),
                ]);

                session([
                    'user_login' => $request->chapLogin,
                    'chap_id' => $checkResponse['body']['chapId']
                ]);

                Log::info('Usuário e sessão salvos', [
                    'user_id' => $user->id,
                    'chap_id' => session('chap_id')
                ]);

                return redirect()->route('registertwo')->with('success', 'Usuário criado e logado com sucesso!');
            }

            Log::warning('Usuário criado na API mas falhou ao recuperar chap_id');
            return back()->with('error', 'Usuário criado na API, mas falhou ao recuperar o chap_id. Tente novamente.');
        }

        Log::warning('Falha no registro de usuário', ['response' => $response]);
        return back()->with('error', 'Falha ao criar usuário na API.');
    }


    // Exibe a segunda parte do registro
    public function parteDois(){
        // Verifica se o usuário já está logado
        if (!session()->has('user_login')) {
            return redirect('/')->with('error', 'Você precisa estar logado para continuar!');
        }
        // Verifica se o chap_id está na sessão
        if (!session()->has('chap_id')) {
            return redirect('/')->with('error', 'Chap ID não encontrado. Por favor, tente novamente.');
        }
        // Se tudo estiver ok, exibe a view
        // Log::info('Exibindo parte dois do registro', [
        //     'user_login' => session('user_login'),
        //     'chap_id' => session('chap_id')
        // ]);

        // Retorna a view da segunda parte do registro
        // Mas antes, precisamos enviar o getClasses e getRaces para a view
        $apiService = new ExternalApiService();
        $classes = $apiService->getClasses();
        $races = $apiService->getRaces();
        Log::info('Classes e raças obtidas', [
            'classes' => $classes,
            'racas' => $races
        ]);

        // Passa as classes e raças para a view
        return view('registertwo', ['classes' => $classes,'racas' => $races]);
    }

    // Processa a segunda parte do registro do usuário
    public function segundoCadastro(Request $request, ExternalApiService $apiService)
    {
        return redirect('/')->with('error', 'Não é pra acessar essa bomba atomica.');
    }

    // Exibe a terceira parte do registro
    public function parteTres(){
        return view('registerthree');
    }

    // Processa a terceira parte do registro do usuário
    public function terceiroCadastro(Request $request, ExternalApiService $apiService)
    {
        return redirect('/')->with('error', 'Não é pra acessar essa bomba atomica.');
    }
}
