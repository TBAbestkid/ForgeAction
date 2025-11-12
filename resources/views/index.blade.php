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

                            {{-- Lista de personagens --}}
                            <ul id="characterList"
                                class="list-group list-group-flush bg-dark scroll-invisible"
                                style="max-height: 400px; overflow-y: auto;">
                                <li class="list-group-item bg-dark text-white text-center" id="loadingCharacters">
                                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Carregando personagens...
                                </li>
                            </ul>

                            {{-- Botões --}}
                            <div class="d-grid gap-2 mt-4">
                                <a href="{{ route('registerPerson') }}" class="btn btn-outline-success">
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
                            <ul id="salasList"
                                class="list-group list-group-flush bg-dark scroll-invisible"
                                style="max-height: 400px; overflow-y: auto;">
                                <li class="list-group-item bg-dark text-white text-center" id="loadingRoom">
                                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Carregando salas...
                                </li>
                            </ul>

                            {{-- Botões --}}
                            <div class="d-grid gap-2 mt-4">
                                @if (session('user_role') === 'MASTER')
                                    <a href="{{ route('salas.create') }}" class="btn btn-outline-success">
                                        <i class="fa-solid fa-user-group me-1"></i> Criar sala
                                    </a>
                                @else
                                    <button class="btn btn-outline-success px-4 py-2 shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalSalabyCode">
                                        <i class="fa-solid fa-door-open me-2"></i> Entrar em Sala
                                    </button>
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
            <div class="card text-center mb-5 bg-dark p-4 rounded-3 shadow-lg">
                <img src="{{ asset('assets/images/forgeicon.png') }}" alt="ForgeAction Logo"
                    class="logo-center mb-3 text-center justify-content-center" style="max-width:150px;">
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
@include('partials/entercode')
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
                                <div class="personagem-card bg-dark text-white p-3 mb-2 rounded-3 border border-secondary">
                                    <div class="d-flex justify-content-between align-items-center"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse-${p.id}"
                                        aria-expanded="false"
                                        aria-controls="collapse-${p.id}"
                                        style="cursor: pointer;">

                                        <div>
                                            <strong class="fs-5">${p.nome}</strong><br>
                                            <small>
                                                ${racas[p.raca] ?? p.raca} |
                                                ${classes[p.classe] ?? p.classe}
                                            </small>
                                        </div>

                                        <i class="fa-solid fa-chevron-down transition"></i>
                                    </div>

                                    <div id="collapse-${p.id}" class="collapse mt-2">
                                        <div class="bg-secondary bg-opacity-25 rounded p-2">
                                            <div class="row g-2">
                                                <div class="col-6"><small><strong>Nível:</strong> ${p.level}</small></div>
                                                <div class="col-6"><small><strong>Força:</strong> ${p.forca}</small></div>
                                                <div class="col-6"><small><strong>Agilidade:</strong> ${p.agilidade}</small></div>
                                                <div class="col-6"><small><strong>Inteligência:</strong> ${p.inteligencia}</small></div>
                                                <div class="col-6"><small><strong>Destreza:</strong> ${p.destreza}</small></div>
                                                <div class="col-6"><small><strong>Vitalidade:</strong> ${p.vitalidade}</small></div>
                                                <div class="col-6"><small><strong>Percepção:</strong> ${p.percepcao}</small></div>
                                                <div class="col-6"><small><strong>Sabedoria:</strong> ${p.sabedoria}</small></div>
                                                <div class="col-6"><small><strong>Carisma:</strong> ${p.carisma}</small></div>
                                                <div class="col-6"><small><strong>Vida:</strong> ${p.vida}</small></div>
                                                <div class="col-6"><small><strong>Mana:</strong> ${p.mana}</small></div>
                                                <div class="col-6"><small><strong>Iniciativa:</strong> ${p.iniciativa}</small></div>
                                                <div class="col-6"><small><strong>Atk Mágico:</strong> ${p.ataqueMagico}</small></div>
                                                <div class="col-6"><small><strong>Atk Corpo:</strong> ${p.ataqueFisicoCorpo}</small></div>
                                                <div class="col-6"><small><strong>Atk Distância:</strong> ${p.ataqueFisicoDistancia}</small></div>
                                                <div class="col-6"><small><strong>Defesa:</strong> ${p.defesaPersonagem}</small></div>
                                                <div class="col-6"><small><strong>Esquiva:</strong> ${p.esquivaPersonagem}</small></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                        });
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
        const $salasList = $("#salasList");

        function loadSalas() {
            const userId = "{{ session('user_id') }}";
            const userRole = "{{ session('user_role') }}";

            // 🔹 Define as rotas
            const rotas = [];
            if (userRole === "PLAYER") {
                rotas.push(`/api/salas/jogador/${userId}`);
            } else if (userRole === "MASTER") {
                rotas.push(`/api/salas/mestre/${userId}`);
            }

            // 🔹 Faz todas as requisições
            Promise.all(rotas.map(url =>
                $.ajax({ url, method: "GET" })
                    .then(r => Array.isArray(r) ? r : (r.data || []))
                    .catch(() => [])
            ))
            .then(results => {
                const salas = results.flat().filter(
                    (s, i, arr) => arr.findIndex(x => x.id === s.id) === i
                );

                // Limpa o loading
                $salasList.empty();

                // 🔹 Monta cada sala
                salas.forEach(sala => {
                    const isMestre = sala.mestre == userId;

                    const botoes = isMestre
                        ? `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${sala.id}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success btn-invite" data-id="${sala.id}">
                                    <i class="fa-solid fa-user-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary btn-copy" data-code="${sala.codigo}" title="Copiar código">
                                    <i class="fa-solid fa-clipboard"></i>
                                </button>
                            </div>
                        `
                        : `
                            <button class="btn btn-sm btn-outline-danger btn-leave" data-id="${sala.id}">
                                <i class="fa-solid fa-door-open"></i> Sair
                            </button>
                        `;

                    $salasList.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fa-solid fa-door-open me-2 text-primary"></i>
                                <a href="/salas/${sala.id}" class="text-decoration-none">${sala.nome}</a>
                            </span>
                            ${botoes}
                        </li>
                    `);
                });
            })
            .catch(() => {
                $salasList.html(`
                    <li class="list-group-item text-danger text-center">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Erro ao carregar salas.
                    </li>
                `);
            });
        }

        /* -------------------------------------------------------------
        🚀 INICIALIZAÇÃO
        ------------------------------------------------------------- */
        loadPersonagens();
        loadSalas();
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Seleciona todos os collapses da página
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(toggle => {
            const targetId = toggle.getAttribute('data-bs-target');
            const target = document.querySelector(targetId);
            const icon = toggle.querySelector('i');

            if (!target || !icon) return;

            // Quando o collapse abrir
            target.addEventListener('shown.bs.collapse', () => {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            });

            // Quando o collapse fechar
            target.addEventListener('hidden.bs.collapse', () => {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            });
        });
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
<script src="{{ asset('js/room/enter-code.js') }}"></script>

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
