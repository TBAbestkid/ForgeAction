<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class StatusPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /status-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/status-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/status-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/status-personagem", $request->all())
        );
    }

    /**
     * PUT /api/status-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/status-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /api/status-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/status-personagem/personagem/{$personagemId}")
        );
    }
}
