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
     * GET /dano-base-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("dano-base-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /dano-base-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("dano-base-personagem", $request->all())
        );
    }

    /**
     * PUT /dano-base-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("dano-base-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /dano-base-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("dano-base-personagem/personagem/{$personagemId}")
        );
    }
}
