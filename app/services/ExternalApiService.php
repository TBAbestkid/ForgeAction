<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExternalApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://total-alice-rokaideveloper-77325d04.koyeb.app';
    }

    // Método para autenticação básica, pra chamar os endpoints que precisam de autenticação
    protected function withAuth()
    {
        return Http::withBasicAuth('roger', 'roger@123');
    }

    // Método GET
    public function registerUser($login, $senha)
    {
        $response = Http::post("{$this->baseUrl}/chave_personagem", [
            'chapLogin' => $login,
            'chapSenha' => $senha,
        ]);

        return $response->json();
    }

    public function getChapId($login)
    {
        $response = Http::get("{$this->baseUrl}/chave_personagem/check/{$login}");
        return $response->json();
    }

    // Metodo POST para login
    public function loginUser($login, $senha)
    {
        $response = $this->withAuth()->post("{$this->baseUrl}/login", [
                'login' => $login,
                'senha' => $senha,
            ]);

        // \Log::debug('HTTP status', ['status' => $response->status()]);
        // \Log::debug('HTTP body', ['body' => $response->body()]);

        // Se for um JSON válido, decodifica
        $json = json_decode($response->body(), true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        // Se não for JSON, retorna o texto puro em um array
        return ['message' => $response->body()];
    }

    // Método POST para criar personagem
    public function createPersonagemInfo($chapId, $nome, $racaId, $classeId, $idade, $identificacao)
    {
        $response = $this->withAuth()->post("{$this->baseUrl}/info_personagem", [
                'infpChapId' => $chapId,
                'infpNome' => $nome,
                'infpRacaId' => $racaId,
                'infpClasseId' => $classeId,
                'infpIdade' => $idade,
                'infpSexualidade' => $identificacao, // acho que tá meio trocado... deveria ser genero, não sexualidade
            ]);

        return $response->json();
    }

    // Método POST para criar atributos
    public function createAtributos($chapId, $atributos)
    {
        $data = array_merge(['atpChapId' => $chapId], $atributos);

        $response = Http::withBasicAuth('roger', 'roger@123')
            ->post("{$this->baseUrl}/atributo_personagem", $data);

        return $response->json();
    }

    // Método GET para pegar o personagem
    public function getPersonagem($token)
    {
        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/info_personagem/{$this->getChapId()}"); // ou outro endpoint

        if ($response->ok()) {
            return $response->json();
        }

        return null;
    }

    // pegar as raças
    public function getRacas()
    {
        $response = Http::get("{$this->baseUrl}/raca_relacao");
        return $response->json();
    }

    // pegar as classes
    public function getClasses()
    {
        $response = Http::get("{$this->baseUrl}/classe_relacao");
        return $response->json();
    }

    public function createBonus($chapId, $vida, $mana)
    {
        $response = Http::withBasicAuth('roger', 'roger@123')
            ->post("{$this->baseUrl}/bonus_personagem", [
                'bonupChapId' => $chapId,
                'bonupVida' => $vida,
                'bonupMana' => $mana,
            ]);

        return $response->json();
    }

    public function createStatus($chapId, $vidaTotal, $manaTotal, $iniciativa)
    {
        $response = Http::withBasicAuth('roger', 'roger@123')
            ->post("{$this->baseUrl}/status_personagem", [
                'stapChapId' => $chapId,
                'stapVida' => $vidaTotal,
                'stapMana' => $manaTotal,
                'stapIniciativa' => $iniciativa,
            ]);

        return $response->json();
    }

}
