<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\ApiService;

class EquipPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /api/equip-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("api/equip-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/equip-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/equip-personagem", $request->all())
        );
    }

    /**
     * PUT /api/equip-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("api/equip-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /api/equip-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("api/equip-personagem/personagem/{$personagemId}")
        );
    }
}
