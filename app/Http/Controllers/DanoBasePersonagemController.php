<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class DanoBasePersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /api/dano-base-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/dano-base-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/dano-base-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/dano-base-personagem", $request->all())
        );
    }

    /**
     * PUT /api/dano-base-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/dano-base-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /api/dano-base-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/dano-base-personagem/personagem/{$personagemId}")
        );
    }
}
