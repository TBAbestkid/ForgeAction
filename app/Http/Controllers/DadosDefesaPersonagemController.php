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
     * GET /dados-defesa-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("dados-defesa-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /dados-defesa-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("dados-defesa-personagem", $request->all())
        );
    }

    /**
     * PUT /dados-defesa-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("dados-defesa-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /dados-defesa-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("dados-defesa-personagem/personagem/{$personagemId}")
        );
    }
}
