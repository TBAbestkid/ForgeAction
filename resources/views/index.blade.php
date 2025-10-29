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
                <h1 class="font-medieval text-start mb-4">Seus Personagens</h1>

                <div class="d-flex flex-wrap gap-4 justify-content-center">
                    <!-- Coluna 1: Personagem Selecionado -->
                    <div class="flex-fill" style="min-width: 320px; max-width: 380px;">
                        <div class="card shadow-lg border-0 h-100">
                            <div class="card-body text-white rounded-3 p-4">
                                <h5 class="card-title mb-4 fw-bold">
                                    <i class="fa-solid fa-user-astronaut me-2"></i> Personagem Selecionado
                                </h5>

                                @if(session('selected_character'))
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fa-solid fa-chess-knight fa-3x text-secondary"></i>
                                        <div class="ms-3">
                                            <strong class="fs-5 d-block">{{ session('selected_character.name') }}</strong>
                                            <span class="badge bg-success">Equipado</span>
                                            <p class="mb-0">{{ session('selected_character.raca') }} | {{ session('selected_character.classe') }}</p>
                                        </div>
                                    </div>
                                    <p class="small fst-italic mb-3">{{ session('selected_character.description') ?? 'Descrição breve do personagem selecionado...' }}</p>
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
                    <div class="flex-fill" style="min-width: 320px; max-width: 400px;">
                        <div class="card shadow-lg border-0 text-white h-100">
                            <div class="card-body rounded-3 p-4">
                                <h6 class="fw-bold text-light mb-3"><i class="fa-solid fa-users me-2"></i> Personagens Disponíveis</h6>

                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-dark text-light border-secondary">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <input type="text" id="searchCharacter" class="form-control text-white border-secondary"
                                        placeholder="Pesquisar por nome, raça ou classe...">
                                </div>

                                <ul id="characterList" class="list-group list-group-flush bg-dark" style="max-height: 400px; overflow-y: auto;">
                                    <li class="list-group-item bg-dark text-white text-center" id="loadingCharacters">
                                        <i class="fa-solid fa-spinner fa-spin me-2"></i> Carregando personagens...
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna 3: Botões de Ação -->
                    <div class="flex-fill" style="min-width: 200px; max-width: 300px;">
                        <div class="card text-start shadow-sm h-100">
                            <div class="card-body rounded d-flex flex-column justify-content-center">
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

                <!-- Salas -->
                <div class="container mt-5">
                    <h1 class="font-medieval text-start mb-4">Salas</h1>
                    <div class="d-flex flex-wrap gap-4 justify-content-center align-items-start">
                        <!-- Card principal -->
                        <div class="flex-grow-1 flex-shrink-1" style="flex-basis: 600px; min-width: 320px;">
                            <div class="card shadow border-0 h-100">
                                <div class="card-body text-white rounded-3 p-4" id="salas-container">
                                    <div class="d-flex align-items-center justify-content-center gap-2 bg-dark text-light fw-bold rounded-3 p-3 shadow-sm">
                                        <i class="fa-solid fa-spinner fa-spin fa-lg"></i>
                                        Carregando salas...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões pra Mestre -->
                        <div class="flex-grow-0 flex-shrink-1 master-only" style="flex-basis: 300px; min-width: 200px;" @if(session('user_role') !== 'MASTER') style="display:none" @endif>
                            <div class="card shadow border-0 h-100">
                                <div class="card-body text-white rounded-3 p-4 d-flex flex-column justify-content-start">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('salas.index') }}" class="btn btn-outline-success">
                                            <i class="fa-solid fa-user-plus"></i> Todas as salas
                                        </a>
                                        <a href="{{ route('salas.create') }}" class="btn btn-outline-primary">
                                            <i class="fa-solid fa-user-group"></i> Criar Sala
                                        </a>
                                        <a href="{{ route('dashboard') }}" class="btn btn-outline-danger">
                                            <i class="fa-solid fa-trash me-1"></i> Remover Sala
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Bloco dos cards de login, cadastro, sobre e download --}}
        <div class="container text-white d-flex flex-column align-items-center justify-content-center min-vh-100">
            <div class="text-center mb-5">
                <img src="{{ asset('assets/images/forgeicon.png') }}" alt="ForgeAction Logo" class="logo-center mb-3" style="max-width:150px;">
                <h1>ForgeAction</h1>
                <p class="lead">Prepare-se para a aventura épica!</p>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-4 w-100" style="max-width: 900px;">
                <!-- Card Login -->
                <div class="card bg-dark text-white text-center flex-fill" style="min-width: 260px; max-width: 300px;">
                    <div class="card-body">
                        <h5 class="card-title">Já tem login?</h5>
                        <p class="card-text">Acesse sua conta e continue a aventura.</p>
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    </div>
                </div>

                <!-- Card Cadastro -->
                <div class="card bg-dark text-white text-center flex-fill" style="min-width: 260px; max-width: 300px;">
                    <div class="card-body">
                        <h5 class="card-title">Ainda não é cadastrado?</h5>
                        <p class="card-text">Crie sua conta e embarque nessa jornada.</p>
                        <a href="{{ route('register') }}" class="btn btn-success">Cadastro</a>
                    </div>
                </div>

                <!-- Card Sobre -->
                <div class="card bg-dark text-white text-center flex-fill" style="min-width: 260px; max-width: 300px;">
                    <div class="card-body">
                        <h5 class="card-title">Sobre</h5>
                        <p class="card-text">Saiba mais sobre o ForgeAction.</p>
                        <a href="{{ route('about') }}" class="btn btn-info">Sobre</a>
                    </div>
                </div>

                <!-- Card Baixar App -->
                <div id="installCard" class="card bg-dark text-white text-center flex-fill fade-in-card"
                    style="min-width: 260px; max-width: 300px; display:none;">
                    <div class="card-body">
                        <h5 class="card-title">Instale o App</h5>
                        <p class="card-text">Use o ForgeAction direto no seu dispositivo!</p>
                        <button id="installBtn" class="btn btn-warning glow-btn">
                            <i class="fa-solid fa-download me-1"></i> Instalar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@include('partials/loading')
@include('partials/alerts')
<script src="{{ asset('js/loading.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {

        /* -------------------------------------------------------------
        🔍 PESQUISA DE PERSONAGENS
        ------------------------------------------------------------- */
        const $searchInput = $("#searchCharacter");

        $searchInput.on("input", function () {
            const query = $(this).val().toLowerCase();

            $(".personagem-item").each(function () {
                const nome = $(this).data("nome");
                const raca = $(this).data("raca");
                const classe = $(this).data("classe");

                if (nome.includes(query) || raca.includes(query) || classe.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        /* -------------------------------------------------------------
        🧙‍♂️ CARREGAR PERSONAGENS (AJAX)
        ------------------------------------------------------------- */
        const $characterList = $("#characterList");

        function loadPersonagens() {
            $.ajax({
                url: "/personagem/usuario/{{ session('user_id') }}",
                method: "GET",
                success: function (response) {
                    $characterList.empty(); // limpa o conteúdo anterior

                    const personagens = response.data || [];

                    if (personagens.length > 0) {
                        personagens.forEach(p => {
                            const racas = {
                                "DRACONATO": "Draconato",
                                "TIEFLING": "Tiefling",
                                "HALFLING": "Halfling",
                                "ANAO": "Anão",
                                "HUMANO": "Humano",
                                "ELFO": "Elfo",
                                "ORC": "Orc",
                                "BRUTE_MEIO_ORC_HUMANO": "Brute (meio-orc + humano)",
                                "BRUTE_MEIO_ORC_ELFO": "Brute (meio-orc + elfo)",
                                "TARNISHED_ELFO_HUMANO": "Tarnished (elfo + humano)",
                                "TARNISHED_ELFO_TIEFLING": "Tarnished (elfo + tiefling)"
                            };

                            const classes = {
                                "ATIRADOR": "Atirador",
                                "CACADOR": "Caçador",
                                "GUERREIRO": "Guerreiro",
                                "PALADINO": "Paladino",
                                "ESPADACHIM": "Espadachim",
                                "ASSASSINO": "Assassino",
                                "LADRAO": "Ladrão",
                                "FEITICEIRO": "Feiticeiro",
                                "BRUXO": "Bruxo",
                                "MAGO": "Mago",
                                "CLERIGO": "Clérigo",
                                "MONGE": "Monge",
                                "XAMA": "Xamã",
                                "DRUIDA": "Druida",
                                "ARTIFICE": "Artífice",
                                "BARDO": "Bardo"
                            };

                            $characterList.append(`
                                <div class="personagem-card bg-dark text-white p-3 mb-2 rounded-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="fs-5">${p.nome}</strong><br>
                                        <small>
                                            Raça: ${racas[p.raca] ?? p.raca} |
                                            Classe: ${classes[p.classe] ?? p.classe}
                                        </small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary select-btn" data-character='${JSON.stringify(p)}'>
                                        <i class="fa-solid fa-check me-1"></i> Selecionar
                                    </button>
                                </div>
                            `);
                        });

                        attachCharacterEvents(); // reativa os botões
                    } else {
                        $characterList.html(`
                            <div class="text-center text-light py-3 bg-dark rounded">
                                <i class="fa-solid fa-circle-exclamation"></i> Nenhum personagem encontrado.
                            </div>
                        `);
                    }
                },
                error: function () {
                    $characterList.html(`
                        <div class="text-center text-danger py-3 bg-dark rounded">
                            <i class="fa-solid fa-triangle-exclamation"></i> Erro ao carregar personagens.
                        </div>
                    `);
                }
            });
        }

        /* -------------------------------------------------------------
        🧩 SELECIONAR PERSONAGEM (AJAX)
        ------------------------------------------------------------- */
        function attachCharacterEvents() {
            $(".select-btn").off("click").on("click", function () {
                const character = $(this).data("character");

                $.ajax({
                    url: "{{ route('character.select') }}",
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(character),
                    headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    success: function (data) {
                        if (data.success) location.reload();
                    },
                    error: function () {
                        alert("Erro ao selecionar personagem.");
                    }
                });
            });
        }

        /* -------------------------------------------------------------
        🧩 DESELECIONAR PERSONAGEM (AJAX)
        ------------------------------------------------------------- */
        function attachDeselectEvents() {
            $(".deselect-btn").off("click").on("click", function () {

                $.ajax({
                    url: "{{ route('character.deselect') }}",
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    success: function (data) {
                        if (data.success) location.reload();
                    },
                    error: function () {
                        alert("Erro ao deselecionar personagem.");
                    }
                });
            });
        }

        /* -------------------------------------------------------------
        🚪 LISTAGEM DE SALAS (AJAX)
        ------------------------------------------------------------- */
        const $salasContainer = $("#salas-container");

        function loadSalas() {
            const userId = "{{ session('user_id') }}";
            const userRole = "{{ session('user_role') }}";

            $salasContainer.html("<p class='text-light'>Carregando salas...</p>");

            // Função auxiliar para buscar as salas (por tipo)
            function fetchSalas(url) {
                return $.ajax({ url, method: "GET" })
                    .then(response => response.data || [])
                    .catch(() => []);
            }

            // Decide quais rotas chamar
            const rotas = [];

            if (userRole === "PLAYER") {
                rotas.push(`/salas/jogador/${userId}`);
            } else if (userRole === "MESTRE") {
                // busca tanto as que ele criou quanto as que participa
                rotas.push(`/salas/mestre/${userId}`, `/salas/jogador/${userId}`);
            } else {
                // fallback se for outro tipo ou indefinido
                rotas.push(`/salas/jogador/${userId}`);
            }

            // Faz todas as requisições e junta os resultados
            Promise.all(rotas.map(fetchSalas)).then(results => {
                const salas = results.flat(); // junta as listas
                $salasContainer.empty();

                if (salas.length > 0) {
                    const $list = $("<ul class='list-group'></ul>");
                    salas.forEach((sala) => {
                        $list.append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fa-solid fa-door-open me-2 text-primary"></i>
                                    <a href="/salas/${sala.id}" class="text-decoration-none">${sala.nome}</a>
                                </span>
                                <div class="btn-group">
                                    <a href="/salas/${sala.id}/edit" class="btn btn-sm btn-outline-warning">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${sala.id}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success btn-invite" data-id="${sala.id}">
                                        <i class="fa-solid fa-user-plus"></i>
                                    </button>
                                </div>
                            </li>
                        `);
                    });
                    $salasContainer.append($list);
                    attachSalaEvents();
                } else {
                    $salasContainer.html(`
                        <div class="alert alert-info">
                            <i class="fa-solid fa-circle-exclamation"></i> Não há salas!
                        </div>
                    `);
                }
            }).catch(() => {
                $salasContainer.html("<p class='text-danger'>Erro ao carregar salas.</p>");
            });
        }

        /* -------------------------------------------------------------
        🗑️ EVENTOS DAS SALAS (excluir / convidar)
        ------------------------------------------------------------- */
        function attachSalaEvents() {
            $(".btn-delete").off("click").on("click", function () {
                const id = $(this).data("id");
                if (!confirm("Tem certeza que deseja excluir esta sala?")) return;

                $.ajax({
                    url: `/salas/${id}`,
                    method: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: loadSalas,
                    error: function () {
                        alert("Erro ao excluir sala.");
                    }
                });
            });

            $(".btn-invite").off("click").on("click", function () {
                const id = $(this).data("id");
                showModal("Convite", "Aqui você pode convidar usuários para a sala " + id);
            });
        }

        /* -------------------------------------------------------------
        🚀 INICIALIZAÇÃO
        ------------------------------------------------------------- */
        loadPersonagens();
        loadSalas();
    });
</script>
{{-- Script de instalação PWA --}}
<script>
    let deferredPrompt;
    const installCard = document.getElementById('installCard');
    const installBtn = document.getElementById('installBtn');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        installCard.style.display = 'block';
        installCard.classList.add('show-card'); // animação de entrada
    });

    installBtn.addEventListener('click', async () => {
        installCard.style.display = 'none';
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log(outcome === 'accepted' ? 'PWA instalado!' : 'Instalação cancelada.');
        deferredPrompt = null;
    });
</script>
{{-- Estilos animados --}}
<style>
    @keyframes glow {
        0%, 100% { box-shadow: 0 0 10px 2px rgba(255, 215, 0, 0.6); }
        50% { box-shadow: 0 0 20px 6px rgba(255, 215, 0, 1); }
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .glow-btn {
        animation: glow 1.8s infinite ease-in-out;
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    .fade-in-card.show-card {
        animation: fadeInScale 0.6s ease forwards;
    }
</style>
@endsection
