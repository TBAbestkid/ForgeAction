<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class BonusPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /api/bonus-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/bonus-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/bonus-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/bonus-personagem", $request->all())
        );
    }

    /**
     * PUT /api/bonus-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/bonus-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /api/bonus-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/bonus-personagem/personagem/{$personagemId}")
        );
    }
}
