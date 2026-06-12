<?php

namespace App\Http\Controllers;

use App\Services\ApiService;

class DashboardController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        $request = request();
        $personagens = [];

        if ($request->session()->has('user_id')) {
            $userId = $request->session()->get('user_id');
            $response = $this->api->get("api/personagem/usuario/{$userId}");
            $rawPersonagens = $response['data'] ?? $response;

            if (is_array($rawPersonagens) && !isset($rawPersonagens['error'])) {
                $personagens = array_map(function ($p) {
                    return [
                        'id' => $p['id'] ?? 0,
                        'infoPersonagem' => $p['infoPersonagem'] ?? [
                            'nome' => $p['nome'] ?? 'Desconhecido',
                            'classe' => $p['classe'] ?? '-',
                            'raca' => $p['raca'] ?? '-',
                            'idade' => $p['idade'] ?? 0,
                            'genero' => $p['genero'] ?? '-',
                        ],
                        'atributos' => $p['atributos'] ?? [
                            'forca' => $p['forca'] ?? 0,
                            'agilidade' => $p['agilidade'] ?? 0,
                            'inteligencia' => $p['inteligencia'] ?? 0,
                            'sabedoria' => $p['sabedoria'] ?? 0,
                            'destreza' => $p['destreza'] ?? 0,
                            'vitalidade' => $p['vitalidade'] ?? 0,
                            'percepcao' => $p['percepcao'] ?? 0,
                            'carisma' => $p['carisma'] ?? 0,
                        ],
                        'bonus' => $p['bonus'] ?? ['bonupVida' => 0, 'bonupMana' => 0],
                        'status' => $p['status'] ?? ['vida' => $p['vida'] ?? 0, 'mana' => $p['mana'] ?? 0, 'iniciativa' => $p['iniciativa'] ?? 0],
                        'ataque' => $p['ataque'] ?? [
                            'ataqueMagico' => $p['ataqueMagico'] ?? 0,
                            'ataqueFisicoCorpo' => $p['ataqueFisicoCorpo'] ?? 0,
                            'ataqueFisicoDistancia' => $p['ataqueFisicoDistancia'] ?? 0,
                        ],
                        'danoBase' => $p['danoBase'] ?? ['fisico' => 0, 'magico' => 0],
                    ];
                }, $rawPersonagens);
            }
        }

        return view('index', compact('personagens'));
    }

    public function dice()
    {
        return view('dice');
    }

    public function about()
    {
        return view('about');
    }
}
