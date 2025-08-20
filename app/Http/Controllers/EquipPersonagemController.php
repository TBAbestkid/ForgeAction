<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class EquipPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /equip-personagem/personagem/{personagemId}
     */
    public function show($personagemId)
    {
        return response()->json(
            $this->api->get("equip-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /equip-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("equip-personagem", $request->all())
        );
    }

    /**
     * PUT /equip-personagem/personagem/{personagemId}
     */
    public function update(Request $request, $personagemId)
    {
        return response()->json(
            $this->api->put("equip-personagem/personagem/{$personagemId}", $request->all())
        );
    }

    /**
     * DELETE /equip-personagem/personagem/{personagemId}
     */
    public function destroy($personagemId)
    {
        return response()->json(
            $this->api->delete("equip-personagem/personagem/{$personagemId}")
        );
    }
}
