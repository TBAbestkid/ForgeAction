<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatusPersonagemController extends Controller
{
    // pagina principal
    public function exibicao()
    {
        return view('personagem.index');
    }

    // Pagina de criar personagem
    public function criar(){
        return view('personagem.criar');
    }

    // Pagina de atualizar personagem 
    public function atualiza(){
        return view('personagem.edit');
    }
    
    // Pagina de deletar personagem
    public function deleta(){
        // bem, não tem segredo, deletar né?
    }
    
    
}
