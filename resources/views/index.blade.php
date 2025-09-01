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
            <div class="d-flex flex-wrap gap-4">
                <!-- Personagem Selecionado -->
                <div class="card shadow-lg border-0 flex-fill" style="min-width: 320px; max-width: 380px;">
                    <div class="card-body bg-dark text-white rounded-3 p-4">

                        <h5 class="card-title mb-4 fw-bold">
                            <i class="fa-solid fa-user-astronaut me-2"></i> Personagem Selecionado
                        </h5>

                        <div class="d-flex align-items-center mb-4">
                            <i class="fa-solid fa-chess-knight fa-3x text-secondary"></i>

                            <div class="ms-3">
                                <strong class="fs-5 d-block">Nome do Personagem</strong>
                                <span class="badge bg-success">Equipado</span>
                            </div>
                        </div>

                        <p class="small fst-italic mb-4">
                            "Descrição breve do personagem selecionado..."
                        </p>

                        <div class="d-flex justify-content-between">
                            <a href="#" class="btn btn-outline-info btn-lg" title="Dashboard">
                                <i class="fa-solid fa-gauge-high"></i>
                            </a>
                            <a href="#" class="btn btn-outline-warning btn-lg" title="Histórico">
                                <i class="fa-solid fa-book-open"></i>
                            </a>
                            <a href="#" class="btn btn-outline-success btn-lg" title="Inventário">
                                <i class="fa-solid fa-suitcase"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Personagens Disponíveis -->
                <div class="card shadow-lg border-0 flex-fill text-white" style="min-width: 320px; max-width: 420px;">
                    <div class="card-body rounded-3 p-4">

                        <h6 class="fw-bold text-light mb-3">
                            <i class="fa-solid fa-users me-2"></i> Personagens Disponíveis
                        </h6>

                        <!-- Barra de pesquisa -->
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-dark text-light border-secondary">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" id="searchCharacter" class="form-control bg-dark text-white border-secondary"
                                placeholder="Pesquisar por nome, raça ou classe...">
                        </div>

                        <!-- Lista de personagens com scroll -->
                        <ul id="characterList" class="list-group list-group-flush bg-dark"
                            style="max-height: 250px; overflow-y: auto;">

                            <li class="bg-dark text-white list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Nome 1</strong><br>
                                    <small>Raça: Humano | Classe: Guerreiro</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-check me-1"></i>
                                </button>
                            </li>

                            <li class="bg-dark text-white list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Nome 2</strong><br>
                                    <small>Raça: Elfo | Classe: Mago</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-check me-1"></i>
                                </button>
                            </li>

                            <li class="bg-dark text-white list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Nome 3</strong><br>
                                    <small>Raça: Anão | Classe: Clérigo</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-check me-1"></i>
                                </button>
                            </li>

                            <!-- Exemplos extras -->
                            <li class="bg-dark text-white list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Nome 4</strong><br>
                                    <small>Raça: Orc | Classe: Bárbaro</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-check me-1"></i>
                                </button>
                            </li>

                            <li class="bg-dark text-white list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Nome 5</strong><br>
                                    <small>Raça: Humano | Classe: Arqueiro</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-check me-1"></i>
                                </button>
                            </li>
                        </ul>

                        <div class="mt-4 text-center">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary w-100">
                                <i class="fa-solid fa-id-card me-2"></i> Ver Todos no Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card com Botões -->
                <div class="card text-start shadow-sm flex-fill" style="min-width: 200px;">
                    <div class="card-body rounded">
                        <div class="d-grid gap-2">
                            <a href="{{ route('registerPerson') }}" class="btn btn-outline-light">
                                <i class="fa-solid fa-user-plus me-1"></i> Adicionar Personagem
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                                <i class="fa-solid fa-id-card me-1"></i> Ver Fichas
                            </a>
                            <button class="btn btn-outline-success">
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
<script>
    document.getElementById("searchCharacter").addEventListener("input", function() {
        const searchTerm = this.value.toLowerCase();
        const items = document.querySelectorAll("#characterList li");

        items.forEach(item => {
            const text = item.innerText.toLowerCase();
            item.style.display = text.includes(searchTerm) ? "" : "none";
        });
    });
</script>
