<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Logs;
use App\Http\Controllers\LoginController;


Route::get('/', function () {
    return view('index');
});

Route::get('/dados-externos', [ExternalApiController::class, 'index']);

// Exibir formulário de login
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'postLogin'])->name('login.post');

// Exibir formulário de registro (GET)
Route::get('/register-two', [LoginController::class, 'cadastro'])->name('registertwo');

// Exibir formulário de registro (GET)
Route::get('/register', [LoginController::class, 'cadastro'])->name('registertwo');

// Processar o formulário (POST)
Route::post('/register-two/criado', [LoginController::class, 'post'])->name('registertwo.post');

// Exibir formulário de registro
Route::get('/sobre-forgeaction', [LoginController::class, 'about'])->name('about');

// personagem
Route::get('/meu-personagem', [Controller::class, 'index'])->name('personagem.index');

// Rotas de POST (processamento dos formulários)

Route::post('/auth/register', [AuthController::class, 'login-reg']);

Route::get('/characters', [ExternalApiController::class, 'getCharacters']);
Route::post('/characters', [ExternalApiController::class, 'createCharacter']);
Route::put('/characters/{id}', [ExternalApiController::class, 'updateCharacter']);
Route::delete('/characters/{id}', [ExternalApiController::class, 'deleteCharacter']);


// Exibir formulário de registro (GET)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
