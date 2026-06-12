<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        $userRole = session('user_role');

        $outrasSalas = $this->listFromApi($this->api->get("api/salas/jogador/{$userId}"));
        $minhasSalas = [];

        if ($userRole === 'MASTER') {
            $minhasSalas = $this->listFromApi($this->api->get("api/salas/mestre/{$userId}"));
        }

        foreach ([&$outrasSalas, &$minhasSalas] as &$lista) {
            foreach ($lista as &$sala) {
                if (!isset($sala['id'])) {
                    $sala['total_jogadores'] = 0;
                    continue;
                }

                try {
                    $personagens = $this->listFromApi($this->api->get("api/salas/personagens/listar/{$sala['id']}"));
                    $sala['total_jogadores'] = count($personagens);
                } catch (\Exception $e) {
                    $sala['total_jogadores'] = 0;
                }
            }
        }

        return view('room.index', compact('minhasSalas', 'outrasSalas', 'userRole'));
    }

    public function invite()
    {
        return response()->json(
            $this->api->get('api/usuario')
        );
    }

    public function createRoom()
    {
        if (session('user_role') !== 'MASTER') {
            return redirect()->route('home')->with('error', 'Apenas mestres podem criar salas.');
        }

        return view('room.create');
    }

    public function sendInvite(Request $request)
    {
        $validated = $request->validate([
            'salaId' => 'required|integer',
            'emails' => 'required|array|min:1',
            'emails.*' => 'required|email',
        ]);

        $userId = session('user_id');
        $salaId = (int) $validated['salaId'];

        $sala = $this->findSalaDoMestre($salaId, $userId);
        if (!$sala) {
            return response()->json(['message' => 'Voce nao tem permissao para convidar nesta sala.'], 403);
        }

        $tokens = [];
        foreach ($validated['emails'] as $email) {
            $token = Str::random(64);

            Cache::put('invite_sala_' . $token, [
                'email' => $email,
                'salaId' => $salaId,
                'donoId' => $userId,
            ], now()->addMinutes(60));

            $tokens[$email] = route('api.invite.accept', ['token' => $token]);
        }

        $inviteLink = reset($tokens);
        $html = view('emails.invite', [
            'sala' => $sala,
            'remetente' => session('user_login'),
            'link' => $inviteLink,
        ])->render();

        $response = $this->api->post('api/email/enviar', [
            'assunto' => 'Convite para a sala ' . ($sala['nome'] ?? ''),
            'corpo' => $html,
            'destinatarios' => $validated['emails'],
        ]);

        if (($response['status'] ?? '') !== 'success') {
            return response()->json(['message' => $response['message'] ?? 'Erro ao enviar e-mail.'], 500);
        }

        return response()->json(['message' => 'Convite enviado com sucesso!']);
    }

    public function authenticated(Request $request, $user)
    {
        Log::info('authenticated - usuario logado', [
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'invite_token_session' => session('invite_token'),
        ]);

        if (session()->has('invite_token')) {
            $token = session()->pull('invite_token');
            return redirect()->route('api.invite.accept', ['token' => $token]);
        }

        return redirect()->route('home');
    }

    public function acceptInvite($token)
    {
        $data = Cache::get('invite_sala_' . $token);

        if (!$data) {
            return redirect()->route('home')->withErrors(['token' => 'Convite expirado ou invalido.']);
        }

        if (!session('user_id')) {
            session(['invite_token' => $token]);
            return redirect()->route('login');
        }

        $salaId = (int) ($data['salaId'] ?? 0);
        $salaOwnerId = $data['donoId'] ?? null;

        if (!$salaOwnerId) {
            return redirect()->route('home')->withErrors(['sala' => 'Nao foi possivel identificar o dono da sala.']);
        }

        $sala = $this->findSalaDoMestre($salaId, $salaOwnerId);
        if (!$sala) {
            return redirect()->route('home')->withErrors(['sala' => 'Sala nao encontrada.']);
        }

        $personagens = $this->personagensDoUsuario(session('user_id'));
        if (count($personagens) === 0) {
            return redirect()->route('registerPerson', ['salaId' => $salaId])
                ->with('info', 'Voce precisa criar um personagem antes de entrar na sala.');
        }

        return view('room.selection', [
            'sala' => $sala,
            'personagens' => $personagens,
        ]);
    }

    public function enterByCode(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:100',
        ]);

        $userId = session('user_id');
        $salaResponse = $this->api->get('api/salas/codigo/' . rawurlencode($validated['codigo']));

        if (!$salaResponse || isset($salaResponse['error'])) {
            return redirect()->route('home')->withErrors([
                'codigo' => 'Codigo invalido ou sala nao encontrada.',
            ]);
        }

        $personagens = $this->personagensDoUsuario($userId);
        if (empty($personagens)) {
            return redirect()->route('registerPerson', ['salaId' => $salaResponse['id'] ?? null])
                ->with('info', 'Voce precisa criar um personagem antes de entrar na sala.');
        }

        return view('room.selection', [
            'sala' => $salaResponse,
            'personagens' => $personagens,
        ]);
    }

    public function room(Request $request, $id)
    {
        $userId = session('user_id');
        $userRole = session('user_role');

        $sala = $this->api->get("api/salas/{$id}");
        if (!$sala) {
            abort(404, 'Sala nao encontrada.');
        }

        $personagens = collect($this->listFromApi($this->api->get("api/salas/personagens/listar/{$id}")));
        $usuariosNaSala = $personagens->pluck('usuarioId')->unique()->values();

        if (!$usuariosNaSala->contains($sala['mestre'])) {
            $usuariosNaSala->push($sala['mestre']);
        }

        $membros = $usuariosNaSala->mapWithKeys(function ($uid) {
            $u = $this->api->get("api/usuario/{$uid}");
            return [$uid => $u['data']['login'] ?? 'Desconhecido'];
        });

        $isDono = ($userRole === 'MASTER' && (int) ($sala['mestre'] ?? 0) === (int) $userId);
        $isConvidado = collect($this->salasDoJogador($userId))->contains(function ($salaDoJogador) use ($id) {
            return (int) ($salaDoJogador['id'] ?? 0) === (int) $id;
        });

        if (!$isDono && !$isConvidado) {
            return redirect()->route('home')->with('error', 'Voce nao tem permissao para acessar esta sala.');
        }

        $personagemJogador = null;
        if (!$isDono) {
            $personagemObj = $personagens->firstWhere('usuarioId', $userId);

            if ($personagemObj) {
                $personagemJogador = $this->api->get('api/personagem/' . ($personagemObj['id'] ?? $personagemObj['personagemId']));
                $personagemJogador = $personagemJogador['data'] ?? $personagemJogador;
            }
        }

        return view('room.room', [
            'sala' => $sala,
            'isDono' => $isDono,
            'membros' => $membros,
            'personagemJogador' => $personagemJogador,
        ]);
    }

    public function adicionarPersonagem(Request $request, $salaId)
    {
        $validated = $request->validate([
            'personagemId' => 'required|integer',
        ]);

        if (!$this->usuarioPossuiPersonagem(session('user_id'), $validated['personagemId'])) {
            abort(403, 'Voce nao tem permissao para usar este personagem.');
        }

        $this->api->post("api/salas/personagens/adicionar/{$salaId}/{$validated['personagemId']}");

        return redirect()->route('room.room', ['id' => $salaId]);
    }

    private function findSalaDoMestre(int $salaId, $mestreId): ?array
    {
        $salas = $this->listFromApi($this->api->get("api/salas/mestre/{$mestreId}"));

        return collect($salas)->first(function ($sala) use ($salaId, $mestreId) {
            return (int) ($sala['id'] ?? 0) === $salaId
                && (int) ($sala['mestre'] ?? $mestreId) === (int) $mestreId;
        });
    }

    private function salasDoJogador($userId): array
    {
        return $this->listFromApi($this->api->get("api/salas/jogador/{$userId}"));
    }

    private function personagensDoUsuario($userId): array
    {
        return $this->listFromApi($this->api->get("api/personagem/usuario/{$userId}"));
    }

    private function usuarioPossuiPersonagem($userId, $personagemId): bool
    {
        return collect($this->personagensDoUsuario($userId))->contains(function ($personagem) use ($personagemId) {
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
