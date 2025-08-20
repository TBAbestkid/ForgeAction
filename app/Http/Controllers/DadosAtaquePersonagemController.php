<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class DadosAtaquePersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /dados-ataque-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("dados-ataque-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /dados-ataque-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("dados-ataque-personagem", $request->all())
        );
    }

    /**
     * PUT /dados-ataque-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("dados-ataque-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /dados-ataque-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("dados-ataque-personagem/personagem/{$personagemId}")
        );
    }
}
