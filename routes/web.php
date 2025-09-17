<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Logs;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExternalApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Controller;

use App\Http\Controllers\AtaquePersonagemController;
use App\Http\Controllers\StatusPersonagemController;
use App\Http\Controllers\InfoPersonagemController;
use App\Http\Controllers\EquipPersonagemController;
use App\Http\Controllers\DefesaPersonagemController;
use App\Http\Controllers\DanoBasePersonagemController;
use App\Http\Controllers\DadosDefesaPersonagemController;
use App\Http\Controllers\DadosAtaquePersonagemController;
use App\Http\Controllers\BonusPersonagemController;
use App\Http\Controllers\AtributoPersonagemController;
use App\Http\Controllers\PersonagemController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\SalaPersonagemController;
use App\Http\Controllers\SalaController;


Route::get('/dados-externos', [ExternalApiController::class, 'index']);

// -------------------- LOGIN --------------------
// Exibir formulário de login
Route::get('/login', [LoginController::class, 'login'])->name('login');
// Processar login
Route::post('/login', [LoginController::class, 'postLogin'])->name('login.post');
// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Update, para MASTER
Route::put('/login/update', [LoginController::class, 'update'])->name('login.update');

// -------------------- REGISTRO --------------------
// Exibir formulário de registro
Route::get('/register', [RegisterController::class, 'register'])->name('register');

// Processar registro do usuário
Route::post('/register', [RegisterController::class, 'registerPost'])->name('register.post');

// Redireciona '/' para '/home'
Route::get('/', function () {
    return redirect()->route('home');
});

// Home
Route::get('/home', [DashboardController::class, 'index'])->name('home');

// Sobre
Route::get('/sobre-forgeaction', [DashboardController::class, 'about'])->name('about');

// Apenas logado
Route::get('/dashboard', [DashboardController::class, 'dash'])->name('dashboard');

// Rota pra acessar apenas a view de criar personagem:
Route::get('/registro-personagem', [PersonagemController::class, 'personagem'])->name('registerPerson');

// Selecionar personagem
Route::post('/character/select', [PersonagemController::class, 'select'])->name('character.select');


/*
 *--------------------------------------------
 *   Rotas de personagem (API)              /
 *------------------------------------------
 */

// Ataques do personagem
Route::get('/ataque-personagem/{personagemId}', [AtaquePersonagemController::class, 'show']);
Route::post('/ataque-personagem', [AtaquePersonagemController::class, 'store']);
Route::put('/ataque-personagem/{personagemId}', [AtaquePersonagemController::class, 'update']);
Route::delete('/ataque-personagem/{personagemId}', [AtaquePersonagemController::class, 'destroy']);

// Status do personagem
Route::get('/status-personagem/{personagemId}', [StatusPersonagemController::class, 'show']);
Route::post('/status-personagem', [StatusPersonagemController::class, 'store']);
Route::put('/status-personagem/{personagemId}', [StatusPersonagemController::class, 'update']);
Route::delete('/status-personagem/{personagemId}', [StatusPersonagemController::class, 'destroy']);

// Informações do personagem
Route::get('/info-personagem/{personagemId}', [InfoPersonagemController::class, 'show']);
Route::post('/info-personagem', [InfoPersonagemController::class, 'store']);
Route::put('/info-personagem/{personagemId}', [InfoPersonagemController::class, 'update']);
Route::delete('/info-personagem/{personagemId}', [InfoPersonagemController::class, 'destroy']);

// Equipamentos do personagem
Route::get('/equip-personagem/{personagemId}', [EquipPersonagemController::class, 'show']);
Route::post('/equip-personagem', [EquipPersonagemController::class, 'store']);
Route::put('/equip-personagem/{personagemId}', [EquipPersonagemController::class, 'update']);
Route::delete('/equip-personagem/{personagemId}', [EquipPersonagemController::class, 'destroy']);

// Defesa do personagem
Route::get('/defesa-personagem/{personagemId}', [DefesaPersonagemController::class, 'show']);
Route::post('/defesa-personagem', [DefesaPersonagemController::class, 'store']);
Route::put('/defesa-personagem/{personagemId}', [DefesaPersonagemController::class, 'update']);
Route::delete('/defesa-personagem/{personagemId}', [DefesaPersonagemController::class, 'destroy']);

// Dano base do personagem
Route::get('/dano-base-personagem/{personagemId}', [DanoBasePersonagemController::class, 'show']);
Route::post('/dano-base-personagem', [DanoBasePersonagemController::class, 'store']);
Route::put('/dano-base-personagem/{personagemId}', [DanoBasePersonagemController::class, 'update']);
Route::delete('/dano-base-personagem/{personagemId}', [DanoBasePersonagemController::class, 'destroy']);

// Dados de defesa do personagem
Route::get('/dados-defesa-personagem/{personagemId}', [DadosDefesaPersonagemController::class, 'show']);
Route::post('/dados-defesa-personagem', [DadosDefesaPersonagemController::class, 'store']);
Route::put('/dados-defesa-personagem/{personagemId}', [DadosDefesaPersonagemController::class, 'update']);
Route::delete('/dados-defesa-personagem/{personagemId}', [DadosDefesaPersonagemController::class, 'destroy']);

// Dados de ataque do personagem
Route::get('/dados-ataque-personagem/{personagemId}', [DadosAtaquePersonagemController::class, 'show']);
Route::post('/dados-ataque-personagem', [DadosAtaquePersonagemController::class, 'store']);
Route::put('/dados-ataque-personagem/{personagemId}', [DadosAtaquePersonagemController::class, 'update']);
Route::delete('/dados-ataque-personagem/{personagemId}', [DadosAtaquePersonagemController::class, 'destroy']);

// Bonus do personagem
Route::get('/bonus-personagem/{personagemId}', [BonusPersonagemController::class, 'show']);
Route::post('/bonus-personagem', [BonusPersonagemController::class, 'store']);
Route::put('/bonus-personagem/{personagemId}', [BonusPersonagemController::class, 'update']);
Route::delete('/bonus-personagem/{personagemId}', [BonusPersonagemController::class, 'destroy']);

// Atributos do personagem
Route::get('/atributos-personagem/{personagemId}', [AtributoPersonagemController::class, 'show']);
Route::post('/atributos-personagem', [AtributoPersonagemController::class, 'store']);
Route::put('/atributos-personagem/{personagemId}', [AtributoPersonagemController::class, 'update']);
Route::delete('/atributos-personagem/{personagemId}', [AtributoPersonagemController::class, 'destroy']);

// Personagem principal
Route::get('/personagem/{personagemId}', [PersonagemController::class, 'show']);
Route::post('/personagem', [PersonagemController::class, 'store']);
Route::delete('/personagem/{personagemId}', [PersonagemController::class, 'destroy']);
Route::get('/personagem/usuario/{usuarioId}', [PersonagemController::class, 'showByUsuario']);

// Apenas tudo néh
Route::get('/enums/personagem', [EnumController::class, 'getPersonagemEnums']);

// Enum de raças e classes
Route::get('/enums/racas', [EnumController::class, 'racas']);
Route::get('/enums/classes', [EnumController::class, 'classes']);
Route::get('/enums/bonus-racas/{raca}', [EnumController::class, 'bonusRacas']);

// Página que lista as salas do usuário
Route::get('/salas', [SalaController::class, 'index'])->name('salas.index');

// Página de criar sala
Route::get('/salas/criar', function() {
    return view('room.create');
})->name('salas.create');

// Rotas POST, PUT, DELETE continuam apontando para a API
Route::post('/salas', [SalaController::class, 'store'])->name('salas.store');
Route::put('/salas/{id}', [SalaController::class, 'update'])->name('salas.update');
Route::delete('/salas/{id}', [SalaController::class, 'destroy'])->name('salas.destroy');
Route::get('/salas/usuario/{usuarioId}', [SalaController::class, 'getByUsuario'])->name('salas.usuario');

// SalaPersonagem
Route::get('/sala-personagem/sala/{salaId}', [SalaPersonagemController::class, 'showBySala']);
Route::get('/sala-personagem/personagem/{personagemId}', [SalaPersonagemController::class, 'showByPersonagem']);
Route::post('/sala-personagem', [SalaPersonagemController::class, 'store']);
Route::delete('/sala-personagem/{id}', [SalaPersonagemController::class, 'destroy']);
Route::delete('/sala-personagem/sala/{salaId}/personagem/{personagemId}', [SalaPersonagemController::class, 'destroyBySalaAndPersonagem']);
