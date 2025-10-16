<?php

namespace App\Http\Controllers;

use App\services\ApiService;
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
        $personagens = [];

        // Faz a requisição à API para pegar os personagens do usuário
        $personagens = $this->api->get("api/personagem/usuario/{$userId}");

        // Decodifica JSON, se vier como string
        if (is_string($personagens)) {
            $personagens = json_decode($personagens, true) ?: [];
        }

        // Se a API retornou erro, status 404 ou formato inesperado
        if (isset($personagens['error']) || ($personagens['status'] ?? 200) === 404) {
            $personagens = [];
        }

        // Garante que é array e só faz o map se tiver dados válidos
        if (is_array($personagens) && !empty($personagens) && !isset($personagens['error'])) {
            $personagens = array_map(function ($p) {
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
                        'forca' => 0, 'agilidade' => 0, 'inteligencia' => 0, 'sabedoria' => 0,
                        'destreza' => 0, 'vitalidade' => 0, 'percepcao' => 0, 'carisma' => 0
                    ],
                    'bonus' => $p['bonus'] ?? ['bonupVida' => 0, 'bonupMana' => 0],
                    'status' => $p['status'] ?? ['vida' => 0, 'mana' => 0, 'iniciativa' => 0],
                    'ataque' => $p['ataque'] ?? [
                        'ataqueMagico' => 0,
                        'ataqueFisicoCorpo' => 0,
                        'ataqueFisicoDistancia' => 0
                    ],
                    'danoBase' => $p['danoBase'] ?? ['fisico' => 0, 'magico' => 0],
                ];
            }, $personagens);
        } else {
            $personagens = []; // garante estrutura segura
        }

        return view('dashboard', compact('personagens'));
    }

}
