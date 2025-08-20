<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class AtributoPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /atributos-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("atributos-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /atributos-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("atributos-personagem", $request->all())
        );
    }

    /**
     * PUT /atributos-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("atributos-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /atributos-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("atributos-personagem/personagem/{$personagemId}")
        );
    }
}
