<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    protected $baseUrl;
    protected $user;
    protected $pass;

    public function __construct()
    {
        $this->baseUrl = config('services.api.base_url', 'https://narrow-christan-rokaideveloper-806169ef.koyeb.app');
        $this->user = config('services.api.user', 'admin');
        $this->pass = config('services.api.pass', 'admin');
    }

    protected function withAuth()
    {
        return Http::withBasicAuth($this->user, $this->pass);
    }

    /**
     * Trata centralizadamente os retornos da API e seus possíveis erros.
     */
    protected function handleResponse($response)
    {
        $status = $response->status();

        // 401: Serviço indisponível ou autenticação falhou
        if ($status === 401) {
            throw new \Exception('SERVICO_INDISPONIVEL_401');
        }

        // 403: Acesso proibido
        if ($status === 403) {
            throw new \Exception('ACESSO_NEGADO_403');
        }

        // 404: Rota ou recurso não encontrado
        if ($status === 404) {
            throw new \Exception('RECURSO_NAO_ENCONTRADO_404');
        }

        // Erros do servidor (500+)
        if ($status >= 500) {
            throw new \Exception('FALHA_SERVIDOR_API_500');
        }

        // Falhas genéricas (timeout, erro de validação, etc)
        if ($response->failed()) {
            throw new \Exception('FALHA_API_GENERICA');
        }

        // Retorna em formato JSON, como antes
        return $response->json();
    }

    public function get(string $path, array $query = [])
    {
        $response = $this->withAuth()->get("{$this->baseUrl}/{$path}", $query);
        return $this->handleResponse($response);
    }

    public function post(string $path, array $data = [])
    {
        $response = $this->withAuth()->post("{$this->baseUrl}/{$path}", $data);
        return $this->handleResponse($response);
    }

    public function put(string $path, array $data = [])
    {
        $response = $this->withAuth()->put("{$this->baseUrl}/{$path}", $data);
        return $this->handleResponse($response);
    }

    public function delete(string $path)
    {
        $response = $this->withAuth()->delete("{$this->baseUrl}/{$path}");
        return $this->handleResponse($response);
    }

}
