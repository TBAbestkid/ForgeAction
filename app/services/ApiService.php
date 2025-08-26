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

    public function get(string $path, array $query = [])
    {
        $response = $this->withAuth()->get("{$this->baseUrl}/{$path}", $query);
        return $response->json();
    }

    public function post(string $path, array $data = [])
    {
        $response = $this->withAuth()->post("{$this->baseUrl}/{$path}", $data);
        return $response->json();
    }

    public function put(string $path, array $data = [])
    {
        $response = $this->withAuth()->put("{$this->baseUrl}/{$path}", $data);
        return $response->json();
    }

    public function delete(string $path)
    {
        $response = $this->withAuth()->delete("{$this->baseUrl}/{$path}");
        return $response->json();
    }

}
