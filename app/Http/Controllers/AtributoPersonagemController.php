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
     * GET /api/atributos-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/atributos-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/atributos-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/atributos-personagem", $request->all())
        );
    }

    /**
     * PUT /api/atributos-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/atributos-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /api/atributos-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/atributos-personagem/personagem/{$personagemId}")
        );
    }
}
