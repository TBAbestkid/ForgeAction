<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Logs;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExternalApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoogleController;

use App\Mail\TestMail;
use App\Mail\InviteMail;

/* -----------------------------------------
 *  Controllers da Api externa            /
 * --------------------------------------
 */
use App\Http\Controllers\BonusPersonagemController;
use App\Http\Controllers\PersonagemController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\SalaController;

Route::view('/loading', 'loading')->name('loading');
Route::view('/baixar', 'pwa.download')->name('pwa.download');

Route::post('/enviar-invite', function(Request $request) {
    $sala = Sala::find($request->salaId);
    $user = User::where('email', $request->email)->first();
    $remetente = auth()->user()->login; // usuário logado

    $link = route('room.selection', ['salaId' => $sala->id]); // link para seleção de personagem

    Mail::to($user->email)->send(new InviteMail($remetente, $sala->nome, $link));

    return response()->json([
        'success' => true,
        'message' => "Convite enviado para {$user->email}"
    ]);
});

Route::get('/dados-externos', [ExternalApiController::class, 'index']);

// -------------------- LOGIN --------------------
// Exibir formulário de login
Route::get('/login', [LoginController::class, 'login'])->name('login');
// Processar login
Route::post('/login', [LoginController::class, 'postLogin'])->name('login.post');
// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Update, para MASTER ou PLAYER
Route::put('/login/update', [LoginController::class, 'update'])->name('login.update');
// Esqueceu a senha?
Route::get('/login/forgot-password', [AuthController::class, 'forgotpassword'])->name('forgot-password');
Route::post('/login/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('forgot-password.send');
Route::get('/login/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/login/reset-password', [AuthController::class, 'reset'])->name('password.update');

// Login com Google
Route::get('/login/google', [GoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/login/google/callback', [GoogleController::class, 'handleGoogleCallback']);

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
Route::get('/perfil', [AuthController::class, 'profile'])->name('profile');

// Rota pra acessar apenas a view de criar personagem:
Route::get('/registro-personagem', [PersonagemController::class, 'personagem'])->name('registerPerson');

// Selecionar personagem
Route::post('/personagem/selecionar', [PersonagemController::class, 'select'])->name('character.select');
// Deselecionar personagem
Route::post('/personagem/deselecionar', [PersonagemController::class, 'deselect'])->name('character.deselect');
// Para testar os dados
Route::get('/dados-teste', [DashboardController::class, 'dice'])->name('dice');

// Atualizar perfil
Route::get('/perfil', [UserController::class, 'profile'])->name('profile');
Route::put('/perfil/email', [UserController::class, 'updateEmail'])->name('profile.updateEmail');
Route::put('/perfil/role', [UserController::class, 'updateRole'])->name('profile.updateRole');
Route::put('/perfil/senha', [UserController::class, 'updatePassword'])->name('profile.updatePassword');

/*
 *--------------------------------------------
 *   Rotas de personagem (API)              /
 *------------------------------------------
 */

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

// Rotas de página
Route::get('/salas', [SalaController::class, 'index'])->name('salas.index');
Route::get('/salas/criar', function() { return view('room.create'); })->name('salas.create');
Route::get('/salas/{id}', [SalaController::class, 'room'])->name('room.room');

// Rotas de API
Route::prefix('api')->group(function() {
    Route::get('/salas', [SalaController::class, 'get'])->name('salas.sala');
    Route::get('/salas/jogador/{usuarioId}', [SalaController::class, 'getByJogador'])->name('salas.jogador');
    Route::get('/salas/mestre/{usuarioId}', [SalaController::class, 'getByMestre'])->name('salas.mestre');
    Route::get('/salas/buscar/{nome}', [SalaController::class, 'getByNome'])->name('salas.buscar');

    Route::post('/salas', [SalaController::class, 'store'])->name('salas.store');
    Route::put('/salas/{id}', [SalaController::class, 'update'])->name('salas.update');
    Route::delete('/salas/{id}', [SalaController::class, 'destroy'])->name('salas.destroy');

    Route::get('/usuarios', [SalaController::class, 'invite'])->name('usuarios.invite');
    Route::post('/enviar-invite', [SalaController::class, 'sendInvite'])->name('enviar.invite');
    Route::get('/convite/{token}', [SalaController::class, 'acceptInvite'])->name('room.invite.accept');
});
