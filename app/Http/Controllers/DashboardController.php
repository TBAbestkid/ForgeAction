<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    // Home acessível para todos
    public function index()
    {
        $request = request();
        $char = session('selected_character');
        $personagens = [];

        // Se estiver logado, pega os personagens do usuário
        if ($request->session()->has('user_login')) {
            $userId = $request->session()->get('user_id');

            // Faz a requisição para a API
            $personagens = $this->api->get("api/personagem/usuario/{$userId}");

            // Decodifica JSON, se necessário
            if (is_string($personagens)) {
                $personagens = json_decode($personagens, true) ?: [];
            }

            // ⚠️ Se a API retornou erro, não tenta montar personagens
            if (isset($personagens['error']) || ($personagens['status'] ?? 200) === 404) {
                $personagens = [];
            }

            // Garante que é array e faz o map somente se houver dados válidos
            if (is_array($personagens) && !empty($personagens) && !isset($personagens['error'])) {
                $personagens = array_map(function($p) {
                    return [
                        'id' => $p['id'] ?? 0,
                        'infoPersonagem' => $p['infoPersonagem'] ?? [
                            'nome' => 'Desconhecido',
                            'classe' => '-',
                            'raca' => '-',
                            'idade' => 0,
                            'genero' => '-',
                        ],
                        'atributos' => $p['atributos'] ?? [
                            'forca'=>0, 'agilidade'=>0, 'inteligencia'=>0, 'sabedoria'=>0,
                            'destreza'=>0, 'vitalidade'=>0, 'percepcao'=>0, 'carisma'=>0
                        ],
                        'bonus' => $p['bonus'] ?? ['bonupVida'=>0, 'bonupMana'=>0],
                        'status' => $p['status'] ?? ['vida'=>0, 'mana'=>0, 'iniciativa'=>0],
                        'ataque' => $p['ataque'] ?? ['ataqueMagico'=>0, 'ataqueFisicoCorpo'=>0, 'ataqueFisicoDistancia'=>0],
                        'danoBase' => $p['danoBase'] ?? ['fisico'=>0, 'magico'=>0],
                    ];
                }, $personagens);
            } else {
                $personagens = []; // garante que o Blade e AJAX não quebrem
            }
        }

        return view('index', compact('char', 'personagens'));
    }

    public function dice()
    {
        return view('dice');
    }

    // Sobre
    public function about()
    {
        return view('about');
    }

    // Dashboard apenas logado
    public function dash(Request $request)
    {
        if (!$request->session()->has('user_login')) {
            return redirect()->route('home')->with('error', 'Você precisa estar logado.');
        }

        $userId = $request->session()->get('user_id');

        $response = $this->api->get("api/personagem/usuario/{$userId}");

        // Decodifica JSON, se necessário
        if (is_string($response)) {
            $response = json_decode($response, true) ?: [];
        }

        // Pega apenas o data[]
        $personagens = $response['data'] ?? [];

        // Busca classes e raças
        $classesRes = $this->api->get("enums/classes");
        $racasRes = $this->api->get("enums/racas");

        $classes = is_string($classesRes) ? json_decode($classesRes, true)['data'] ?? [] : $classesRes['data'] ?? [];
        $racas = is_string($racasRes) ? json_decode($racasRes, true)['data'] ?? [] : $racasRes['data'] ?? [];

        // Opcional: garantir que todos os campos existam para evitar undefined na view
        $personagens = array_map(function ($p) {
            return [
                'id' => $p['id'] ?? 0,
                'personagemId' => $p['personagemId'] ?? 0,
                'nome' => $p['nome'] ?? 'Desconhecido',
                'raca' => $p['raca'] ?? '-',
                'classe' => $p['classe'] ?? '-',
                'idade' => $p['idade'] ?? 0,
                'genero' => $p['genero'] ?? '-',
                'level' => $p['level'] ?? 1,
                'forca' => $p['forca'] ?? 0,
                'agilidade' => $p['agilidade'] ?? 0,
                'inteligencia' => $p['inteligencia'] ?? 0,
                'destreza' => $p['destreza'] ?? 0,
                'vitalidade' => $p['vitalidade'] ?? 0,
                'percepcao' => $p['percepcao'] ?? 0,
                'sabedoria' => $p['sabedoria'] ?? 0,
                'carisma' => $p['carisma'] ?? 0,
                'vida' => $p['vida'] ?? 0,
                'mana' => $p['mana'] ?? 0,
                'iniciativa' => $p['iniciativa'] ?? 0,
                'ataqueMagico' => $p['ataqueMagico'] ?? 0,
                'ataqueFisicoCorpo' => $p['ataqueFisicoCorpo'] ?? 0,
                'ataqueFisicoDistancia' => $p['ataqueFisicoDistancia'] ?? 0,
                'defesaPersonagem' => $p['defesaPersonagem'] ?? 0,
                'esquivaPersonagem' => $p['esquivaPersonagem'] ?? 0,
                'usuarioId' => $p['usuarioId'] ?? 0,
            ];
        }, $personagens);

        return view('dashboard', compact('personagens', 'classes', 'racas'));
    }

}
