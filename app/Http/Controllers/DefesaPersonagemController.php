<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\ApiService;

class DefesaPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /api/defesa-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/defesa-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/defesa-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/defesa-personagem", $request->all())
        );
    }

    /**
     * PUT /api/defesa-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/defesa-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /api/defesa-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/defesa-personagem/personagem/{$personagemId}")
        );
    }
}
