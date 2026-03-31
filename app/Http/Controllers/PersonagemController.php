<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Facades\Log;

class PersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function personagem(Request $request)
    {
        // Se usuário não tiver sessão, redireciona
        if (!$request->session()->has('user_login')) {
            return redirect()->route('home')
                ->with('error', 'Você deve estar logado para criar um personagem!');
        }

        // Se veio de uma sala, armazena na sessão para redirecionar depois
        if ($request->has('salaId')) {
            $request->session()->put('return_sala_id', $request->query('salaId'));
        }

        return view('registerPerson');
    }

    /**
     * GET /api/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/personagem
     */
    public function store(Request $request)
    {
        try {
            // Envia exatamente tudo que veio do form
            $payload = $request->all();
            $payload['usuarioId'] = session('user_id');

            $response = $this->api->post("api/personagem", $payload);

            if (($response['status'] ?? '') === 'success') {
                // Se tem salaId na sessão, adiciona à sala e entra direto
                if ($request->session()->has('return_sala_id')) {
                    $salaId = $request->session()->pull('return_sala_id');
                    $personagemId = $response['data']['id'] ?? null;

                    // Se conseguiu o ID do personagem, adiciona à sala
                    if ($personagemId) {
                        try {
                            $this->api->post("api/salas/personagens/adicionar/{$salaId}/{$personagemId}");
                            // Salva na sessão como personagem selecionado
                            $request->session()->put('selected_character.id', $personagemId);

                            // Redireciona direto para a sala
                            return redirect()->route('room.room', ['id' => $salaId])
                                ->with('success', 'Personagem criado com sucesso! Bem-vindo à sala!');
                        } catch (\Exception $e) {
                            Log::warning('Erro ao adicionar personagem à sala', ['erro' => $e->getMessage()]);
                            // Se falhar, retorna para home com mensagem
                            return redirect('/')->with('success', 'Personagem criado, mas houve um erro ao entrar na sala. Tente novamente.');
                        }
                    }

                    return redirect('/')->with('success', 'Personagem criado com sucesso!');
                }

                return redirect('/')->with('success', 'Personagem criado com sucesso!');
            }

            return redirect()->back()
                            ->withInput()
                            ->with('error', $response['message'] ?? 'Erro ao criar personagem');

        } catch (\Exception $e) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Erro inesperado: ' . $e->getMessage());
        }
    }


    /**
     * DELETE /api/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/personagem/{$personagemId}")
        );
    }

    /**
     * GET /api/personagem/usuario/{usuarioId}
     */
    public function showByUsuario($usuarioId)
    {
        return response()->json(
            $this->api->get("api/personagem/usuario/{$usuarioId}")
        );
    }
}
