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

Route::get('/dados-externos', [ExternalApiController::class, 'index']);

// -------------------- LOGIN --------------------
// Exibir formulário de login
Route::get('/login', [LoginController::class, 'login'])->name('login');
// Processar login
Route::post('/login', [LoginController::class, 'postLogin'])->name('login.post');
// Logout
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// -------------------- REGISTRO --------------------
// Exibir formulário de registro
// Processar registro do usuário
Route::post('/register/posts', [RegisterController::class, 'register'])->name('register.post');
Route::get('/register', [RegisterController::class, 'registerAjax'])->name('register');

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
