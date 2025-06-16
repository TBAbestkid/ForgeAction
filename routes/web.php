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


Route::get('/', function () {
    return view('index');
});

Route::get('/dados-externos', [ExternalApiController::class, 'index']);

// Exibir formulário de login
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'postLogin'])->name('login.post');

// logout
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');


// Exibir formulário de registro (GET)
Route::get('/register-primeiro', [RegisterController::class, 'parteUm'])->name('register');
Route::post('/register-primeiro/criado', [LoginController::class, 'primeiroCadastro'])->name('register.post');

// Exibir formulário de registro (GET) - segunda parte
Route::get('/register-segundo', [RegisterController::class, 'parteDois'])->name('registertwo');
Route::post('/register-segundo', [LoginController::class, 'segundoCadastro'])->name('registertwo.post');

// Exibir formulário de registro (GET) - terceira parte
Route::get('/register-terceiro', [RegisterController::class, 'parteTres'])->name('registerthree');
Route::post('/register-terceiro', [LoginController::class, 'terceiroCadastro'])->name('registerthree.post');



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
