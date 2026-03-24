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
        return $this->withAuth()->get("{$this->baseUrl}/{$path}", $query)->json();
    }

    public function post(string $path, array $data = [])
    {
        return $this->withAuth()->post("{$this->baseUrl}/{$path}", $data)->json();
    }

    public function put(string $path, array $data = [])
    {
        return $this->withAuth()->put("{$this->baseUrl}/{$path}", $data)->json();
    }

    public function delete(string $path)
    {
        return $this->withAuth()->delete("{$this->baseUrl}/{$path}")->json();
    }

    // Para enviar imagens com multipart/form-data
    public function uploadImage(string $path, string $fieldName, $contents, string $filename = null)
    {
        return $this->withAuth()
            ->attach($fieldName, $contents, $filename)
            ->post("{$this->baseUrl}/{$path}")
            ->json();
    }

}
