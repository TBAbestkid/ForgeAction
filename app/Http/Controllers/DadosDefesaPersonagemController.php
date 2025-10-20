<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class DadosDefesaPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /api/dados-defesa-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/dados-defesa-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/dados-defesa-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/dados-defesa-personagem", $request->all())
        );
    }

    /**
     * PUT /api/dados-defesa-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/dados-defesa-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /api/dados-defesa-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/dados-defesa-personagem/personagem/{$personagemId}")
        );
    }
}
