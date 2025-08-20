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
     * GET /bonus-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("bonus-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /bonus-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("bonus-personagem", $request->all())
        );
    }

    /**
     * PUT /bonus-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("bonus-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /bonus-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("bonus-personagem/personagem/{$personagemId}")
        );
    }
}
