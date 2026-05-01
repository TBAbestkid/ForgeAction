@extends('partials/appSala')
@section('title', "{$sala['nome']} - ForgeAction")

@section('content')
{{-- Manter tudo na tela visualmente, aqui dentro --}}
<div id="roomContainer" class="">
    <style>
        #dice-container {
            position: relative;
            overflow: hidden;
        }

        #dice-box {
            z-index: 2;
            pointer-events: none;
        }

        #dice-placeholder {
            z-index: 1;
            background: rgba(0, 0, 0, 0.55);
            padding: 0.9rem 1.4rem;
            border-radius: 1.5rem;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1), 0 20px 45px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(8px);
            transition: transform 0.25s ease, opacity 0.25s ease;
            pointer-events: none;
        }

        #dice-placeholder::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -14px;
            transform: translateX(-50%);
            width: 180px;
            height: 12px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(0,0,0,0) 70%);
            filter: blur(6px);
            opacity: 0.85;
        }

        .dice-result {
            position: absolute;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.6);
            font-size: 4rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 0 28px rgba(255,255,255,0.9), 0 0 60px rgba(0,0,0,0.45);
            z-index: 3;
            pointer-events: none;
            opacity: 0;
            animation: diceValuePop 1.5s ease-out forwards;
        }

        @keyframes diceValuePop {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.5);
            }
            20% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1.4);
            }
            55% {
                opacity: 1;
                transform: translate(-50%, -55%) scale(1.0);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -70%) scale(0.8);
            }
        }

        @keyframes pulse-vida {
            0% {
                transform: scaleX(1);
            }
            50% {
                transform: scaleX(1.05);
            }
            100% {
                transform: scaleX(1);
            }
        }
    </style>

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
        @endif

    </div>

    {{-- Chat e Logs Desktop (pq pra cell eu não to muito afim) --}}
    <div class="position-absolute bottom-0 start-0 m-3" style="z-index: 100;">

        <!-- Botão com Badge de Notificações -->
        {{-- Meio que não tem problema inserir notificações bombasticas --}}
        <button class="btn btn-dark mb-2 position-relative" data-bs-toggle="collapse" data-bs-target="#chatCollapse">
            <i class="fa-solid fa-comments"></i> Chat
            <span id="chat-notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none; font-size: 0.6rem;">
                <span id="chat-notification-count">0</span>
            </span>
        </button>

        <!-- Collapse -->
        <div class="collapse" id="chatCollapse">
            <div class="chat-container rounded-4 shadow" style="width: 400px; max-width: 90vw; height: 400px; background-color: rgba(0, 0, 0, 0.8); display: flex; flex-direction: column;">

                <!-- Tabs para Chat e Logs -->
                <ul class="nav nav-tabs nav-fill border-0" id="chatTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-light border-0" id="chat-tab" data-bs-toggle="pill" data-bs-target="#chat-content" type="button" role="tab" aria-controls="chat-content" aria-selected="true">
                            <i class="fa-solid fa-comments me-1"></i>Chat
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-light border-0" id="logs-tab" data-bs-toggle="pill" data-bs-target="#logs-content" type="button" role="tab" aria-controls="logs-content" aria-selected="false">
                            <i class="fa-solid fa-list-ul me-1"></i>Logs
                        </button>
                    </li>
                </ul>

                <!-- Conteúdo das Tabs -->
                <div class="tab-content flex-grow-1 overflow-hidden" id="chatTabContent">

                    <!-- Aba Chat -->
                    <div class="tab-pane fade show active d-flex flex-column h-100" id="chat-content" role="tabpanel" aria-labelledby="chat-tab">
                        <div id="chat-messages" class="flex-grow-1 overflow-auto mb-3 p-3" style="min-height: 0;">
                            {{-- Mensagens serão inseridas aqui --}}
                        </div>
                        <div class="input-group p-3 border-top border-secondary">
                            <input type="text" class="form-control" placeholder="Digite sua mensagem..." id="chat-input">
                            <button class="btn btn-primary" id="chat-send">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Aba Logs -->
                    <div class="tab-pane fade d-flex flex-column h-100" id="logs-content" role="tabpanel" aria-labelledby="logs-tab">
                        <div id="system-logs" class="flex-grow-1 overflow-auto p-3" style="min-height: 0;">
                            {{-- Logs do sistema serão inseridos aqui --}}
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Seção principal de jogo com layout flexbox --}}
    <div id="game-section" class="position-fixed top-50 start-50 translate-middle d-flex gap-3 align-items-stretch"
        style="width: 90vw; height: 60vh; max-width: 1200px; z-index: 5; transform: translate(-50%, -50%);">

        {{-- Coluna central (DiceBox) --}}
        <div id="dice-container" class="shadow-lg rounded-4 d-flex align-items-center justify-content-center flex-grow-1"
            style="background: rgba(0, 0, 0, 0.25);">

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
        </div>

        {{-- Coluna direita (Personagens) --}}
        {{-- @if ($isDono) --}}
        <div id="coluna-personagens" class="d-flex flex-column gap-3 overflow-auto rounded-4"
            style="flex: 0 0 240px; max-height: 100%; padding: 0.75rem; background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.1);">
        </div>
        {{-- @endif --}}
    </div>

    {{-- Botões de Ações Mestre/Player (HUD abaixo)
        Usando de base a ideia de HUID
        Botões de ação de mestre como linha abaixo de área --}}
    <div class="position-fixed bottom-0 start-50 translate-middle-x mb-3 z-3">
        <div class="d-flex flex-column gap-2 px-3 py-2 rounded-4 shadow hud-bg" style="align-items: center;">

            @if($isDono)

                <div class="d-flex gap-3">
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
                </div>
            @else
                {{-- 🎲 Rodar Dado e Pular Turno --}}
                <div class="d-flex gap-3">
                    <button id="btn-roll"
                        class="btn btn-light btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                        title="Rodar Dado" disabled>
                        <i class="fa-solid fa-dice-d20"></i>
                    </button>

                    <button id="btn-skip"
                        class="btn btn-warning btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                        title="Pular Turno" disabled>
                        <i class="fa-solid fa-forward"></i>
                    </button>
                </div>

                {{-- ❤️ Barra de Vida do Jogador --}}
                <div style="width: 240px; display: flex; flex-direction: column; gap: 4px;">
                    <small class="text-danger" style="margin: 0;"><i class="fa-solid fa-heart"></i> <strong>Vida</strong></small>
                    <div class="progress" style="height: 26px; font-size: 0.8rem;">
                        <div id="playerHealthBar"
                            class="progress-bar bg-success d-flex justify-content-center align-items-center text-dark fw-bold"
                            role="progressbar"
                            style="width: 100%;"
                            data-personagem-id="{{ $personagemJogador['id'] ?? 0 }}"
                            data-vida-max="{{ $personagemJogador['vida'] ?? 100 }}"
                            data-vida-atual="{{ $personagemJogador['vida'] ?? 100 }}">
                            {{ $personagemJogador['vida'] ?? 100 }}/{{ $personagemJogador['vida'] ?? 100 }}
                        </div>
                    </div>
                </div>
            @endif
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

    <!-- Offcanvas para Ficha de Personagem (genérico, usado pelo mestre) -->
    <div class="offcanvas offcanvas-end text-light" tabindex="-1" id="offcanvasFichaPersonagem"
        aria-labelledby="offcanvasFichaPersonagemLabel" style="background-color: #1c1c1c; max-width: 320px;">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasFichaPersonagemLabel">
                <i class="fa-solid fa-scroll me-2"></i>Ficha
            </h5>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-3 overflow-auto" style="max-height: calc(100vh - 60px);">
            {{-- Conteúdo será preenchido dinamicamente por JavaScript --}}
        </div>
    </div>

@else
    <!-- Ficha do jogador -->
    <div class="offcanvas offcanvas-end text-light" tabindex="-1" id="offcanvasFicha"
        aria-labelledby="offcanvasFichaLabel" style="background-color: #1c1c1c; max-width: 280px;">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title d-flex align-items-center w-100" id="offcanvasFichaLabel">
                <i class="fa-solid fa-scroll me-2"></i>

                <span class="personagem-nome flex-grow-1" title="{{ $personagemJogador['nome'] ?? 'Personagem' }}">
                    Ficha de {{ $personagemJogador['nome'] ?? 'Personagem' }}
                </span>
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

<!-- Offcanvas para Upgrade de Personagem (acessível para mestres e players) -->
<div class="offcanvas offcanvas-end text-light" tabindex="-1" id="offcanvasUpgradePersonagem"
    aria-labelledby="offcanvasUpgradePersonagemLabel" style="background-color: #1c1c1c; max-width: 380px;">

    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasUpgradePersonagemLabel">
            <i class="fa-solid fa-star me-2"></i>Distribuir Pontos
        </h5>
        <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-3 overflow-auto" style="max-height: calc(100vh - 60px);">
        <div id="upgradeContent">
            {{-- Conteúdo será preenchido dinamicamente por JavaScript --}}
        </div>
    </div>
</div>

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

{{-- 1. Dependências globais --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- 2. Serviço WebSocket --}}
<script src="{{ asset('js/utils/webSocketService.js') }}"></script>

{{-- 3. Exporta variáveis PHP para JS --}}
<script>
    window.CHAT_CONFIG = {
        userId: {{ session('user_id') ?? 'null' }},
        userLogin: "{{ session('user_login') ?? 'Desconhecido' }}",
        salaId: {{ $sala['id'] }},
        // wsUrl: "{{ env('EXTERNAL_API_URL') }}" + "/ws",
        wsUrl: window.location.origin + "/ws",
        isMestre: {{ $isDono ? 'true' : 'false' }}
    };

    window.isMestre = {{ $isDono ? 'true' : 'false' }};

    window.csrfToken = "{{ csrf_token() }}";
    const csrfToken = "{{ csrf_token() }}";
    const routeSalasIndex = "{{ route('salas.index') }}";
</script>

{{-- 4. Gerenciadores de Estado e Turno (ordem: estado primeiro, depois manager) --}}
<script type="module" src="{{ asset('js/room/general/enums.js') }}"></script>
<script src="{{ asset('js/room/game/turnState.js') }}"></script>
<script src="{{ asset('js/room/game/turnManager.js') }}"></script>
<script src="{{ asset('js/room/game/turnUIManager.js') }}"></script>

{{-- 5. Gerenciadores de Personagens e Fluxo de Jogo --}}
<script src="{{ asset('js/room/game/personagensManager.js') }}"></script>
<script src="{{ asset('js/room/game/gameFlow.js') }}"></script>
<script src="{{ asset('js/room/game/actionNotifier.js') }}"></script>

{{-- 6. Dados (módulo ES6) --}}
<script type="module" src="{{ asset('js/room/game/diceManager.js') }}"></script>

{{-- 7. Gerenciadores da Sala (após todos os anteriores) --}}
<script src="{{ asset('js/room/general/chatRoom.js') }}"></script>
<script src="{{ asset('js/room/game/roomManager.js') }}"></script>

<script>
    /**
     * =========================
     * 📌 INICIALIZAÇÃO
     * =========================
     */
    window.onload = function () {
        const aviso = document.getElementById('aviso-fullscreen');

        // Mostra aviso depois de 1s
        setTimeout(() => {
            if (!aviso) return;

            aviso.style.display = 'block';

            // Ao clicar no aviso → entra em fullscreen
            aviso.onclick = function () {
                entrarEmFullscreen();
                aviso.style.display = 'none';
            };
        }, 1000);

        // Esconde automaticamente após 5s
        setTimeout(() => {
            if (aviso) aviso.style.display = 'none';
        }, 5000);

        // Garante estado correto do botão ao carregar
        atualizarBotaoFullscreen();
    };


    /**
     * =========================
     * 📌 LISTENERS GLOBAIS
     * =========================
     */

    // Dispara quando entra/sai do fullscreen via API
    document.addEventListener('fullscreenchange', atualizarBotaoFullscreen);

    // Dispara quando tela muda tamanho (inclui F11)
    window.addEventListener('resize', atualizarBotaoFullscreen);

    // Intercepta F11 e usa nosso controle
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F11') {
            e.preventDefault(); // bloqueia fullscreen padrão do navegador
            toggleFullscreen();
        }
    });


    /**
     * =========================
     * 📌 CONTROLE DO BOTÃO
     * =========================
     */
    function atualizarBotaoFullscreen() {
        const btn = document.getElementById('fullscreen');
        if (!btn) return;

        // Detecta fullscreen via API
        const isFullscreenAPI = !!document.fullscreenElement;

        // Detecta fullscreen "real" (F11)
        const isFullscreenReal = window.innerHeight === screen.height;

        // Se qualquer um for true → está em fullscreen
        if (isFullscreenAPI || isFullscreenReal) {
            btn.innerHTML = '<i class="fa-solid fa-compress"></i> Minimizar';
            btn.onclick = sairDoFullscreen;
        } else {
            btn.innerHTML = '<i class="fa-solid fa-expand"></i> Tela Cheia';
            btn.onclick = entrarEmFullscreen;
        }
    }


    /**
     * =========================
     * 📌 AÇÕES DE FULLSCREEN
     * =========================
     */

    // Entra em fullscreen usando API
    function entrarEmFullscreen() {
        const elem = document.documentElement;

        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen(); // Safari
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen(); // IE/Edge antigo
        }
    }


    // Sai do fullscreen
    function sairDoFullscreen() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }


    // Alterna entre entrar/sair
    function toggleFullscreen() {
        const isFullscreenAPI = !!document.fullscreenElement;
        const isFullscreenReal = window.innerHeight === screen.height;

        if (isFullscreenAPI || isFullscreenReal) {
            sairDoFullscreen();
        } else {
            entrarEmFullscreen();
        }
    }
</script>
@endsection
