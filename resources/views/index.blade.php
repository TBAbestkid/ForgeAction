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
            <div class="container mt-4">
                <h1 class="font-medieval text-center mb-4">Seus Personagens</h1>

                <div class="row g-4">

                    <!-- Coluna 1: Personagem Selecionado -->
                    <div class="col-md-4">
                        <div class="card shadow-lg border-0 flex-fill" style="min-width: 320px; max-width: 380px;">
                            <div class="card-body text-white rounded-3 p-4">
                                <h5 class="card-title mb-4 fw-bold">
                                    <i class="fa-solid fa-user-astronaut me-2"></i> Personagem Selecionado
                                </h5>

                                @if(session('selected_character'))
                                    <div class="d-flex align-items-center mb-4">
                                        <i class="fa-solid fa-chess-knight fa-3x text-secondary"></i>
                                        <div class="ms-3">
                                            <strong class="fs-5 d-block">{{ session('selected_character.name') }}</strong>
                                            <span class="badge bg-success">Equipado</span>
                                            <p class="mb-0">{{ session('selected_character.raca') }} | {{ session('selected_character.classe') }}</p>
                                        </div>
                                    </div>
                                    <p class="small fst-italic mb-4">{{ session('selected_character.description') ?? 'Descrição breve do personagem selecionado...' }}</p>
                                @else
                                    <div class="d-flex align-items-center mb-4">
                                        <i class="fa-regular fa-circle-user fa-3x text-secondary"></i>
                                        <div class="ms-3">
                                            <strong class="fs-5 d-block">Nenhum personagem</strong>
                                        </div>
                                    </div>
                                    <p class="small fst-italic mb-4">Nenhum personagem selecionado.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Coluna 2: Lista de Personagens -->
                    <div class="col-md-4">
                        <div class="card shadow-lg border-0 flex-fill text-white" style="min-width: 320px; max-width: 100%;">
                            <div class="card-body rounded-3 p-4">
                                <h6 class="fw-bold text-light mb-3"><i class="fa-solid fa-users me-2"></i> Personagens Disponíveis</h6>

                                <!-- Barra de pesquisa -->
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-dark text-light border-secondary">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <input type="text" id="searchCharacter" class="form-control bg-dark text-white border-secondary"
                                        placeholder="Pesquisar por nome, raça ou classe...">
                                </div>

                                <ul id="characterList" class="list-group list-group-flush bg-dark" style="max-height: 400px; overflow-y: auto;">
                                    @foreach($personagens as $p)
                                        @php
                                            $info = $p['infoPersonagem'];
                                            $racas = [
                                                "DRACONATO"=>"Draconato","TIEFLING"=>"Tiefling","HALFLING"=>"Halfling","ANAO"=>"Anão",
                                                "HUMANO"=>"Humano","ELFO"=>"Elfo","ORC"=>"Orc",
                                                "BRUTE_MEIO_ORC_HUMANO"=>"Brute (meio-orc + humano)",
                                                "BRUTE_MEIO_ORC_ELFO"=>"Brute (meio-orc + elfo)",
                                                "TARNISHED_ELFO_HUMANO"=>"Tarnished (elfo + humano)",
                                                "TARNISHED_ELFO_TIEFLING"=>"Tarnished (elfo + tiefling)"
                                            ];
                                            $classes = [
                                                "ATIRADOR"=>"Atirador","CACADOR"=>"Caçador","GUERREIRO"=>"Guerreiro",
                                                "PALADINO"=>"Paladino","ESPADACHIM"=>"Espadachim","ASSASSINO"=>"Assassino",
                                                "LADRAO"=>"Ladrão","FEITICEIRO"=>"Feiticeiro","BRUXO"=>"Bruxo","MAGO"=>"Mago",
                                                "CLERIGO"=>"Clérigo","MONGE"=>"Monge","XAMA"=>"Xamã","DRUIDA"=>"Druida",
                                                "ARTIFICE"=>"Artífice","BARDO"=>"Bardo"
                                            ];
                                        @endphp

                                        <li class="bg-dark text-white list-group-item d-flex justify-content-between align-items-center personagem-item"
                                            data-nome="{{ strtolower($info['nome']) }}"
                                            data-raca="{{ strtolower($info['raca']) }}"
                                            data-classe="{{ strtolower($info['classe']) }}">
                                            <div>
                                                <strong>{{ $info['nome'] }}</strong><br>
                                                <small>Raça: {{ $racas[$info['raca']] ?? $info['raca'] }} | Classe: {{ $classes[$info['classe']] ?? $info['classe'] }}</small>
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary select-btn" data-character='@json($p)'>
                                                <i class="fa-solid fa-check me-1"></i> Selecionar
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna 3: Botões de Ação -->
                    <div class="col-md-4">
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
    document.addEventListener('DOMContentLoaded', function () {

        // Pesquisa rápida
        const searchInput = document.getElementById('searchCharacter');
        const listItems = document.querySelectorAll('.personagem-item');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();

            listItems.forEach(item => {
                const nome = item.dataset.nome;
                const raca = item.dataset.raca;
                const classe = item.dataset.classe;

                if(nome.includes(query) || raca.includes(query) || classe.includes(query)){
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Selecionar personagem via AJAX
        const selectButtons = document.querySelectorAll('.select-btn');
        selectButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                // Converte string JSON de volta para objeto
                const character = JSON.parse(this.dataset.character);

                fetch("{{ route('character.select') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(character) // envia como JSON
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        location.reload(); // Atualiza o card de personagem selecionado
                    }
                });
            });
        });
    });

</script>
