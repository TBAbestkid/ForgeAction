<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class AtaquePersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /ataque-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/ataque-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /ataque-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/ataque-personagem", $request->all())
        );
    }

    /**
     * PUT /ataque-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/ataque-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /ataque-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/ataque-personagem/personagem/{$personagemId}")
        );
    }
}
