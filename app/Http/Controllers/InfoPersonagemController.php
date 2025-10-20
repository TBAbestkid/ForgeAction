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
     * GET /api/info-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/info-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/info-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/info-personagem", $request->all())
        );
    }

    /**
     * PUT /api/info-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/info-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /api/info-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/info-personagem/personagem/{$personagemId}")
        );
    }
}
