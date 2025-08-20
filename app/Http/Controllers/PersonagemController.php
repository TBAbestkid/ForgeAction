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

    /**
     * GET /personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("personagem/{$personagemId}")
        );
    }

    /**
     * POST /personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("personagem", $request->all())
        );
    }

    /**
     * DELETE /personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("personagem/{$personagemId}")
        );
    }

    /**
     * GET /personagem/usuario/{usuarioId}
     */
    public function showByUsuario($usuarioId)
    {
        return response()->json(
            $this->api->get("personagem/usuario/{$usuarioId}")
        );
    }
}
