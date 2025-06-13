<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\Request;

class ExternalApiController extends Controller
{
    protected $apiService;

    public function __construct(ExternalApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    // Teste GET
    public function getCharacters()
    {
        $response = $this->apiService->get('/status_personagem/{$id}'); 
        return response()->json($response);
    }

    // Teste POST
    public function createCharacter(Request $request)
    {
        $data = $request->all();
        $response = $this->apiService->post('/status_personagem', $data);
        return response()->json($response);
    }

    // Teste PUT
    public function updateCharacter(Request $request, $id)
    {
        $data = $request->all();
        $response = $this->apiService->put("/status_personagem/{$id}", $data);
        return response()->json($response);
    }

    // Teste DELETE
    public function deleteCharacter($id)
    {
        $response = $this->apiService->delete("/status_personagem/{$id}");
        return response()->json($response);
    }

}