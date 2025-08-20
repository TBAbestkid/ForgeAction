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
     * GET /ataque_personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("ataque_personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /ataque_personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("ataque_personagem", $request->all())
        );
    }

    /**
     * PUT /ataque_personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("ataque_personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /ataque_personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("ataque_personagem/personagem/{$personagemId}")
        );
    }
}
