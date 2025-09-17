<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class SalaPersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /api/sala-personagem/sala/{salaId}
     */
    public function showBySala($salaId)
    {
        return response()->json(
            $this->api->get("api/sala-personagem/sala/{$salaId}")
        );
    }

    /**
     * GET /api/sala-personagem/personagem/{personagemId}
     */
    public function showByPersonagem($personagemId)
    {
        return response()->json(
            $this->api->get("api/sala-personagem/personagem/{$personagemId}")
        );
    }

    /**
     * POST /api/sala-personagem
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/sala-personagem", $request->all())
        );
    }

    /**
     * DELETE /api/sala-personagem/{id}
     */
    public function destroy($id)
    {
        return response()->json(
            $this->api->delete("api/sala-personagem/{$id}")
        );
    }

    /**
     * DELETE /api/sala-personagem/sala/{salaId}/personagem/{personagemId}
     */
    public function destroyBySalaAndPersonagem($salaId, $personagemId)
    {
        return response()->json(
            $this->api->delete("api/sala-personagem/sala/{$salaId}/personagem/{$personagemId}")
        );
    }
}
