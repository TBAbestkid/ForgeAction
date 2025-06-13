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

    public function loginUser($login, $senha)
    {
        $response = Http::withBasicAuth('roger', 'roger@123')
            ->post("{$this->baseUrl}/login", [
                'login' => $login,
                'senha' => $senha,
            ]);

        \Log::debug('HTTP status', ['status' => $response->status()]);
        \Log::debug('HTTP body', ['body' => $response->body()]);

        // Se for um JSON válido, decodifica
        $json = json_decode($response->body(), true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        // Se não for JSON, retorna o texto puro em um array
        return ['message' => $response->body()];
    }

    public function getPersonagem($token)
    {
        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/info_personagem/{$this->getChapId()}"); // ou outro endpoint

        if ($response->ok()) {
            return $response->json();
        }

        return null;
    }

    // Supondo que você tenha o chapId guardado
    // protected function getChapId()
    // {
    //     return session('chap_id'); // ou de onde você está salvando o chap_id
    // }

}
