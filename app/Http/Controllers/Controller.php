<?php

namespace App\Http\Controllers;

abstract class Controller
{
    // Aqui vai ser o controller de personagem sim.
    public function index(){
        return view('personagem.index');
    }
    // Exibe a view de registro
    public function about(){
        return view('about');
    }

}
