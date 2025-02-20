<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\Request;

class ExternalApiController extends Controller
{
    protected $externalApi;

    public function __construct(ExternalApiService $externalApi)
    {
        $this->externalApi = $externalApi;
    }

    public function index()
    {
        $data = $this->externalApi->getData();

        if (!$data) {
            return response()->json(['error' => 'Erro ao buscar dados da API externa'], 500);
        }

        return response()->json($data);
    }
}
