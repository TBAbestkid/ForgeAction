<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class SalaApiController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    // ---------- Salas ----------

    /**
     * GET /api/salas
     * Retorna todas as salas
     */
    public function get() {
        return response()->json(
            $this->api->get("api/salas")
        );
    }

    /**
     * POST /api/salas
     * Cria uma nova sala
     */
    public function store(Request $request) {
        return response()->json(
            $this->api->post("api/salas", $request->all())
        );
    }

    /**
     * PUT /api/salas/{id}
     * Atualiza uma sala existente
     */
    public function update(Request $request, $id) {
        return response()->json(
            $this->api->put("api/salas/{$id}", $request->all())
        );
    }

    /**
     * DELETE /api/salas/{id}
     * Deleta uma sala
     */
    public function destroy($id) {
        return response()->json(
            $this->api->delete("api/salas/{$id}")
        );
    }

    /**
     * GET /api/salas/jogador/{usuarioId}
     * Retorna as salas em que o jogador participa
     */
    public function getByJogador($usuarioId) {
        return response()->json(
            $this->api->get("api/salas/jogador/{$usuarioId}")
        );
    }

    /**
     * GET /api/salas/mestre/{usuarioId}
     * Retorna as salas em que o usuário é mestre
     */
    public function getByMestre($usuarioId) {
        return response()->json(
            $this->api->get("api/salas/mestre/{$usuarioId}")
        );
    }

    /**
     * GET /api/salas/buscar/{nome}
     * Busca salas pelo nome
     */
    public function getByNome($nome) {
        return response()->json(
            $this->api->get("api/salas/buscar/{$nome}")
        );
    }

    /**
     * GET /api/salas/{id}
     * Retorna os detalhes de uma sala específica
     */
    public function getById($id) {
        return response()->json(
            $this->api->get("api/salas/{$id}")
        );
    }

    // ---------- Personagens em Salas ----------

    /**
     * GET /api/salas/personagens/listar/{salaId}
     * Lista todos os personagens de uma sala
     */
    public function listarPersonagens($salaId) {
        return response()->json(
            $this->api->get("api/salas/personagens/listar/{$salaId}")
        );
    }

    /**
     * POST /api/salas/personagens/adicionar/{salaId}/{personagemId}
     * Adiciona um personagem a uma sala
     */
    public function adicionarPersonagem($salaId, $personagemId) {
        return response()->json(
            $this->api->post("api/salas/personagens/adicionar/{$salaId}/{$personagemId}")
        );
    }

    /**
     * DELETE /api/salas/personagens/remover/{salaId}/{personagemId}
     * Remove um personagem de uma sala
     */
    public function removerPersonagem($salaId, $personagemId) {
        return response()->json(
            $this->api->delete("api/salas/personagens/remover/{$salaId}/{$personagemId}")
        );
    }

}
