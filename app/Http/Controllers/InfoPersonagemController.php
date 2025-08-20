<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class InfoPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /info-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("info-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /info-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("info-personagem", $request->all())
        );
    }

    /**
     * PUT /info-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("info-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /info-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("info-personagem/personagem/{$personagemId}")
        );
    }
}
