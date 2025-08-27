<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\ApiService;

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
        return response()->json(
            $this->api->post("api/personagem", $request->all())
        );
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
