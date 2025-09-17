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
        return view('room.index');
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
