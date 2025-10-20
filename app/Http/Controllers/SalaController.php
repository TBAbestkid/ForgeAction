<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class SalaController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado.');
        }

        $salas = $this->api->get("api/salas/usuario/{$userId}") ?? [];

        return view('room.index', compact('salas'));
    }

    public function invite()
    {
        return response()->json(
            $this->api->get("api/usuarios")
        );
    }

    public function room(Request $request, $id)
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado.');
        }

        // Pega todas as salas do usuário na API
        $salasResponse = $this->api->get("api/salas/usuario/{$userId}");

        if (!$salasResponse || !is_array($salasResponse)) {
            abort(404, 'Nenhuma sala encontrada para o usuário.');
        }

        // Procura a sala específica pelo ID
        $sala = collect($salasResponse)->firstWhere('id', (int) $id);

        if (!$sala) {
            abort(404, 'Sala não encontrada.');
        }

        // Pega os membros da sala via API
        $membros = $this->api->get("sala-personagem/sala/{$id}") ?? [];

        // Verifica se o usuário é dono
        $isDono = isset($sala['usuarioId']) && $sala['usuarioId'] == $userId;

        return view('room.room', [
            'sala'    => $sala,
            'isDono'  => $isDono,
            'membros' => $membros
        ]);
    }

    /**
     * POST /api/salas
     */
    public function store(Request $request)
    {
        return response()->json(
            $this->api->post("api/salas", $request->all())
        );
    }

    /**
     * PUT /api/salas/{id}
     */
    public function update(Request $request, $id)
    {
        return response()->json(
            $this->api->put("api/salas/{$id}", $request->all())
        );
    }

    /**
     * DELETE /api/salas/{id}
     */
    public function destroy($id)
    {
        return response()->json(
            $this->api->delete("api/salas/{$id}")
        );
    }

    /**
     * GET /api/salas/usuario/{usuarioId}
     */
    public function getByUsuario($usuarioId)
    {
        return response()->json(
            $this->api->get("api/salas/usuario/{$usuarioId}")
        );
    }
}
