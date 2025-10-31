<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
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
            $this->api->get("api/usuarios")
        );
    }

    public function sendInvite(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'salaId' => 'required|integer'
        ]);

        $email = $request->email;
        $salaId = $request->salaId;
        $userId = session('user_id');

        // Busca a sala do dono
        $salasResponse = $this->api->get("api/salas/usuario/{$userId}");
        if (!$salasResponse || ($salasResponse['status'] ?? '') !== 'success') {
            return response()->json(['message' => 'Erro ao recuperar suas salas.'], 404);
        }

        $sala = collect($salasResponse['data'] ?? [])->firstWhere('id', (int) $salaId);
        if (!$sala) {
            return response()->json(['message' => 'Sala não encontrada.'], 404);
        }

        $dono = session('user_login');

        // Gera token único e salva temporariamente no cache (expira em 60 min)
        $token = Str::random(64);
        Cache::put('invite_sala_' . $token, [
            'email' => $email,
            'salaId' => $salaId,
            'donoId' => $userId,
        ], now()->addMinutes(60));

        // Link de convite
        $inviteLink = route('room.invite.accept', ['token' => $token]);

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
            'destinatarios' => [$email],
        ]);

        if (($response['status'] ?? '') !== 'success') {
            return response()->json(['message' => $response['message'] ?? 'Erro ao enviar e-mail.'], 500);
        }

        return response()->json(['message' => 'Convite enviado com sucesso!']);
    }

    public function authenticated(Request $request, $user)
    {
        // Se houver token de convite na sessão
        if (session()->has('invite_token')) {
            $token = session()->pull('invite_token'); // pega e remove da sessão
            return redirect()->route('room.invite.accept', ['token' => $token]);
        }

        // Caso contrário, redireciona normalmente
        return redirect()->route('salas.index');
    }

    public function acceptInvite($token)
    {
        $data = Cache::get('invite_sala_' . $token);

        if (!$data) {
            return redirect()->route('salas.index')->withErrors(['token' => 'Convite expirado ou inválido.']);
        }

        $salaId = $data['salaId'];
        $email = $data['email'];

        if (!session('user_id')) {
            session(['invite_token' => $token]);
            return redirect()->route('login');
        }

        // Aqui você precisa do dono da sala
        $salaOwnerId = $data['donoId'] ?? null; // você deve salvar isso ao gerar o convite
        if (!$salaOwnerId) {
            return redirect()->route('salas.index')->withErrors(['sala' => 'Não foi possível identificar o dono da sala.']);
        }

        // Busca todas as salas do dono
        $salasResponse = $this->api->get("api/salas/usuario/{$salaOwnerId}");
        $salas = $salasResponse['data'] ?? [];

        // Filtra a sala específica
        // $sala = collect($salas)->firstWhere('id', (int) $salaId);

        if (!$salas) {
            return redirect()->route('salas.index')->withErrors(['sala' => 'Sala não encontrada.']);
        }

        // Busca personagens do usuário que aceitou
        $personagensResponse = $this->api->get("api/personagens/usuario/" . session('user_id'));
        $personagens = $personagensResponse['data'] ?? [];

        return view('room.selection', [
            'sala' => $salas,
            'personagens' => $personagens
        ]);
    }

    public function room(Request $request, $id)
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado.');
        }

        // 🔹 Busca sala
        $sala = $this->api->get("api/salas/{$id}");
        if (!$sala) abort(404, 'Sala não encontrada.');

        // 🔹 Busca membros
        $membros = collect($this->api->get("api/salas/personagens/listar/{$id}") ?? []);

        // 🔹 Verifica permissões
        $salasDoJogador = collect($this->api->get("api/salas/jogador/{$userId}") ?? []);
        $isDono = $sala['mestre'] == $userId;
        $isConvidado = $salasDoJogador->contains('id', $id);

        if (!$isDono && !$isConvidado) {
            return redirect()->route('salas.index')->with('error', 'Você não tem permissão para acessar esta sala.');
        }

        // 🔹 Busca raças e classes (só uma vez)
        $racas = collect($this->api->get("enums/racas")['data'] ?? []);
        $classes = collect($this->api->get("enums/classes")['data'] ?? []);

        // 🔹 Monta mapa de usuários (idealmente com endpoint em lote)
        $usuarios = $membros->pluck('usuarioId')->unique()->mapWithKeys(function ($idUsuario) {
            $res = $this->api->get("api/usuario/{$idUsuario}");
            return [$idUsuario => $res['data']['login'] ?? 'Desconhecido'];
        });

        // 🔹 Enriquecimento único
        $membros = $membros->map(function ($m) use ($racas, $classes, $usuarios) {
            $m['racaDescricao'] = $racas->firstWhere('descricao', $m['raca'])['descricao'] ?? $m['raca'];
            $m['classeDescricao'] = $classes->firstWhere('descricao', $m['classe'])['descricao'] ?? $m['classe'];
            $m['usuarioLogin'] = $usuarios[$m['usuarioId']] ?? 'Desconhecido';
            return $m;
        });
        // dd([]);
        // 🔹 Personagem do usuário logado
        $meuPersonagem = $membros->firstWhere('usuarioId', $userId);

        return view('room.room', [
            'sala'       => $sala,
            'isDono'     => $isDono,
            'membros'    => $membros,
            'personagem' => $meuPersonagem,
            // 'mestre'     => [
            //     'usuarioId'    => $sala['mestre'],
            //     'usuarioLogin' => $usuarios[$sala['usuarioId']] ?? 'Mestre',
            //     'racaDescricao' => 'Mestre',
            //     'classeDescricao' => 'Narrador',
            // ],
        ]);
    }
}
