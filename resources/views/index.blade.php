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
                                <button class="btn btn-outline-danger btn-delete-character">
                                    <i class="fa-solid fa-trash me-1"></i> Excluir Personagem
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
                                    <a href="{{ route('salas.create') }}" class="btn btn-outline-success shadow-sm">
                                        <i class="fa-solid fa-user-group me-1"></i> Criar sala
                                    </a>
                                    <button class="btn btn-outline-danger x-4 py-2 shadow-sm btn-delete-sala">
                                        <i class="fa-solid fa-trash"></i> Excluir sala
                                    </button>
                                @else
                                    <button class="btn btn-outline-success px-4 py-2 shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalSalabyCode">
                                        <i class="fa-solid fa-door-open me-2"></i> Entrar em Sala
                                    </button>
                                    <button class="btn btn-outline-danger x-4 py-2 shadow-sm btn-exit-sala">
                                        <i class="fa-solid fa-door-open"></i> Sair da sala
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
        <div class="container text-white text-center py-5 d-flex flex-column align-items-center">

            {{-- HERO --}}
            <div class="mb-5">
                <img
                    src="{{ asset('assets/images/forgeicon.png') }}"
                    alt="ForgeAction Logo"
                    class="hero-logo mb-3"
                >

                <h1 class="forge-title mb-2">ForgeAction</h1>

                <p class="forge-subtitle lead">
                    Prepare-se para a aventura épica!
                </p>
            </div>


            {{-- GRID DE CARDS --}}
            <div class="home-cards w-100">

                {{-- Login --}}
                <div class="card bg-dark border-secondary shadow text-white text-center fade-in-card">
                    <div class="card-body">
                        <h5 class="card-title">Já tem login?</h5>
                        <p class="card-text">Acesse sua conta e continue a aventura.</p>

                        <a href="{{ route('login') }}" class="btn btn-primary w-100">
                            Login
                        </a>
                    </div>
                </div>

                {{-- Cadastro --}}
                <div class="card bg-dark border-success shadow text-white text-center fade-in-card">
                    <div class="card-body">
                        <h5 class="card-title">Ainda não é cadastrado?</h5>
                        <p class="card-text">Crie sua conta e embarque nessa jornada.</p>

                        <a href="{{ route('register') }}" class="btn btn-success w-100">
                            Cadastro
                        </a>
                    </div>
                </div>

                {{-- Sobre --}}
                <div class="card bg-dark border-info shadow text-white text-center fade-in-card">
                    <div class="card-body">
                        <h5 class="card-title">Sobre</h5>
                        <p class="card-text">Saiba mais sobre o ForgeAction.</p>

                        <a href="{{ route('about') }}" class="btn btn-info w-100">
                            Sobre
                        </a>
                    </div>
                </div>

                {{-- Instalar App --}}
                <div id="installCard"
                    class="card bg-dark border-warning shadow text-white text-center fade-in-card"
                    style="display:none;">

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

                    if (personagens.length === 0) {
                        $characterList.html(`
                            <div class="text-center text-light py-3 bg-dark rounded border-light shadow">
                                <i class="fa-solid fa-circle-exclamation"></i> Nenhum personagem encontrado.
                            </div>
                        `);
                        return;
                    }

                    const racas = {
                        DRACONATO:"Draconato", TIEFLING:"Tiefling", HALFLING:"Halfling",
                        ANAO:"Anão", HUMANO:"Humano", ELFO:"Elfo", ORC:"Orc",
                        BRUTE_MEIO_ORC_HUMANO:"Brute (meio-orc + humano)",
                        BRUTE_MEIO_ORC_ELFO:"Brute (meio-orc + elfo)",
                        TARNISHED_ELFO_HUMANO:"Tarnished (elfo + humano)",
                        TARNISHED_ELFO_TIEFLING:"Tarnished (elfo + tiefling)"
                    };

                    const classes = {
                        ATIRADOR:"Atirador", CACADOR:"Caçador", GUERREIRO:"Guerreiro",
                        PALADINO:"Paladino", ESPADACHIM:"Espadachim", ASSASSINO:"Assassino",
                        LADRAO:"Ladrão", FEITICEIRO:"Feiticeiro", BRUXO:"Bruxo", MAGO:"Mago",
                        CLERIGO:"Clérigo", MONGE:"Monge", XAMA:"Xamã", DRUIDA:"Druida",
                        ARTIFICE:"Artífice", BARDO:"Bardo"
                    };

                    personagens.forEach(p => {
                        $characterList.append(`
                            <div class="personagem-card bg-dark text-white p-3 mb-2 rounded-3 border border-secondary"
                                data-id="${p.id}">

                                <div class="d-flex justify-content-between align-items-center"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse-${p.id}"
                                    aria-expanded="false"
                                    aria-controls="collapse-${p.id}"
                                    style="cursor: pointer;">

                                    <div>
                                        <strong class="fs-5">${p.nome}</strong><br>
                                        <small>${racas[p.raca] ?? p.raca} | ${classes[p.classe] ?? p.classe}</small>
                                    </div>

                                    <i class="fa-solid fa-chevron-down transition"></i>
                                </div>

                                <div id="collapse-${p.id}" class="collapse mt-2">
                                    <div class="bg-secondary bg-opacity-25 rounded p-2">
                                        <div class="row g-2">
                                            <div class="col-6"><small><strong><i class="fa-solid fa-signal"></i> Nível:</strong> ${p.level}</small></div>

                                            <div class="col-6"><small><strong><i class="fa-solid fa-dumbbell"></i> Força:</strong> ${p.forca}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-bolt"></i> Agilidade:</strong> ${p.agilidade}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-brain"></i> Inteligência:</strong> ${p.inteligencia}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-hand"></i> Destreza:</strong> ${p.destreza}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-shield-heart"></i> Vitalidade:</strong> ${p.vitalidade}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-eye"></i> Percepção:</strong> ${p.percepcao}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-book"></i> Sabedoria:</strong> ${p.sabedoria}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-comments"></i> Carisma:</strong> ${p.carisma}</small></div>

                                            <div class="col-6"><small><strong><i class="fa-solid fa-heart"></i> Vida:</strong> ${p.vida}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-droplet"></i> Mana:</strong> ${p.mana}</small></div>

                                            <div class="col-6"><small><strong><i class="fa-solid fa-forward"></i> Iniciativa:</strong> ${p.iniciativa}</small></div>

                                            <div class="col-6"><small><strong><i class="fa-solid fa-wand-magic-sparkles"></i> Atk Mágico:</strong> ${p.ataqueMagico}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-hand-fist"></i> Atk Corpo:</strong> ${p.ataqueFisicoCorpo}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-bullseye"></i> Atk Distância:</strong> ${p.ataqueFisicoDistancia}</small></div>

                                            <div class="col-6"><small><strong><i class="fa-solid fa-shield-halved"></i> Defesa:</strong> ${p.defesaPersonagem}</small></div>
                                            <div class="col-6"><small><strong><i class="fa-solid fa-feather"></i> Esquiva:</strong> ${p.esquivaPersonagem}</small></div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        `);
                    });
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

            const rotas = [];
            if (userRole === "PLAYER") rotas.push(`/api/salas/jogador/${userId}`);
            if (userRole === "MASTER") rotas.push(`/api/salas/mestre/${userId}`);

            Promise.all(
                rotas.map(url =>
                    $.ajax({ url })
                        .then(r => Array.isArray(r) ? r : (r.data || []))
                        .catch(() => [])
                )
            )
            .then(results => {
                const salas = results.flat().filter(
                    (s, i, arr) => arr.findIndex(x => x.id === s.id) === i
                );

                $salasList.empty();

                salas.forEach(sala => {
                    const isMestre = sala.mestre == userId;

                    const botoes = isMestre
                        ? `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-success btn-invite" data-id="${sala.id}">
                                    <i class="fa-solid fa-user-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary btn-copy" data-code="${sala.codigo}">
                                    <i class="fa-solid fa-clipboard"></i>
                                </button>
                            </div>
                        `
                        : ` `;

                    $salasList.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center"
                            data-id="${sala.id}">
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
        🗡️  EXCLUIR PERSONAGEM
        ------------------------------------------------------------- */
        let deleteMode = false;
        let selectedCharacterId = null;

        $(document).on('click', '.btn-delete-character', function () {
            deleteMode = true;
            showToast('Clique no personagem que deseja excluir.', 'info');
        });

        $(document).on('click', '.personagem-card', function () {
            if (!deleteMode) return;

            $('.personagem-card').removeClass('border-danger border-2');
            $(this).addClass('border-danger border-2');

            selectedCharacterId = $(this).data('id');

            showConfirm('Tem certeza que deseja excluir este personagem?', () => {
                $.ajax({
                    url: `/personagem/${selectedCharacterId}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: () => {
                        $(`.personagem-card[data-id="${selectedCharacterId}"]`)
                            .fadeOut(300, function () { $(this).remove(); });

                        deleteMode = false;
                        selectedCharacterId = null;
                    },
                    error: () => {
                        showAlert('Erro ao excluir personagem.');
                        deleteMode = false;
                    }
                });
            });
        });

        /* -------------------------------------------------------------
        🏰  EXCLUIR OU SAIR DE SALA
        ------------------------------------------------------------- */
        let deleteModeSala = false;
        let exitMode = false;
        let selectedSalaId = null;

        $(document).on('click', '.btn-delete-sala', function () {
            deleteModeSala = true;
            exitMode = false;
            showToast('Clique na sala que deseja deletar.', 'info');
        });

        $(document).on('click', '.btn-exit-sala', function () {
            exitMode = true;
            deleteModeSala = false;
            showToast('Clique na sala que deseja sair.', 'info');
        });

        $(document).on('click', '#salasList li', function () {
            if (!deleteModeSala && !exitMode) return;

            $('#salasList li').removeClass('border-danger border-2');
            $(this).addClass('border-danger border-2');

            selectedSalaId = $(this).data('id');

            /* --- Deletar sala --- */
            if (deleteModeSala) {
                showConfirm('Tem certeza que deseja deletar esta sala?', () => {
                    $.ajax({
                        url: `/api/salas/${selectedSalaId}`,
                        type: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: () => {
                            $(`#salasList li[data-id="${selectedSalaId}"]`).fadeOut(300, function () {
                                $(this).remove();
                            });
                            deleteModeSala = false;
                            selectedSalaId = null;
                        },
                        error: () => {
                            showAlert('Erro ao deletar a sala.');
                            deleteModeSala = false;
                        }
                    });
                });
                return;
            }

            /* --- Sair da sala --- */
            if (exitMode) {
                const userId = "{{ session('user_id') }}";

                showConfirm('Tem certeza que deseja sair desta sala?', function () {
                    $.ajax({
                        url: `/api/salas/personagens/listar/${selectedSalaId}`,
                        type: 'GET',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: personagens => {
                            const personagem = personagens.find(p => p.usuarioId == userId);
                            if (!personagem) {
                                showToast('Seu personagem não foi encontrado nesta sala.');
                                exitMode = false;
                                return;
                            }

                            const personagemId = personagem.personagemId;

                            $.ajax({
                                url: `/api/salas/personagens/remover/${selectedSalaId}/${personagemId}`,
                                type: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                success: res => {
                                    showToast(res.message || 'Você saiu da sala.');
                                    $(`#salasList li[data-id="${selectedSalaId}"]`).fadeOut(300, function () {
                                        $(this).remove();
                                    });

                                    exitMode = false;
                                    selectedSalaId = null;
                                    loadSalas();
                                },
                                error: xhr => {
                                    showAlert(xhr.responseJSON?.message || 'Erro ao sair da sala.');
                                    exitMode = false;
                                }
                            });
                        },
                        error: () => {
                            showAlert('Erro ao carregar os personagens da sala.');
                            exitMode = false;
                        }
                    });
                });
            }
        });

        /* -------------------------------------------------------------
        🚀 INICIALIZAÇÃO
        ------------------------------------------------------------- */
        loadPersonagens();
        loadSalas();
    });

    /* -------------------------------------------------------------
    🔽 ÍCONES DO COLLAPSE
    ------------------------------------------------------------- */
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(toggle => {
            const target = document.querySelector(toggle.getAttribute("data-bs-target"));
            const icon = toggle.querySelector("i");

            if (!target || !icon) return;

            target.addEventListener("shown.bs.collapse", () => {
                icon.classList.replace("fa-chevron-down", "fa-chevron-up");
            });

            target.addEventListener("hidden.bs.collapse", () => {
                icon.classList.replace("fa-chevron-up", "fa-chevron-down");
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
<script src="{{ asset('js/room/general/invite.js') }}"></script>
<script src="{{ asset('js/room/general/exit.js') }}"></script>
<script src="{{ asset('js/room/general/delete.js') }}"></script>
<script src="{{ asset('js/room/general/enterCode.js') }}"></script>

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
