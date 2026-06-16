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
                background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
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

                color: #fff;
                text-shadow:
                    0 0 28px rgba(255, 255, 255, .9),
                    0 0 60px rgba(0, 0, 0, .45);

                z-index: 35;
                /* ← antes era 3 */

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

            @keyframes lowHealthPulse {
                0%,
                100% {
                    opacity: var(--low-health-opacity-min, 0.1);
                }

                50% {
                    opacity: var(--low-health-opacity-max, 0.18);
                }
            }

            #lowHealthOverlay {
                position: fixed;
                inset: 0;
                z-index: 4;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.35s ease;
                background:
                    radial-gradient(circle at center, rgba(180, 0, 0, 0) 52%, rgba(180, 0, 0, 0.14) 82%, rgba(180, 0, 0, 0.32) 100%),
                    linear-gradient(90deg, rgba(170, 0, 0, 0.22), rgba(170, 0, 0, 0) 16%, rgba(170, 0, 0, 0) 84%, rgba(170, 0, 0, 0.22));
            }

            #lowHealthOverlay.is-wounded {
                --low-health-opacity-min: 0.08;
                --low-health-opacity-max: 0.18;
                opacity: 0.14;
                animation: lowHealthPulse 2.2s ease-in-out infinite;
            }

            #lowHealthOverlay.is-critical {
                --low-health-opacity-min: 0.22;
                --low-health-opacity-max: 0.38;
                opacity: 0.3;
                animation: lowHealthPulse 1.15s ease-in-out infinite;
                background:
                    radial-gradient(circle at center, rgba(220, 0, 0, 0) 48%, rgba(220, 0, 0, 0.18) 78%, rgba(220, 0, 0, 0.34) 100%),
                    linear-gradient(90deg, rgba(220, 0, 0, 0.26), rgba(220, 0, 0, 0) 20%, rgba(220, 0, 0, 0) 80%, rgba(220, 0, 0, 0.26));
            }

            /**
             * 📌 SISTEMA DE PIN (FIXAR FICHA)
             */
            #fixarFichaMestre,
            #fixarFichaJogador {
                transition: all 0.3s ease;
            }

            #fixarFichaMestre.active,
            #fixarFichaJogador.active {
                transform: rotate(-15deg) scale(1.1);
                box-shadow: 0 0 12px rgba(17, 150, 243, 0.6);
            }

            .offcanvas-pinned {
                box-shadow: inset -4px 0 0 0 rgba(17, 150, 243, 0.4) !important;
                pointer-events: auto;
            }

            body.offcanvas-interactive {
                overflow: auto !important;
                padding-right: 0 !important;
            }

            body.offcanvas-interactive .offcanvas-backdrop {
                display: none !important;
            }

            .personagem-nome {
                display: block;
                min-width: 0;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .personagem-card-nome-wrap {
                min-width: 0;
                max-width: 100%;
                padding-right: 2.25rem;
            }

            .room-text-safe {
                min-width: 0;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .room-wrap-safe {
                min-width: 0;
                overflow-wrap: anywhere;
                word-break: break-word;
            }

            #coluna-personagens {
                overflow-x: hidden !important;
            }

            #dice-placeholder {
                max-width: calc(100% - 2rem);
                overflow-wrap: anywhere;
            }

            .chat-container,
            #chat-content,
            #logs-content,
            #chat-messages,
            #system-logs {
                min-width: 0;
            }

            .hud-bg {
                max-width: calc(100vw - 1rem);
            }

            .hud-bg > .d-flex {
                flex-wrap: wrap;
                justify-content: center;
            }

            #offcanvasFichaPersonagem .offcanvas-header,
            #offcanvasUpgradePersonagem .offcanvas-header,
            #offcanvasMembers .offcanvas-header {
                gap: 0.5rem;
            }

            #offcanvasFichaPersonagem .offcanvas-title,
            #offcanvasUpgradePersonagem .offcanvas-title,
            #offcanvasMembers .offcanvas-title {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            #lista-membros .list-group-item > div,
            #lista-membros .list-group-item > .room-text-safe {
                min-width: 0;
            }

            .offcanvas-ficha-player {
                width: min(92vw, 360px) !important;
                max-width: 92vw !important;
            }

            .ficha-player-header {
                align-items: flex-start;
                gap: 0.5rem;
            }

            .ficha-player-title {
                min-width: 0;
                line-height: 1.25;
                padding-top: 0.25rem;
            }

            .ficha-player-title .personagem-nome {
                flex: 1 1 auto;
            }

            .ficha-player-actions {
                flex: 0 0 auto;
            }

            .ficha-player-body small {
                overflow-wrap: anywhere;
            }

            @media (max-width: 768px) {
                #game-section {
                    width: 94vw !important;
                    height: 56vh !important;
                    gap: 0.5rem !important;
                }

                #coluna-personagens {
                    flex-basis: 180px !important;
                    padding: 0.5rem !important;
                }

                .chat-container {
                    width: min(400px, 92vw) !important;
                    height: min(400px, 54vh) !important;
                }

                .hud-btn {
                    width: 52px;
                    height: 52px;
                    font-size: 1.2rem;
                }
            }

            .offcanvas-pinned .offcanvas-header {
                border-bottom: 2px solid rgba(17, 150, 243, 0.4);
            }

            /**
             * Dica visual: ESC para desfixar
             */
            .offcanvas-pinned::after {
                content: '';
                position: fixed;
                top: 10px;
                left: 50%;
                transform: translateX(-50%);
                padding: 6px 12px;
                background: rgba(17, 150, 243, 0.1);
                border-radius: 4px;
                font-size: 0.75rem;
                color: rgba(17, 150, 243, 0.8);
                pointer-events: none;
                white-space: nowrap;
                z-index: 1;
            }
        </style>

        {{-- Background da Sala --}}
        <div id="roomBackground"
            style="background-image: url('{{ $sala['urlBackground'] ? $sala['urlBackground'] : asset('assets/images/forge.png') }}');">
        </div>

        {{-- Configurações de Sala, para mestre e player --}}
        @if (!$isDono)
            <div id="lowHealthOverlay" aria-hidden="true"></div>
        @endif

        <div class="position-absolute top-0 end-0 d-flex align-items-center gap-2 m-3">

            <div class="dropdown">
                <button class="btn btn-light text-dark dropdown-toggle" id="optionsMenu" data-bs-toggle="dropdown"
                    aria-expanded="false">
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
                        <a href="#" class="dropdown-item" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasMembers">
                            <i class="fa-solid fa-users"></i>
                            Membros
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider text-white">
                    </li>
                    @if ($isDono)
                        <li>
                            <a href="#" class="dropdown-item" data-action="convidar">
                                <i class="fa-solid fa-user-plus"></i>
                                Convidar Membros
                            </a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" data-action="copiar-codigo"
                                data-code="{{ $sala['codigo'] ?? '' }}">
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
                <button class="btn btn-light text-dark ms-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFicha"
                    aria-controls="offcanvasFicha">
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
                <span id="chat-notification-badge"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="display: none; font-size: 0.6rem;">
                    <span id="chat-notification-count">0</span>
                </span>
            </button>

            <!-- Collapse -->
            <div class="collapse" id="chatCollapse">
                <div class="chat-container rounded-4 shadow"
                    style="width: 400px; max-width: 90vw; height: 400px; background-color: rgba(0, 0, 0, 0.8); display: flex; flex-direction: column;">

                    <!-- Tabs para Chat e Logs -->
                    <ul class="nav nav-tabs nav-fill border-0" id="chatTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-light border-0" id="chat-tab" data-bs-toggle="pill"
                                data-bs-target="#chat-content" type="button" role="tab" aria-controls="chat-content"
                                aria-selected="true">
                                <i class="fa-solid fa-comments me-1"></i>Chat
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-light border-0" id="logs-tab" data-bs-toggle="pill"
                                data-bs-target="#logs-content" type="button" role="tab" aria-controls="logs-content"
                                aria-selected="false">
                                <i class="fa-solid fa-list-ul me-1"></i>Logs
                            </button>
                        </li>
                    </ul>

                    <!-- Conteúdo das Tabs -->
                    <div class="tab-content flex-grow-1 overflow-hidden" id="chatTabContent">

                        <!-- Aba Chat -->
                        <div class="tab-pane fade show active d-flex flex-column h-100" id="chat-content" role="tabpanel"
                            aria-labelledby="chat-tab">
                            <div id="chat-messages" class="flex-grow-1 overflow-auto mb-3 p-3" style="min-height: 0;">
                                {{-- Mensagens serão inseridas aqui --}}
                            </div>
                            <div class="input-group p-3 border-top border-secondary">
                                <input type="text" class="form-control" placeholder="Digite sua mensagem..."
                                    id="chat-input">
                                <button class="btn btn-primary" id="chat-send">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Aba Logs -->
                        <div class="tab-pane fade d-flex flex-column h-100" id="logs-content" role="tabpanel"
                            aria-labelledby="logs-tab">
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
            <div id="dice-container"
                class="shadow-lg rounded-4 d-flex align-items-center justify-content-center flex-grow-1 position-relative"
                style="background: rgba(0,0,0,.25); overflow:hidden;">

                {{-- Placeholder (mais atrás) --}}
                <div id="dice-placeholder"
                    class="text-white text-center position-absolute top-50 start-50 translate-middle" style="z-index:32;">
                    🎲 Aguardando início do turno...
                </div>

                {{-- Grid (na frente do placeholder) --}}
                <div id="grid-layer" class="position-absolute top-0 start-0 w-100 h-100" style="z-index:20;">
                </div>

                {{-- Dice (na frente do grid) --}}
                <div id="dice-box" class="position-absolute top-0 start-0 w-100 h-100" style="z-index:30;">
                </div>

                {{-- Controles (na frente de tudo) --}}
                <div id="turnControls"
                    class="d-none flex-column align-items-center gap-2 position-absolute bottom-0 start-50 translate-middle-x mb-2"
                    style="z-index:40;">

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
        <div class="position-fixed bottom-0 start-50 translate-middle-x mb-3" style="z-index: 50;">
            <div class="d-flex flex-column gap-2 px-3 py-2 rounded-4 shadow hud-bg" style="align-items: center;">

                @if ($isDono)
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
                        <small class="text-danger" style="margin: 0;"><i class="fa-solid fa-heart"></i>
                            <strong>Vida</strong></small>
                        <div class="progress" style="height: 26px; font-size: 0.8rem;">
                            <div id="playerHealthBar"
                                class="progress-bar bg-success d-flex justify-content-center align-items-center text-dark fw-bold"
                                role="progressbar" style="width: 100%;"
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
                        <button type="button" id="btnConfirmarValor" class="btn btn-danger"
                            data-bs-dismiss="modal">Aplicar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Offcanvas para Ficha de Personagem (genérico, usado pelo mestre) -->
        <div class="offcanvas offcanvas-end text-light" tabindex="-1" id="offcanvasFichaPersonagem"
            data-bs-scroll="false" data-bs-backdrop="true" aria-labelledby="offcanvasFichaPersonagemLabel"
            style="background-color: #1c1c1c; max-width: 320px;">

            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasFichaPersonagemLabel">
                    <i class="fa-solid fa-scroll me-2"></i>Ficha
                </h5>
                <button type="button" class="btn btn-outline-info btn-sm rounded-3" id="fixarFichaMestre"
                    title="Fixar Ficha">
                    <i class="fa-solid fa-thumbtack"></i>
                </button>
                <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>

            <div class="offcanvas-body p-3 overflow-auto" style="max-height: calc(100vh - 60px);">
                {{-- Conteúdo será preenchido dinamicamente por JavaScript --}}
            </div>
        </div>
    @else
        <!-- Ficha do jogador -->
        <div class="offcanvas offcanvas-end text-light offcanvas-ficha-player" tabindex="-1" id="offcanvasFicha"
            data-bs-scroll="false" data-bs-backdrop="true" aria-labelledby="offcanvasFichaLabel"
            style="background-color: #1c1c1c;">

            <div class="offcanvas-header ficha-player-header">
                <h5 class="offcanvas-title d-flex align-items-center flex-grow-1 ficha-player-title mb-0"
                    id="offcanvasFichaLabel">
                    <i class="fa-solid fa-scroll me-2 flex-shrink-0"></i>
                    <span class="flex-shrink-0">Ficha de</span>
                    <span class="personagem-nome ms-1" title="{{ $personagemJogador['nome'] ?? 'Personagem' }}">
                        {{ $personagemJogador['nome'] ?? 'Personagem' }}
                    </span>
                </h5>
                <div class="ficha-player-actions d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-info btn-sm rounded-3" id="fixarFichaJogador"
                        title="Fixar Ficha">
                        <i class="fa-solid fa-thumbtack"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
            </div>

            <div class="offcanvas-body p-3 overflow-auto ficha-player-body" style="max-height: calc(100vh - 60px);">
                <div class="row g-2">
                    <!-- Header do Personagem -->
                    <div class="col-12 mb-3 border-bottom border-secondary pb-2">
                        <div><small><strong><i class="fa-solid fa-user-shield"></i> Raça:</strong>
                                {{ $personagemJogador['raca'] ?? 'N/A' }}</small></div>
                        <div><small><strong><i class="fa-solid fa-wand-magic-sparkles"></i> Classe:</strong>
                                {{ $personagemJogador['classe'] ?? 'N/A' }}</small></div>
                        <div><small><strong><i class="fa-solid fa-signal"></i> Nível:</strong>
                                {{ $personagemJogador['level'] ?? 1 }}</small></div>
                    </div>

                    <!-- Atributos Principais -->
                    <div class="col-12 mb-2">
                        <small class="text-warning"><strong>⚔️ Atributos Principais</strong></small>
                    </div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-dumbbell"></i> Força:</strong>
                            {{ $personagemJogador['forca'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-bolt"></i> Agilidade:</strong>
                            {{ $personagemJogador['agilidade'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-brain"></i> Inteligência:</strong>
                            {{ $personagemJogador['inteligencia'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-hand"></i> Destreza:</strong>
                            {{ $personagemJogador['destreza'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-shield-heart"></i> Vitalidade:</strong>
                            {{ $personagemJogador['vitalidade'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-eye"></i> Percepção:</strong>
                            {{ $personagemJogador['percepcao'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-book"></i> Sabedoria:</strong>
                            {{ $personagemJogador['sabedoria'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-comments"></i> Carisma:</strong>
                            {{ $personagemJogador['carisma'] ?? 0 }}</small></div>

                    <!-- Recursos de Vida/Mana -->
                    <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                        <small class="text-info"><strong>❤️ Recursos</strong></small>
                    </div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-heart"></i> Vida:</strong>
                            {{ $personagemJogador['vida'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-droplet"></i> Mana:</strong>
                            {{ $personagemJogador['mana'] ?? 0 }}</small></div>

                    <!-- Iniciativa e Bônus -->
                    <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                        <small class="text-success"><strong>⚡ Ações</strong></small>
                    </div>
                    <div class="col-12"><small><strong><i class="fa-solid fa-forward"></i> Iniciativa:</strong>
                            {{ $personagemJogador['iniciativa'] ?? 0 }}</small></div>

                    <!-- Ataques -->
                    <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                        <small class="text-danger"><strong>⚔️ Ataques</strong></small>
                    </div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-wand-magic-sparkles"></i> Atk
                                Mágico:</strong> {{ $personagemJogador['ataqueMagico'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-hand-fist"></i> Atk Corpo:</strong>
                            {{ $personagemJogador['ataqueFisicoCorpo'] ?? 0 }}</small></div>
                    <div class="col-12 mt-2"><small><strong><i class="fa-solid fa-bullseye"></i> Atk Distância:</strong>
                            {{ $personagemJogador['ataqueFisicoDistancia'] ?? 0 }}</small></div>

                    <!-- Defesa -->
                    <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                        <small class="text-secondary"><strong>🛡️ Defesa</strong></small>
                    </div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-shield-halved"></i> Defesa:</strong>
                            {{ $personagemJogador['defesaPersonagem'] ?? 0 }}</small></div>
                    <div class="col-6"><small><strong><i class="fa-solid fa-feather"></i> Esquiva:</strong>
                            {{ $personagemJogador['esquivaPersonagem'] ?? 0 }}</small></div>
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
                        <strong class="room-text-safe d-inline-block" title="{{ $membros[$mestreId] ?? 'Mestre' }}">
                            {{ $membros[$mestreId] ?? 'Mestre' }}
                        </strong>
                    </div>
                    <span><span class="members-list-dot offline"></span></span>
                </li>

                {{-- 🔹 Depois os Players (exceto o mestre) --}}
                @foreach ($membros as $uid => $login)
                    @continue($uid == $mestreId)

                    <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center"
                        data-user-id="{{ $uid }}">
                        <span class="room-text-safe d-inline-block" title="{{ $login }}">{{ $login }}</span>
                        <span><span class="members-list-dot offline"></span></span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Modais compartilhados --}}
    @include('partials.invite')
    @include('partials.copyCode')

    {{-- Aviso de tela cheia --}}
    <div id="aviso-fullscreen" class="alert alert-info"
        style="display: none; position: fixed; top: 10px; left: 50%; transform: translateX(-50%); z-index: 1050; cursor: pointer;">
        <i class="fa-solid fa-info-circle"></i>
        Pressione <strong>F11</strong> ou <strong>Clique aqui</strong> para entrar em tela cheia
    </div>

    {{-- 1. Dependências globais --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- 2. Serviço WebSocket --}}
    <script src="{{ asset('js/utils/webSocketService.js') }}?v={{ filemtime(public_path('js/utils/webSocketService.js')) }}"></script>

    {{-- 3. Exporta variáveis PHP para JS --}}
    <script>
        window.CHAT_CONFIG = {
            userId: @json(session('user_id')),
            userLogin: @json(session('user_login') ?? 'Desconhecido'),
            salaId: @json($sala['id']),
            wsUrl: window.location.origin + "/ws",
            isMestre: {{ $isDono ? 'true' : 'false' }},
            nomePersonagem: @json($isDono ? 'Mestre' : ($personagemJogador['nome'] ?? 'Desconhecido'))
        };

        window.isMestre = {{ $isDono ? 'true' : 'false' }};

        window.csrfToken = @json(csrf_token());
        window.salaId = @json($sala['id']);
        window.routeSalasIndex = @json(route('salas.index'));
    </script>

    {{-- 3.5. Gerenciadores gerais da sala --}}
    <script src="{{ asset('js/room/general/screenManager.js') }}"></script>
    <script src="{{ asset('js/room/general/pinManager.js') }}"></script>

    {{-- 4. Gerenciadores de Estado e Turno (ordem: estado primeiro, depois manager) --}}
    <script type="module" src="{{ asset('js/room/general/enums.js') }}"></script>
    <script src="{{ asset('js/room/game/turnState.js') }}"></script>
    <script src="{{ asset('js/room/game/turnManager.js') }}?v={{ filemtime(public_path('js/room/game/turnManager.js')) }}"></script>
    <script src="{{ asset('js/room/game/turnUIManager.js') }}"></script>

    {{-- 4.4. Grid de Batalha --}}
    <script src="{{ asset('js/room/game/mathUtils.js') }}"></script>
    <script src="{{ asset('js/room/game/grid.js') }}"></script>

    {{-- 4.5. Efeitos sonoros --}}
    <script src="{{ asset('js/room/game/sound/audioManager.js') }}"></script>

    {{-- 5. Gerenciadores de Personagens e Fluxo de Jogo --}}
    <script src="{{ asset('js/room/game/personagensManager.js') }}?v={{ filemtime(public_path('js/room/game/personagensManager.js')) }}"></script>
    <script src="{{ asset('js/room/game/gameFlow.js') }}"></script>
    <script src="{{ asset('js/room/game/actionNotifier.js') }}"></script>

    {{-- 6. Dados (módulo ES6) --}}
    <script type="module" src="{{ asset('js/room/game/diceManager.js') }}"></script>

    {{-- 6.5. Gerenciador unificado de convites e cópia de código --}}
    <script src="{{ asset('js/room/general/conviteManager.js') }}"></script>

    {{-- 7. Gerenciadores da Sala (após todos os anteriores) --}}
    <script src="{{ asset('js/room/general/chatRoom.js') }}"></script>
    <script src="{{ asset('js/room/game/roomManager.js') }}?v={{ filemtime(public_path('js/room/game/roomManager.js')) }}"></script>

    <script>

        /**
         * ✅ CONVITES E CÓPIA DE CÓDIGO
         * Gerenciados automaticamente pelo conviteManager.js
         * Detecta: .btn-invite, [data-action="convidar"]
         * Detecta: .btn-copy, [data-action="copiar-codigo"]
         */
        window.csrfToken = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
        window.salaId = window.salaId || @json($sala['id']);
    </script>
@endsection
