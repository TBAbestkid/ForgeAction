@extends('partials/appSala')
@section('title', "{$sala['nome']} - ForgeAction")

@section('content')
{{-- Manter tudo na tela visualmente, aqui dentro --}}
<div id="roomContainer" class="">
    {{-- Background da Sala --}}
    <div id="roomBackground"
        style="background-image: url('{{ $sala['urlBackground'] ? $sala['urlBackground'] : asset('assets/images/forge.png') }}');">
    </div>

    {{-- Configurações de Sala, para mestre e player --}}
    <div class="position-absolute top-0 end-0 d-flex align-items-center gap-2 m-3">

        <div class="dropdown">
            <button class="btn btn-light text-dark dropdown-toggle" id="optionsMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu">
                {{-- Tanto para mestre quanto para player --}}
                <li>
                    <button class="dropdown-item" type="button" id="fullscreen" onclick="entrarEmFullscreen()">
                        <i class="fa-solid fa-expand"></i>
                        Tela Cheia
                    </button>
                </li>
                <li>
                    <a href="#" class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMembers">
                        <i class="fa-solid fa-users"></i>
                        Membros
                    </a>
                </li>
                <li><hr class="dropdown-divider text-white"></li>
                @if($isDono)
                    <li>
                        <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#configModal">
                            <i class="fa-solid fa-user-plus"></i>
                            Convidar Membros
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#copiarcodigoSala">
                            <i class="fa-solid fa-copy"></i>
                            Copiar Código da Sala
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('home') }}" class="dropdown-item">
                        <i class="fa-solid fa-door-open"></i>
                        Sair da Sala
                    </a>
                </li>
            </ul>
        </div>
        @if (!$isDono)
            <button class="btn btn-light text-dark ms-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFicha" aria-controls="offcanvasFicha">
                <i class="fa-solid fa-scroll me-2"></i>
                Ficha
            </button>
        @else
            <button class="btn btn-light text-dark ms-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFichas" aria-controls="offcanvasFichas">
                <i class="fa-solid fa-scroll me-2"></i>
                Fichas
            </button>
        @endif

    </div>

    {{-- Chat --}}
    <div class="position-absolute bottom-0 start-0 m-3">

        <!-- Botão -->
        <button class="btn btn-dark mb-2" data-bs-toggle="collapse" data-bs-target="#chatCollapse">
            <i class="fa-solid fa-comments"></i> Chat
        </button>

        <!-- Collapse -->
        <div class="collapse" id="chatCollapse">
            <div class="chat-box rounded-4 shadow p-3" style="width: 400px; max-width: 90vw; height: 500px; background-color: rgba(0, 0, 0, 0.8);">

                <div id="chat-messages" class="mb-3">
                    {{-- Mensagens Exemplo  --}}
                    {{-- <div class="p-2 rounded bg-secondary text-light d-flex flex-column mb-2 align-items-start">
                        <small class="d-block fw-bold opacity-75">Jogador</small>
                        <span>Olá, pessoal!</span>
                    </div> --}}
                </div>

                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Digite sua mensagem..." id="chat-input">
                    <button class="btn btn-primary" id="chat-send"> <i class="fa-solid fa-paper-plane"></i></button>
                </div>

            </div>
        </div>

    </div>

    {{-- Botões de Ações Mestre/Player
        Usando de base a ideia de HUID
        Botões de ação de mestre como linha abaixo de área --}}
    <div class="position-fixed bottom-0 start-50 translate-middle-x mb-3 z-3">
        <div class="d-flex gap-3 px-3 py-2 rounded-4 shadow hud-bg">

            @if($isDono)

                <button id="btnIniciarTurno"
                    class="btn btn-success btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                    title="Turno">
                    <i class="fa-solid fa-play"></i>
                </button>

                <button id="btnLancarMestre"
                    class="btn btn-warning btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                    title="Dados" disabled>
                    <i class="fa-solid fa-dice-d20"></i>
                </button>

                <button id="btnPermitirJogadaExtra"
                    class="btn btn-primary btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                    title="Extra" disabled>
                    <i class="fa-solid fa-user-check"></i>
                </button>

                <button id="btnDano"
                    class="btn btn-danger btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                    title="Dano" disabled>
                    <i class="fa-solid fa-burst"></i>
                </button>

                <button id="btnCurar"
                    class="btn btn-success btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                    title="Curar" disabled>
                    <i class="fa-solid fa-heart-pulse"></i>
                </button>

                <button id="btnUpar"
                    class="btn btn-info btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                    title="Upar" disabled>
                    <i class="fa-solid fa-arrow-up"></i>
                </button>
            @else
                {{-- 🎲 Rodar Dado --}}
                <button id="btn-roll"
                    class="btn btn-light btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                    title="Rodar Dado" disabled>
                    <i class="fa-solid fa-dice-d20"></i>
                </button>

                {{-- ⏭️ Pular Turno --}}
                <button id="btn-skip"
                    class="btn btn-warning btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                    title="Pular Turno" disabled>
                    <i class="fa-solid fa-forward"></i>
                </button>
            @endif
        </div>
    </div>

    {{-- Coluna central (DiceBox) --}}
    <div id="dice-container" class="position-fixed top-50 start-50 translate-middle shadow-lg rounded-4 d-flex align-items-center justify-content-center"
        style="width: 60vw; height: 50vh; max-width: 900px; min-height: 300px; z-index: 5; background: rgba(0, 0, 0, 0.25);">

        {{-- 🎲 Área do Dice --}}
        <div id="dice-box"
            class="w-100 h-100 position-absolute top-0 start-0">
        </div>

        {{-- Placeholder --}}
        <div id="dice-placeholder"
            class="text-white text-center position-absolute top-50 start-50 translate-middle">
            🎲 Aguardando início do turno...
        </div>

        {{-- Controles --}}
        <div id="turnControls"
            class="d-none flex-column align-items-center gap-2 position-absolute bottom-0 start-50 translate-middle-x mb-2">

            @if ($isDono)
                <div class="form-check text-white">
                    <input type="checkbox" id="ocultarDados" class="form-check-input">
                    <label for="ocultarDados">Ocultar Dados</label>
                </div>
            @endif

            <div id="diceOptions" class="d-none text-center">
                <button class="btn btn-outline-primary m-1 diceBtn" data-sides="4">D4</button>
                <button class="btn btn-outline-primary m-1 diceBtn" data-sides="6">D6</button>
                <button class="btn btn-outline-primary m-1 diceBtn" data-sides="10">D10</button>
                <button class="btn btn-outline-primary m-1 diceBtn" data-sides="12">D12</button>
                <button class="btn btn-outline-primary m-1 diceBtn" data-sides="20">D20</button>
            </div>
        </div>
         <div id="coluna-personagens" class="d-lg-flex flex-column gap-3 overflow-auto" style="flex: 0 0 25%; padding: 0.5rem; min-width: 160px; max-width: 240px;">
                {{-- Personagens serão inseridos aqui via JS --}}
            </div>
    </div>
</div>


@if ($isDono)
    <!-- Modal para input do valor -->
    <div class="modal fade" id="modalValor" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title">Insira valor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="inputValor">Insira valor:</label>
                    <input type="number" id="inputValor" class="form-control" min="0">
                </div>
                <div class="modal-footer">
                    <button type="button" id="btnConfirmarValor" class="btn btn-danger" data-bs-dismiss="modal">Aplicar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fichas de players para o mestre -->
    <div class="offcanvas offcanvas-end text-light d-flex align-content-start" tabindex="-1" id="offcanvasFichas"
        aria-labelledby="offcanvasFichaLabel" style="background-color: #1c1c1c; max-width: 280px;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasFichaLabel">
                <i class="fa-solid fa-scroll me-2"> Fichas</i>
            </h5>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        </div>
    </div>

@else
    <!-- Ficha do jogador -->
    <div class="offcanvas offcanvas-end text-light" tabindex="-1" id="offcanvasFicha"
        aria-labelledby="offcanvasFichaLabel" style="background-color: #1c1c1c; max-width: 280px;">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasFichaLabel">
                <i class="fa-solid fa-scroll me-2"></i>Ficha de {{ $personagemJogador['nome'] ?? 'Personagem' }}
            </h5>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-3 overflow-auto" style="max-height: calc(100vh - 60px);">
            <div class="row g-2">
                <!-- Header do Personagem -->
                <div class="col-12 mb-3 border-bottom border-secondary pb-2">
                    <div><small><strong><i class="fa-solid fa-user-shield"></i> Raça:</strong> {{ $personagemJogador['raca'] ?? 'N/A' }}</small></div>
                    <div><small><strong><i class="fa-solid fa-wand-magic-sparkles"></i> Classe:</strong> {{ $personagemJogador['classe'] ?? 'N/A' }}</small></div>
                    <div><small><strong><i class="fa-solid fa-signal"></i> Nível:</strong> {{ $personagemJogador['level'] ?? 1 }}</small></div>
                </div>

                <!-- Atributos Principais -->
                <div class="col-12 mb-2">
                    <small class="text-warning"><strong>⚔️ Atributos Principais</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-dumbbell"></i> Força:</strong> {{ $personagemJogador['forca'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-bolt"></i> Agilidade:</strong> {{ $personagemJogador['agilidade'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-brain"></i> Inteligência:</strong> {{ $personagemJogador['inteligencia'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-hand"></i> Destreza:</strong> {{ $personagemJogador['destreza'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-shield-heart"></i> Vitalidade:</strong> {{ $personagemJogador['vitalidade'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-eye"></i> Percepção:</strong> {{ $personagemJogador['percepcao'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-book"></i> Sabedoria:</strong> {{ $personagemJogador['sabedoria'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-comments"></i> Carisma:</strong> {{ $personagemJogador['carisma'] ?? 0 }}</small></div>

                <!-- Recursos de Vida/Mana -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-info"><strong>❤️ Recursos</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-heart"></i> Vida:</strong> {{ $personagemJogador['vida'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-droplet"></i> Mana:</strong> {{ $personagemJogador['mana'] ?? 0 }}</small></div>

                <!-- Iniciativa e Bônus -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-success"><strong>⚡ Ações</strong></small>
                </div>
                <div class="col-12"><small><strong><i class="fa-solid fa-forward"></i> Iniciativa:</strong> {{ $personagemJogador['iniciativa'] ?? 0 }}</small></div>

                <!-- Ataques -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-danger"><strong>⚔️ Ataques</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-wand-magic-sparkles"></i> Atk Mágico:</strong> {{ $personagemJogador['ataqueMagico'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-hand-fist"></i> Atk Corpo:</strong> {{ $personagemJogador['ataqueFisicoCorpo'] ?? 0 }}</small></div>
                <div class="col-12 mt-2"><small><strong><i class="fa-solid fa-bullseye"></i> Atk Distância:</strong> {{ $personagemJogador['ataqueFisicoDistancia'] ?? 0 }}</small></div>

                <!-- Defesa -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-secondary"><strong>🛡️ Defesa</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-shield-halved"></i> Defesa:</strong> {{ $personagemJogador['defesaPersonagem'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-feather"></i> Esquiva:</strong> {{ $personagemJogador['esquivaPersonagem'] ?? 0 }}</small></div>
            </div>
        </div>
    </div>
@endif

<!-- Offcanvas direita (MEMBROS) -->
<div class="offcanvas offcanvas-end text-light" tabindex="-1" id="offcanvasMembers"
    aria-labelledby="offcanvasMembersLabel" style="background-color: #1c1c1c; min-width: 250px;">

    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasMembersLabel">Membros</h5>
        <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">
        <ul id="lista-membros" class="list-group list-group-flush overflow-auto" style="max-height: 500px;">

            {{-- 🔹 Primeiro o Mestre (dono da sala) --}}
            @php
                $mestreId = $sala['mestre'];
            @endphp

            <li class="list-group-item bg-dark text-warning d-flex justify-content-between align-items-center">
                <div>
                    <i class="fa-solid fa-crown text-warning me-2"></i>
                    <strong>{{ $membros[$mestreId] ?? 'Mestre' }}</strong>
                </div>
                <span><span class="members-list-dot offline"></span></span>
            </li>

            {{-- 🔹 Depois os Players (exceto o mestre) --}}
            @foreach($membros as $uid => $login)
                @continue($uid == $mestreId)

                <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center"
                    data-user-id="{{ $uid }}">
                    {{ $login }}
                    <span><span class="members-list-dot offline"></span></span>
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- Aviso de tela cheia --}}
<div id="aviso-fullscreen" class="alert alert-info" style="display: none; position: fixed; top: 10px; left: 50%; transform: translateX(-50%); z-index: 1050; cursor: pointer;">
    <i class="fa-solid fa-info-circle"></i>
    Pressione <strong>F11</strong> ou <strong>Clique aqui</strong> para entrar em tela cheia
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{{-- <script src="{{ asset('js/room/game/turnManager.js') }}"></script> --}}
{{-- <script src="{{ asset('js/room/game/turnState.js') }}"></script> --}}
{{-- <script type="module" src="{{ asset('js/room/game/diceManager.js') }}"></script> --}}
{{-- <script src="{{ asset('js/room/game/gameFlow.js') }}"></script> --}}
{{-- <script src="{{ asset('js/room/game/turnUIManager.js') }}"></script> --}}
{{-- <script src="{{ asset('js/room/game/personagensManager.js') }}"></script> --}}

{{-- Exporta variáveis PHP para JS --}}
<script>
    // const newWsUrl = "/ws";

    // console.log("WebSocket URL:", newWsUrl);

    window.CHAT_CONFIG = {
        userId: {{ session('user_id') ?? 'null' }},
        userLogin: "{{ session('user_login') ?? 'Desconhecido' }}",
        salaId: {{ $sala['id'] }},
        //wsUrl: "{{ env('EXTERNAL_API_URL') }}" + "/ws",
        wsUrl: window.location.origin + "/ws",
        isMestre: {{ $isDono ? 'true' : 'false' }}
    };

    window.csrfToken = "{{ csrf_token() }}";
    const csrfToken = "{{ csrf_token() }}";
    const routeSalasIndex = "{{ route('salas.index') }}";
</script>

{{-- 1. Serviço WebSocket (fundamental) --}}
<!-- Status styles for personagens (inline file) -->
<script src="{{ asset('js/utils/webSocketService.js') }}"></script>
{{-- 2. Gerenciadores da sala --}}
<script src="{{ asset('js/room/general/chatRoom.js') }}"></script>
<script src="{{ asset('js/room/game/roomManager.js') }}"></script>

<script>
    window.onload = function() {
        const aviso = document.getElementById('aviso-fullscreen');
        setTimeout(() => {
            aviso.style.display = 'block';
            aviso.onclick = function() {
                entrarEmFullscreen();
                aviso.style.display = 'none';
            };
        }, 1000);

        // Esconde o aviso automaticamente após 5 segundos
        setTimeout(() => {
            aviso.style.display = 'none';
        }, 5000);
    };

    function entrarEmFullscreen() {
        const elem = document.documentElement;
        const fullscreenButton = document.getElementById('fullscreen');

        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari e Opera */
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) { /* IE/Edge */
            elem.msRequestFullscreen();
        }

        if (fullscreenButton) {
            fullscreenButton.innerHTML = '<i class="fa-solid fa-compress"></i> Minimizar';
            fullscreenButton.onclick = sairDoFullscreen;
        }
    }

    function sairDoFullscreen() {
        const fullscreenButton = document.getElementById('fullscreen'); // Ajustado para o ID correto

        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }

        // Volta o botão ao estado original
        if (fullscreenButton) {
            fullscreenButton.innerHTML = '<i class="fa-solid fa-expand"></i> Tela Cheia';
            fullscreenButton.onclick = entrarEmFullscreen;
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'F11') {
            e.preventDefault(); // impede comportamento padrão
            toggleFullscreen();
        }
    });
</script>
@endsection
