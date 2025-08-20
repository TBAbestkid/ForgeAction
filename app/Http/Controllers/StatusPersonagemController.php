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
            $this->api->get("status-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /status-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("status-personagem", $request->all())
        );
    }

    /**
     * PUT /status-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("status-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /status-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("status-personagem/personagem/{$personagemId}")
        );
    }
}
