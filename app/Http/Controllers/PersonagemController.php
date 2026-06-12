<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonagemController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function personagem(Request $request)
    {
        if ($request->has('salaId')) {
            $request->session()->put('return_sala_id', $request->query('salaId'));
        }

        return view('registerPerson');
    }

    public function show($personagemId)
    {
        if (!$this->userOwnsCharacter($personagemId)) {
            return ApiResponse::error('Voce nao tem permissao para acessar este personagem.', 403);
        }

        return response()->json(
            $this->api->get("api/personagem/{$personagemId}")
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:50',
            'classe' => 'required|string|max:50',
            'raca' => 'required|string|max:50',
            'idade' => 'required|integer|min:1|max:999999',
            'genero' => 'required|string|max:50',
            'forca' => 'required|integer|min:1|max:23',
            'agilidade' => 'required|integer|min:1|max:23',
            'inteligencia' => 'required|integer|min:1|max:23',
            'sabedoria' => 'required|integer|min:1|max:23',
            'destreza' => 'required|integer|min:1|max:23',
            'vitalidade' => 'required|integer|min:1|max:23',
            'percepcao' => 'required|integer|min:1|max:23',
            'carisma' => 'required|integer|min:1|max:23',
        ]);

        $totalAttrs = collect([
            'forca', 'agilidade', 'inteligencia', 'sabedoria',
            'destreza', 'vitalidade', 'percepcao', 'carisma',
        ])->sum(fn ($attr) => (int) $validated[$attr]);

        if ($totalAttrs !== 23) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Distribua exatamente 23 pontos entre os atributos.');
        }

        $payload = $validated;
        $payload['usuarioId'] = session('user_id');

        try {
            $response = $this->api->post('api/personagem', $payload);

            if (($response['status'] ?? '') === 'success') {
                if ($request->session()->has('return_sala_id')) {
                    $salaId = $request->session()->pull('return_sala_id');
                    $personagemId = $response['data']['id'] ?? null;

                    if ($personagemId) {
                        try {
                            $this->api->post("api/salas/personagens/adicionar/{$salaId}/{$personagemId}");

                            return redirect()->route('room.room', ['id' => $salaId])
                                ->with('success', 'Personagem criado com sucesso! Bem-vindo a sala!');
                        } catch (\Exception $e) {
                            Log::warning('Erro ao adicionar personagem a sala', ['erro' => $e->getMessage()]);

                            return redirect('/')->with('success', 'Personagem criado, mas houve um erro ao entrar na sala. Tente novamente.');
                        }
                    }
                }

                return redirect('/')->with('success', 'Personagem criado com sucesso!');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Erro ao criar personagem');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro inesperado: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $personagemId)
    {
        if (!$this->userOwnsCharacter($personagemId) && !$this->currentUserIsMaster()) {
            return ApiResponse::error('Voce nao tem permissao para editar este personagem.', 403);
        }

        $validated = $request->validate([
            'nome' => 'sometimes|string|max:50',
            'classe' => 'sometimes|string|max:50',
            'raca' => 'sometimes|string|max:50',
            'idade' => 'sometimes|integer|min:1|max:999999',
            'genero' => 'sometimes|string|max:50',
            'level' => 'sometimes|integer|min:1|max:999',
            'forca' => 'sometimes|integer|min:0|max:999',
            'agilidade' => 'sometimes|integer|min:0|max:999',
            'inteligencia' => 'sometimes|integer|min:0|max:999',
            'sabedoria' => 'sometimes|integer|min:0|max:999',
            'destreza' => 'sometimes|integer|min:0|max:999',
            'vitalidade' => 'sometimes|integer|min:0|max:999',
            'percepcao' => 'sometimes|integer|min:0|max:999',
            'carisma' => 'sometimes|integer|min:0|max:999',
        ]);

        return response()->json(
            $this->api->put("api/personagem/{$personagemId}", $validated)
        );
    }

    public function destroy($personagemId)
    {
        if (!$this->userOwnsCharacter($personagemId)) {
            return ApiResponse::error('Voce nao tem permissao para excluir este personagem.', 403);
        }

        return response()->json(
            $this->api->delete("api/personagem/{$personagemId}")
        );
    }

    public function showByUsuario($usuarioId)
    {
        if ((int) $usuarioId !== (int) session('user_id')) {
            return ApiResponse::error('Voce nao tem permissao para listar personagens deste usuario.', 403);
        }

        return response()->json(
            $this->api->get("api/personagem/usuario/{$usuarioId}")
        );
    }

    private function userOwnsCharacter($personagemId): bool
    {
        $personagens = $this->personagensDoUsuario();

        return collect($personagens)->contains(function ($personagem) use ($personagemId) {
            return (int) ($personagem['id'] ?? $personagem['personagemId'] ?? 0) === (int) $personagemId;
        });
    }

    private function personagensDoUsuario(): array
    {
        $response = $this->api->get('api/personagem/usuario/' . session('user_id'));

        if (isset($response['data']) && is_array($response['data'])) {
            return $response['data'];
        }

        return is_array($response) ? $response : [];
    }

    private function currentUserIsMaster(): bool
    {
        return session('user_role') === 'MASTER';
    }
}
