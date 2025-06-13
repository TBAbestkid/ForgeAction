<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index(ExternalApiService $apiService)
    {
        $token = session('auth_token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado.');
        }

        // Chame a API para obter os dados do personagem
        $personagem = $apiService->getPersonagem($token); 

        if (!$personagem) {
            return back()->with('error', 'Erro ao buscar dados do personagem.');
        }

        return view('dashboard', compact('personagem'));
    }

}
