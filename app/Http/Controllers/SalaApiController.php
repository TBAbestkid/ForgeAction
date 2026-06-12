<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalaApiController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function get()
    {
        return response()->json($this->api->get('api/salas'));
    }

    public function store(Request $request)
    {
        if (session('user_role') !== 'MASTER') {
            return redirect()->route('home')->with('error', 'Apenas mestres podem criar salas.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:1000',
            'background' => 'nullable|image|max:2048',
        ]);

        $response = $this->api->post('api/salas', [
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'mestreId' => session('user_id'),
        ]);

        if (!isset($response['id'])) {
            Log::error('Erro ao criar sala: resposta da API nao contem ID', ['response' => $response]);

            return redirect()->back()->with([
                'error' => 'Erro ao criar sala. Tente novamente. Erro: ' . ($response['message'] ?? 'Resposta inesperada da API'),
            ]);
        }

        $salaId = $response['id'];

        if ($request->hasFile('background')) {
            $file = $request->file('background');

            $this->api->uploadImage(
                "api/salas/{$salaId}/upload",
                'imagem',
                fopen($file->getPathname(), 'r'),
                $file->getClientOriginalName()
            );
        }

        return redirect()->route('home')->with('success', 'Sala criada!');
    }

    public function update(Request $request, $id)
    {
        if (!$this->mestrePossuiSala($id)) {
            return ApiResponse::error('Voce nao tem permissao para editar esta sala.', 403);
        }

        $validated = $request->validate([
            'nome' => 'sometimes|string|max:100',
            'descricao' => 'sometimes|nullable|string|max:1000',
        ]);

        return response()->json($this->api->put("api/salas/{$id}", $validated));
    }

    public function destroy($id)
    {
        if (!$this->mestrePossuiSala($id)) {
            return ApiResponse::error('Voce nao tem permissao para excluir esta sala.', 403);
        }

        return response()->json($this->api->delete("api/salas/{$id}"));
    }

    public function getByJogador($usuarioId)
    {
        if ((int) $usuarioId !== (int) session('user_id')) {
            return ApiResponse::error('Voce nao tem permissao para listar salas deste jogador.', 403);
        }

        return response()->json($this->api->get("api/salas/jogador/{$usuarioId}"));
    }

    public function getByMestre($usuarioId)
    {
        if ((int) $usuarioId !== (int) session('user_id') || session('user_role') !== 'MASTER') {
            return ApiResponse::error('Voce nao tem permissao para listar salas deste mestre.', 403);
        }

        return response()->json($this->api->get("api/salas/mestre/{$usuarioId}"));
    }

    public function getByNome($nome)
    {
        return response()->json($this->api->get('api/salas/buscar/' . rawurlencode($nome)));
    }

    public function getById($id)
    {
        if (!$this->usuarioPodeAcessarSala($id)) {
            return ApiResponse::error('Voce nao tem permissao para acessar esta sala.', 403);
        }

        return response()->json($this->api->get("api/salas/{$id}"));
    }

    public function listarPersonagens($salaId)
    {
        if (!$this->usuarioPodeAcessarSala($salaId)) {
            return ApiResponse::error('Voce nao tem permissao para listar personagens desta sala.', 403);
        }

        return response()->json($this->api->get("api/salas/personagens/listar/{$salaId}"));
    }

    public function adicionarPersonagem($salaId, $personagemId)
    {
        if (!$this->usuarioPossuiPersonagem($personagemId) && !$this->mestrePossuiSala($salaId)) {
            return ApiResponse::error('Voce nao tem permissao para adicionar este personagem.', 403);
        }

        return response()->json($this->api->post("api/salas/personagens/adicionar/{$salaId}/{$personagemId}"));
    }

    public function removerPersonagem($salaId, $personagemId)
    {
        $podeRemover = $this->mestrePossuiSala($salaId) || $this->usuarioPossuiPersonagem($personagemId);

        if (!$podeRemover) {
            return ApiResponse::error('Voce nao tem permissao para remover este personagem.', 403);
        }

        return response()->json($this->api->delete("api/salas/personagens/remover/{$salaId}/{$personagemId}"));
    }

    public function getByCode($code)
    {
        return response()->json($this->api->get('api/salas/codigo/' . rawurlencode($code)));
    }

    public function adicionarPersonagemByCode(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:100',
            'personagemId' => 'required|integer',
        ]);

        if (!$this->usuarioPossuiPersonagem($validated['personagemId'])) {
            return ApiResponse::error('Voce nao tem permissao para usar este personagem.', 403);
        }

        return response()->json(
            $this->api->post("api/salas/codigo/{$validated['codigo']}/personagens/{$validated['personagemId']}")
        );
    }

    private function mestrePossuiSala($salaId): bool
    {
        if (session('user_role') !== 'MASTER') {
            return false;
        }

        $salas = $this->listFromApi($this->api->get('api/salas/mestre/' . session('user_id')));

        return collect($salas)->contains(function ($sala) use ($salaId) {
            return (int) ($sala['id'] ?? 0) === (int) $salaId
                && (int) ($sala['mestre'] ?? session('user_id')) === (int) session('user_id');
        });
    }

    private function usuarioPodeAcessarSala($salaId): bool
    {
        if ($this->mestrePossuiSala($salaId)) {
            return true;
        }

        $salas = $this->listFromApi($this->api->get('api/salas/jogador/' . session('user_id')));

        return collect($salas)->contains(fn ($sala) => (int) ($sala['id'] ?? 0) === (int) $salaId);
    }

    private function usuarioPossuiPersonagem($personagemId): bool
    {
        $personagens = $this->listFromApi($this->api->get('api/personagem/usuario/' . session('user_id')));

        return collect($personagens)->contains(function ($personagem) use ($personagemId) {
            return (int) ($personagem['id'] ?? $personagem['personagemId'] ?? 0) === (int) $personagemId;
        });
    }

    private function listFromApi($response): array
    {
        if (isset($response['data']) && is_array($response['data'])) {
            return $response['data'];
        }

        return is_array($response) ? $response : [];
    }
}
