<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class DefesaPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /defesa-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("defesa-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /defesa-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("defesa-personagem", $request->all())
        );
    }

    /**
     * PUT /defesa-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("defesa-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /defesa-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("defesa-personagem/personagem/{$personagemId}")
        );
    }
}
