<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

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
        // Validação
        $request->validate([
            'nome' => 'required|string|max:50',
            'classe' => 'required|string',
            'raca' => 'required|string',
            'idade' => 'required|integer|min:1|max:999999',
            'genero' => 'required|string',
            // adicione validação dos atributos se quiser
        ]);

        try {
            $response = $this->api->post("api/personagem", $request->all());

            // Sucesso
            if (($response['status'] ?? '') === 'success') {
                return redirect('/')->with('success', 'Personagem criado com sucesso!');
            }

            // Falha
            return redirect()->back()->withInput()->with('error', $response['message'] ?? 'Erro ao criar personagem');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erro inesperado: ' . $e->getMessage());
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
