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
        <div class="container py-5">
            <div class="d-flex flex-wrap justify-content-center align-items-start gap-5">

                <!-- Personagens -->
                <div class="flex-fill" style="min-width: 350px; max-width: 450px;">
                    <div class="card bg-dark text-white shadow-lg border-0">
                        <div class="card-body rounded-3 p-4">
                            <h3 class="fw-bold text-center mb-4">
                                <i class="fa-solid fa-users me-2"></i> Personagens
                            </h3>

                            {{-- Busca --}}
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-dark text-light border-secondary">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" id="searchCharacter"
                                    class="form-control text-white border-secondary"
                                    placeholder="Pesquisar por nome, raça ou classe...">
                            </div>

                            {{-- Lista de personagens --}}
                            <ul id="characterList"
                                class="list-group list-group-flush bg-dark"
                                style="max-height: 400px; overflow-y: auto;">
                                <li class="list-group-item bg-dark text-white text-center" id="loadingCharacters">
                                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Carregando personagens...
                                </li>
                            </ul>

                            {{-- Botões --}}
                            <div class="d-grid gap-2 mt-4">
                                <a href="{{ route('registerPerson') }}" class="btn btn-outline-light">
                                    <i class="fa-solid fa-user-plus me-1"></i> Criar personagem
                                </a>
                                <button class="btn btn-outline-danger">
                                    <i class="fa-solid fa-trash me-1"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Espaço central livre -->
                <div class="flex-fill d-none d-md-block" style="max-width: 150px;"></div>

                <!-- Salas -->
                <div class="flex-fill" style="min-width: 350px; max-width: 450px;">
                    <div class="card bg-dark text-white shadow-lg border-0">
                        <div class="card-body rounded-3 p-4">
                            <h3 class="fw-bold text-center mb-4">
                                <i class="fa-solid fa-door-open me-2"></i> Salas
                            </h3>

                            {{-- Lista de salas --}}
                            <div id="salas-container">
                                <div class="d-flex align-items-center justify-content-center gap-2 bg-dark text-light fw-bold rounded-3 p-3 shadow-sm">
                                    <li class="list-group-item bg-dark text-white text-center" id="loadingRoom">
                                        <i class="fa-solid fa-spinner fa-spin me-2"></i> Carregando salas...
                                    </li>
                                </div>
                            </div>

                            {{-- Botões --}}
                            <div class="d-grid gap-2 mt-3">
                                <a href="{{ route('salas.index') }}" class="btn btn-outline-success">
                                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Entrar sala
                                </a>
                                    @if (session('user_role') === 'MASTER')
                                    <a href="{{ route('salas.create') }}" class="btn btn-outline-primary">
                                        <i class="fa-solid fa-user-group me-1"></i> Criar sala
                                    </a>
                                    <a href="{{ route('dashboard') }}" class="btn btn-outline-danger">
                                        <i class="fa-solid fa-trash me-1"></i> Remover sala
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @else
        {{-- Bloco dos cards de login, cadastro, sobre e download --}}
        <div class="container text-white d-flex flex-column align-items-center justify-content-center py-5">
            <div class="text-center mb-5 bg-dark p-4 rounded-3 shadow-lg">
                <img src="{{ asset('assets/images/forgeicon.png') }}" alt="ForgeAction Logo"
                    class="logo-center mb-3" style="max-width:150px;">
                <h1>ForgeAction</h1>
                <p class="lead">Prepare-se para a aventura épica!</p>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-4 w-100" style="max-width: 900px;">
                <!-- Card Login -->
                <div class="card bg-dark border-secondary shadow text-white text-center flex-fill"
                    style="min-width: 260px; max-width: 300px;">
                    <div class="card-body">
                        <h5 class="card-title">Já tem login?</h5>
                        <p class="card-text">Acesse sua conta e continue a aventura.</p>
                        <a href="{{ route('login') }}" class="btn btn-primary w-100">Login</a>
                    </div>
                </div>

                <!-- Card Cadastro -->
                <div class="card bg-dark border-success shadow text-white text-center flex-fill"
                    style="min-width: 260px; max-width: 300px;">
                    <div class="card-body">
                        <h5 class="card-title">Ainda não é cadastrado?</h5>
                        <p class="card-text">Crie sua conta e embarque nessa jornada.</p>
                        <a href="{{ route('register') }}" class="btn btn-success w-100">Cadastro</a>
                    </div>
                </div>

                <!-- Card Sobre -->
                <div class="card bg-dark border-info shadow text-white text-center flex-fill"
                    style="min-width: 260px; max-width: 300px;">
                    <div class="card-body">
                        <h5 class="card-title">Sobre</h5>
                        <p class="card-text">Saiba mais sobre o ForgeAction.</p>
                        <a href="{{ route('about') }}" class="btn btn-info w-100">Sobre</a>
                    </div>
                </div>

                <!-- Card Baixar App -->
                <div id="installCard"
                    class="card bg-dark border-warning shadow text-white text-center flex-fill fade-in-card"
                    style="min-width: 260px; max-width: 300px; display:none;">
                    <div class="card-body">
                        <h5 class="card-title">Instale o App</h5>
                        <p class="card-text">Use o ForgeAction direto no seu dispositivo!</p>
                        <button id="installBtn" class="btn btn-warning w-100">
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
@include('partials/invite')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/utils/loading.js') }}"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>

<script>

    /* -------------------------------------------------------------
    📢 FUNÇÕES GERAIS E INICIALIZAÇÃO
    ------------------------------------------------------------- */
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
                                <div class="personagem-card bg-dark text-white p-3 mb-2 rounded-3 d-flex justify-content-between align-items-center"
                                    data-nome="${p.nome.toLowerCase()}"
                                    data-raca="${p.raca.toLowerCase()}"
                                    data-classe="${p.classe.toLowerCase()}">
                                    <div>
                                        <strong class="fs-5">${p.nome}</strong><br>
                                        <small>
                                            Raça: ${racas[p.raca] ?? p.raca} |
                                            Classe: ${classes[p.classe] ?? p.classe}
                                        </small>
                                    </div>
                                </div>
                            `);
                        });

                        attachCharacterEvents(); // reativa os botões
                    } else {
                        $characterList.html(`
                            <div class="text-center text-light py-3 bg-dark rounded border-light shadow">
                                <i class="fa-solid fa-circle-exclamation"></i> Nenhum personagem encontrado.
                            </div>
                        `);
                    }
                },
                error: function () {
                    $characterList.html(`
                        <div class="text-center text-danger py-3 bg-dark rounded border-light shadow">
                            <i class="fa-solid fa-triangle-exclamation"></i> Erro ao carregar personagens.
                        </div>
                    `);
                }
            });
        }

        /* -------------------------------------------------------------
        🚪 LISTAGEM DE SALAS (AJAX)
        ------------------------------------------------------------- */
        const $salasContainer = $("#salas-container");

        function loadSalas() {
            const userId = "{{ session('user_id') }}";
            const userRole = "{{ session('user_role') }}";

            $salasContainer.html("<li class='list-group-item bg-dark text-white text-center' id='loadingRoom'> <i class='fa-solid fa-spinner fa-spin me-2'></i> Carregando salas... </li>");

            // 🔹 Função auxiliar para buscar as salas (por tipo)
            function fetchSalas(url) {
                return $.ajax({ url, method: "GET" })
                    .then(response => Array.isArray(response) ? response : (response.data || []))
                    .catch(() => []);
            }


            // 🔹 Define as rotas que serão chamadas
            const rotas = [];

            if (userRole === "PLAYER") {
                // Jogador → apenas as salas onde ele participa
                rotas.push(`/api/salas/jogador/${userId}`);
            }
            else if (userRole === "MASTER") {
                // Mestre → salas que ele criou + salas que participa como jogador
                rotas.push(`/api/salas/mestre/${userId}`);
                rotas.push(`/api/salas/jogador/${userId}`);
            }

            // 🔹 Faz todas as requisições e junta os resultados
            Promise.all(rotas.map(fetchSalas)).then(results => {
                // Junta e remove duplicadas (por ID)
                const salas = results.flat().reduce((acc, sala) => {
                    if (!acc.find(s => s.id === sala.id)) acc.push(sala);
                    return acc;
                }, []);

                $salasContainer.empty();

                if (salas.length > 0) {
                    const $list = $("<ul class='list-group'></ul>");

                    salas.forEach((sala) => {
                        const isMestre = sala.mestre == userId;

                        let botoes = '';

                        if (isMestre) {
                            botoes = `
                                <div class="btn-group">
                                    <a href="#" class="btn btn-sm btn-outline-warning">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${sala.id}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success btn-invite" data-id="${sala.id}">
                                        <i class="fa-solid fa-user-plus"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            botoes = `
                                <button class="btn btn-sm btn-outline-danger btn-leave" data-id="${sala.id}">
                                    <i class="fa-solid fa-door-open"></i> Sair
                                </button>
                            `;
                        }

                        $list.append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fa-solid fa-door-open me-2 text-primary"></i>
                                    <a href="/salas/${sala.id}" class="text-decoration-none">${sala.nome}</a>
                                </span>
                                ${botoes}
                            </li>
                        `);
                    });

                    $salasContainer.append($list);
                    // attachSalaEvents();
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
        🚀 INICIALIZAÇÃO
        ------------------------------------------------------------- */
        loadPersonagens();
        loadSalas();
    });

</script>
{{-- Passando infos do Blade para o script... --}}
<script>
    window.userId = "{{ session('user_id') }}";
    window.csrfToken = "{{ csrf_token() }}";
    const routeSalasIndex = "{{ route('salas.index') }}";
</script>
<script src="{{ asset('js/room/invite.js') }}"></script>
<script src="{{ asset('js/room/exit.js') }}"></script>
<script src="{{ asset('js/room/delete.js') }}"></script>

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

@endsection
