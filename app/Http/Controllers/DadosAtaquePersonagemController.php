<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\ApiService;

class DadosAtaquePersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /api/dados-ataque-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/dados-ataque-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/dados-ataque-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/dados-ataque-personagem", $request->all())
        );
    }

    /**
     * PUT /api/dados-ataque-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/dados-ataque-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /api/dados-ataque-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/dados-ataque-personagem/personagem/{$personagemId}")
        );
    }
}
