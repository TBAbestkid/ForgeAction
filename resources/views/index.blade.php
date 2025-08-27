@extends('partials/app')
@section('title', 'Página Inicial - ForgeAction')
@section('content')
<!-- Conteúdo Principal -->
<div class="container mt-5 font-medieval">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('user_login'))
       <div class="container-fluid">
            <div class="d-flex flex-wrap gap-3">
                <!-- Card de Informações do Personagem -->
                <div class="card text-start shadow-sm flex-fill" style="min-width: 300px;">
                    <div class="card-body text-white rounded">
                        <h5 class="card-title mb-3">Seu personagem</h5>

                        <div class="d-flex align-items-center mb-3">
                            @if(session('character'))
                                <img src="{{ asset('assets/images/characters/' . session('character')->image) }}"
                                    alt="Personagem"
                                    class="img-fluid rounded"
                                    style="width: 64px; height: 64px; object-fit: cover;">
                            @else
                                <i class="fa-regular fa-circle-user fa-2xl"></i>
                            @endif

                            <div class="ms-3">
                                <strong class="d-block">{{ session('character') ? session('character')->name : 'Nenhum personagem' }}</strong>
                                <small class="">Status: {{ session('character') && session('character')->equipped ? 'Equipado' : 'Não equipado' }}</small>
                            </div>
                        </div>

                        <p class="small mb-3">Descrição: <em>{{ session('character') ? session('character')->description : 'Nenhuma descrição disponível.' }}</em></p>

                        <div class="d-flex justify-content-around">
                            <a href="#" class="btn btn-outline-info" title="Ver no Dashboard" style="font-size: 1.5rem;">
                                <i class="fa-solid fa-gauge-high"></i>
                            </a>
                            <a href="#" class="btn btn-outline-warning" title="Histórico" style="font-size: 1.5rem;">
                                <i class="fa-solid fa-book-open"></i>
                            </a>
                            <a href="#" class="btn btn-outline-success" title="Inventário" style="font-size: 1.5rem;">
                                <i class="fa-solid fa-suitcase"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card com Botões -->
                <div class="card text-start shadow-sm flex-fill" style="min-width: 200px;">
                    <div class="card-body rounded">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary">
                                <i class="fa-solid fa-id-card me-1"></i> Ver Ficha
                            </button>
                            <button class="btn btn-outline-secondary">
                                <i class="fa-solid fa-user-pen me-1"></i> Editar Aparência
                            </button>
                            <button class="btn btn-outline-success ">
                                <i class="fa-solid fa-shield-halved me-1"></i> Equipamentos
                            </button>
                            <button class="btn btn-outline-danger">
                                <i class="fa-solid fa-trash me-1"></i> Remover
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Bloco dos cards de login, cadastro e sobre --}}
        <div class="row text-white">
            <div class="col-md-12 text-center">
                <img src="{{ asset('assets/images/forgeicon.png') }}" alt="ForgeAction Logo" class="logo-center">
                <h1>ForgeAction</h1>
                <p class="lead">Prepare-se para a aventura épica!</p>
            </div>
        </div>
        <div class="row mt-5 text-white">
            <!-- Card Login -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body text-white">
                        <h5 class="card-title">Já tem login?</h5>
                        <p class="card-text ">Acesse sua conta e continue a aventura.</p>
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    </div>
                </div>
            </div>

            <!-- Card Cadastro -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body text-white">
                        <h5 class="card-title">Ainda não é cadastrado?</h5>
                        <p class="card-text">Crie sua conta e embarque nessa jornada.</p>
                        <a href="{{ route('register') }}" class="btn btn-success">Cadastro</a>
                    </div>
                </div>
            </div>

            <!-- Card Sobre -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body text-white">
                        <h5 class="card-title">Sobre</h5>
                        <p class="card-text">Saiba mais sobre o ForgeAction.</p>
                        <a href="{{ route('about') }}" class="btn btn-info">Sobre</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
