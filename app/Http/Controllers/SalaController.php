<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado.');
        }

        $outrasSalas = (array) ($this->api->get("api/salas/jogador/{$userId}") ?? []);
        $minhasSalas = [];

        if ($userRole === 'MASTER') {
            $minhasSalas = (array) ($this->api->get("api/salas/mestre/{$userId}") ?? []);
        }

        // 🔹 Para cada sala, buscar número de personagens
        $todasAsSalas = [&$outrasSalas, &$minhasSalas];

        foreach ($todasAsSalas as &$lista) {
            foreach ($lista as &$sala) {
                if (!isset($sala['id'])) {
                    $sala['total_jogadores'] = 0;
                    continue;
                }

                try {
                    $personagens = $this->api->get("api/salas/personagens/listar/{$sala['id']}");
                    $sala['total_jogadores'] = is_array($personagens) ? count($personagens) : 0;
                } catch (\Exception $e) {
                    $sala['total_jogadores'] = 0; // fallback seguro
                }
            }
        }

        return view('room.index', compact('minhasSalas', 'outrasSalas', 'userRole'));
    }

    public function invite()
    {
        return response()->json(
            $this->api->get("api/usuario")
        );
    }

    public function createRoom()
    {
        $user = session('user_login');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado.');
        }

        $userRole = session('user_role');
        if ($userRole !== 'MASTER') {
            return redirect()->route('home')->with('error', 'Apenas mestres podem criar salas.');
        }

        return view('room.create');
    }

    public function sendInvite(Request $request)
    {
        $emails = $request->emails;
        $salaId = $request->salaId;
        $userId = session('user_id');

        // Busca a sala do dono
        $salasResponse = $this->api->get("api/salas/mestre/{$userId}");

        if (!$salasResponse || !is_array($salasResponse) || count($salasResponse) === 0) {
            return response()->json(['message' => 'Erro ao recuperar suas salas.'], 404);
        }

        // Procura a sala correta
        $sala = collect($salasResponse)->firstWhere('id', (int) $salaId);

        if (!$sala) {
            return response()->json(['message' => 'Sala não encontrada.'], 404);
        }

        if ($sala['mestre'] != $userId) {
            return response()->json(['message' => 'Você não tem permissão para convidar nesta sala.'], 403);
        }

        $dono = session('user_login');

        $tokens = [];

        foreach ($emails as $email) {
            $token = Str::random(64);

            Cache::put('invite_sala_' . $token, [
                'email' => $email,
                'salaId' => $salaId,
                'donoId' => $userId,
            ], now()->addMinutes(60));

            $tokens[$email] = route('api.invite.accept', ['token' => $token]);
        }

        // Link de convite
        $inviteLink = route('api.invite.accept', ['token' => $token]);

        // Monta o HTML do e-mail
        $html = view('emails.invite', [
            'sala' => $sala,
            'remetente' => $dono,
            'link' => $inviteLink
        ])->render();

        // Envia via API
        $response = $this->api->post("api/email/enviar", [
            'assunto' => "Convite para a sala {$sala['nome']}",
            'corpo' => $html,
            'destinatarios' => $emails,
        ]);

        if (($response['status'] ?? '') !== 'success') {
            return response()->json(['message' => $response['message'] ?? 'Erro ao enviar e-mail.'], 500);
        }

        return response()->json(['message' => 'Convite enviado com sucesso!']);
    }

    public function authenticated(Request $request, $user)
    {
        Log::info('authenticated - usuário logado', [
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'invite_token_session' => session('invite_token')
        ]);

        // Se houver token de convite na sessão
        if (session()->has('invite_token')) {
            $token = session()->pull('invite_token');
            Log::info('authenticated - redirecionando para convite', ['token' => $token]);

            return redirect()->route('api.invite.accept', ['token' => $token]);
        }

        Log::info('authenticated - redirecionando normalmente para salas');
        return redirect()->route('home');
    }

    // Se usuario possuir um personagem na sessão, ele pode só entrar direto na sala
    public function acceptInvite($token)
    {

        // Log::info('acceptInvite - iniciado', ['token' => $token]);

        $data = Cache::get('invite_sala_' . $token);
        // Log::info('acceptInvite - dados do cache', ['data' => $data]);

        if (!$data) {
            // Log::warning('acceptInvite - convite expirado ou inválido', ['token' => $token]);
            return redirect()->route('home')->withErrors(['token' => 'Convite expirado ou inválido.']);
        }

        $salaId = $data['salaId'] ?? null;
        $email = $data['email'] ?? null;
        // Log::info('acceptInvite - dados básicos', ['salaId' => $salaId, 'email' => $email]);

        if (!session('user_id')) {
            // Log::info('acceptInvite - usuário não logado, redirecionando para login', ['token' => $token]);
            session(['invite_token' => $token]);
            return redirect()->route('login');
        }

        $salaOwnerId = $data['donoId'] ?? null;
        // Log::info('acceptInvite - dono da sala', ['donoId' => $salaOwnerId]);

        if (!$salaOwnerId) {
            // Log::error('acceptInvite - dono da sala ausente');
            return redirect()->route('home')->withErrors(['sala' => 'Não foi possível identificar o dono da sala.']);
        }

        // 🔹 Busca todas as salas do dono
        $salasResponse = $this->api->get("api/salas/mestre/{$salaOwnerId}");
        // Log::info('acceptInvite - resposta da API salas', ['salasResponse' => $salasResponse]);

        $salas = isset($salasResponse['data']) ? $salasResponse['data'] : $salasResponse;
        $sala = collect($salas)->firstWhere('id', (int) $salaId);
        // Log::info('acceptInvite - sala filtrada', ['sala' => $sala]);

        if (!$sala) {
            // Log::error('acceptInvite - sala não encontrada', ['salaId' => $salaId]);
            return redirect()->route('home')->withErrors(['sala' => 'Sala não encontrada.']);
        }

        // 🔹 Busca personagens
        $personagensResponse = $this->api->get("api/personagem/usuario/" . session('user_id'));
        // Log::info('acceptInvite - resposta personagens', ['response' => $personagensResponse]);

        if (!isset($personagensResponse['status']) || $personagensResponse['status'] !== 'success') {
            // Log::warning('API retornou erro ao buscar personagens', ['response' => $personagensResponse]);
            $personagens = [];
        } else {
            $personagens = $personagensResponse['data'] ?? [];
        }

        // Log::info('acceptInvite - personagens carregados', [
        //     'user_id' => session('user_id'),
        //     'total' => count($personagens),
        //     'nomes' => array_column($personagens, 'nome')
        // ]);

        if (count($personagens) === 0) {
            return redirect()->route('personagem.create')
                ->with('info', 'Você precisa criar um personagem antes de entrar na sala.');
        }

        if (session()->has('selected_character')) {
            // {{ url('salas/personagens/adicionar/'.$sala['id']) }}
            // Eu então pego o id da sala eo id do personagem da sessão e faço a chamada para adicionar o personagem na sala
            $personagemIdSessao = session('selected_character.id');
            $this->api->post("api/salas/personagens/adicionar/{$salaId}/{$personagemIdSessao}");
            // Log::info('acceptInvite - personagem selecionado na sessão, entrando na sala', [
            //     'personagem' => session('selected_character')
            // ]);
            return redirect()->route('room.room', ['id' => $salaId]);
        }

        return view('room.selection', [
            'sala' => $sala,
            'personagens' => $personagens
        ]);
    }

    public function enterByCode(Request $request)
    {
        $code = $request->query('codigo');
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para entrar na sala.');
        }

        // Busca sala pelo código
        $salaResponse = $this->api->get("api/salas/codigo/{$code}");

        // Se retornou erro (404 ou outro)
        if (!$salaResponse || isset($salaResponse['error'])) {
            return redirect()->route('home')->withErrors([
                'codigo' => 'Código inválido ou sala não encontrada.'
            ]);
        }

        $sala = $salaResponse;
        // // dd($salaResponse);
        // // dd($sala);

        // === Busca personagens ===
        $personagensResponse = $this->api->get("api/personagem/usuario/{$userId}");
        $personagens = $personagensResponse['data'] ?? [];

        if (empty($personagens)) {
            return redirect()->route('personagem.create')
                ->with('info', 'Você precisa criar um personagem antes de entrar na sala.');
        }

        return view('room.selection', [
            'sala' => $sala,
            'personagens' => $personagens
        ]);
    }

    public function room(Request $request, $id)
    {
        $userId = session('user_id');
        $userRole = session('user_role');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado.');
        }

        // 🔹 Busca sala
        $sala = $this->api->get("api/salas/{$id}");
        if (!$sala) abort(404, 'Sala não encontrada.');

        // 🔹 Busca membros
        $personagens = collect($this->api->get("api/salas/personagens/listar/{$id}") ?? []);

        // Extraímos os usuários dos personagens
        $usuariosNaSala = $personagens->pluck('usuarioId')->unique()->values();

        // 🔹 O mestre SEMPRE precisa estar na lista
        if (!$usuariosNaSala->contains($sala['mestre'])) {
            $usuariosNaSala->push($sala['mestre']);
        }

        // 🔹 Buscar logins dos usuários (em um map id => login)
        $membros = $usuariosNaSala->mapWithKeys(function($uid) {
            $u = $this->api->get("api/usuario/{$uid}");
            return [$uid => $u['data']['login'] ?? 'Desconhecido'];
        });

        // 🔹 Verificar se o usuário acessante tem permissão
        $isDono = ($userRole === 'MASTER' && $sala['mestre'] == $userId);

        $salasDoJogador = collect($this->api->get("api/salas/jogador/{$userId}") ?? []);
        $isConvidado = $salasDoJogador->contains('id', $id);

        if (!$isDono && !$isConvidado) {
            return redirect()->route('home')->with('error', 'Você não tem permissão para acessar esta sala.');
        }
        // dd($sala, $membros, $isDono, $isConvidado);

        // 🔹 Retorna apenas o necessário
        return view('room.room', [
            'sala'   => $sala,
            'isDono' => $isDono,
            'membros' => $membros
        ]);
    }

    public function adicionarPersonagem(Request $request, $salaId)
    {
        $personagemId = $request->personagemId;
        $this->api->post("api/salas/personagens/adicionar/{$salaId}/{$personagemId}");
        return redirect()->route('room.room', ['id' => $salaId]);
    }

}
