<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Home acessível para todos
    public function index()
    {
        return view('index');
    }

    // Sobre
    public function about()
    {
        return view('about');
    }

    // Dashboard apenas logado
    public function dash(Request $request)
    {
        if (!$request->session()->has('user_login')) {
            return redirect()->route('home')->with('error', 'Você precisa estar logado.');
        }

        return view('dashboard'); // view para usuários logados
    }
}
