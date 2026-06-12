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
        $baseUrl = config('services.api.base_url');
        $this->user = config('services.api.user');
        $this->pass = config('services.api.pass');

        if (!$baseUrl || !$this->user || !$this->pass) {
            throw new \RuntimeException('External API configuration is incomplete. Check EXTERNAL_API_URL, EXTERNAL_API_USER and EXTERNAL_API_PASS.');
        }

        $baseUrl = rtrim($baseUrl, '/');
        $this->baseUrl = preg_replace('#/api$#', '', $baseUrl);
    }

    protected function withAuth()
    {
        return Http::withBasicAuth($this->user, $this->pass);
    }

    protected function url(string $path): string
    {
        return "{$this->baseUrl}/" . ltrim($path, '/');
    }

    public function get(string $path, array $query = [])
    {
        return $this->withAuth()->get($this->url($path), $query)->json();
    }

    public function post(string $path, array $data = [])
    {
        return $this->withAuth()->post($this->url($path), $data)->json();
    }

    public function put(string $path, array $data = [])
    {
        return $this->withAuth()->put($this->url($path), $data)->json();
    }

    public function delete(string $path)
    {
        return $this->withAuth()->delete($this->url($path))->json();
    }

    // Para enviar imagens com multipart/form-data
    public function uploadImage(string $path, string $fieldName, $contents, string $filename = null)
    {
        return $this->withAuth()
            ->attach($fieldName, $contents, $filename)
            ->post($this->url($path))
            ->json();
    }

}
