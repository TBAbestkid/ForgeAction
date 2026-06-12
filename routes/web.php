<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PersonagemController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SalaApiController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\UserController;

Route::view('/loading', 'loading')->name('loading');
Route::view('/chat-teste', 'chat')->name('chat');
Route::view('/baixar', 'pwa.download')->name('pwa.download');

Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'postLogin'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/login/forgot-password', [AuthController::class, 'forgotpassword'])->name('forgot-password');
Route::post('/login/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('forgot-password.send');
Route::get('/login/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/login/reset-password', [AuthController::class, 'reset'])->name('password.update');

Route::get('/login/google', [GoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/login/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/register', [RegisterController::class, 'registerPost'])->name('register.post');

Route::get('/', fn () => redirect()->route('home'));
Route::get('/home', [DashboardController::class, 'index'])->name('home');
Route::get('/sobre-forgeaction', [DashboardController::class, 'about'])->name('about');
Route::get('/dados-teste', [DashboardController::class, 'dice'])->name('dice');

Route::get('/enums/personagem', [EnumController::class, 'getPersonagemEnums']);
Route::get('/enums/racas', [EnumController::class, 'racas']);
Route::get('/enums/classes', [EnumController::class, 'classes']);
Route::get('/enums/bonus-racas/{raca}', [EnumController::class, 'bonusRacas']);

// Invitation acceptance must stay public so anonymous users can be sent to login.
Route::get('/api/convite/{token}', [SalaController::class, 'acceptInvite'])->name('api.invite.accept');

Route::middleware('user.session')->group(function () {
    Route::get('/perfil', [UserController::class, 'profile'])->name('profile');
    Route::put('/perfil/email', [UserController::class, 'updateEmail'])->name('profile.updateEmail');
    Route::put('/perfil/role', [UserController::class, 'updateRole'])->name('profile.updateRole');
    Route::put('/perfil/senha', [UserController::class, 'updatePassword'])->name('profile.updatePassword');

    Route::get('/usuario', [UserController::class, 'get'])->name('api.users.list');
    Route::get('/usuario/{usuarioId}', [UserController::class, 'getById'])->name('api.users.show');

    Route::get('/registro-personagem', [PersonagemController::class, 'personagem'])->name('registerPerson');
    Route::get('/personagem/usuario/{usuarioId}', [PersonagemController::class, 'showByUsuario']);
    Route::get('/personagem/{personagemId}', [PersonagemController::class, 'show']);
    Route::post('/personagem', [PersonagemController::class, 'store'])->name('personagem.store');
    Route::put('/personagem/{personagemId}', [PersonagemController::class, 'update'])->name('personagem.update');
    Route::delete('/personagem/{personagemId}', [PersonagemController::class, 'destroy']);

    Route::get('/salas', [SalaController::class, 'index'])->name('salas.index');
    Route::get('/salas/criar', [SalaController::class, 'createRoom'])->name('salas.create');
    Route::get('/salas/entrar/codigo', [SalaController::class, 'enterByCode'])->name('salas.codigo.entrar');
    Route::get('/salas/{id}', [SalaController::class, 'room'])->name('room.room');
    Route::post('/salas/personagens/adicionar/{salaId}', [SalaController::class, 'adicionarPersonagem']);
});

Route::middleware('user.session')->prefix('api')->group(function () {
    Route::get('/salas', [SalaApiController::class, 'get'])->name('api.salas.get');
    Route::post('/salas', [SalaApiController::class, 'store'])->name('api.salas.store');

    Route::get('/salas/jogador/{usuarioId}', [SalaApiController::class, 'getByJogador'])->name('api.salas.jogador');
    Route::get('/salas/mestre/{usuarioId}', [SalaApiController::class, 'getByMestre'])->name('api.salas.mestre');
    Route::get('/salas/buscar/{nome}', [SalaApiController::class, 'getByNome'])->name('api.salas.buscar');

    Route::get('/salas/personagens/listar/{salaId}', [SalaApiController::class, 'listarPersonagens'])->name('api.salas.personagens.listar');
    Route::post('/salas/personagens/adicionar/{salaId}/{personagemId}', [SalaApiController::class, 'adicionarPersonagem'])->name('api.salas.personagens.adicionar');
    Route::delete('/salas/personagens/remover/{salaId}/{personagemId}', [SalaApiController::class, 'removerPersonagem'])->name('api.salas.personagens.remover');

    Route::get('/usuario', [SalaController::class, 'invite'])->name('api.usuarios.invite');
    Route::post('/enviar-invite', [SalaController::class, 'sendInvite'])->name('api.enviar.invite');

    Route::get('/codigo/{codigo}', [SalaApiController::class, 'getByCode'])->name('api.salas.codigo');
    Route::post('/codigo/adicionar', [SalaApiController::class, 'adicionarPersonagemByCode'])->name('api.salas.codigo.adiciona');

    Route::get('/salas/{id}', [SalaApiController::class, 'getById'])->name('api.salas.getById');
    Route::put('/salas/{id}', [SalaApiController::class, 'update'])->name('api.salas.update');
    Route::delete('/salas/{id}', [SalaApiController::class, 'destroy'])->name('api.salas.destroy');
});
