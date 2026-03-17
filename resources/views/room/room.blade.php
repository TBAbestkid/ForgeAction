@extends('partials/app')
@section('title', "{$sala['nome']} - ForgeAction")

@section('content')
<div class="container-fluid mt-3 d-flex flex-column gap-2">

    {{-- Mini Navbar da Sala --}}
    <nav class="navbar navbar-dark bg-dark rounded px-3 py-0 mb-2 d-flex flex-column flex-md-row align-items-md-center justify-content-between small">
        <div class="d-flex flex-column flex-md-row align-items-md-center">
            <div class="d-flex flex-column">
                <h2 class="font-medieval text-white mb-0 fs-5">{{ $sala['nome'] }}</h2>
                <p class="text-light mb-0 small">{{ $sala['descricao'] }}</p>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mt-2 mt-md-0">
            @if ($isDono)
                <button class="btn btn-outline-light d-flex align-items-center btn-invite" data-id="{{ $sala['id'] }}">
                    <i class="fa-solid fa-user-plus me-1"></i>
                    <span class="d-none d-md-inline">Convidar</span>
                </button>

                <button class="btn btn-outline-light d-flex align-items-center btn-copy" data-code="{{ $sala['codigo'] }}">
                    <i class="fa-solid fa-clipboard me-1"></i>
                    <span class="d-none d-md-inline">Copiar Código</span>
                </button>

                <button class="btn btn-outline-light d-flex align-items-center" type="button"
                        data-bs-toggle="modal" data-bs-target="#editSalaModal">
                    <i class="fa-solid fa-pen-to-square me-1"></i>
                    <span class="d-none d-md-inline">Editar Sala</span>
                </button>
            @endif

            <button class="btn btn-outline-light d-flex align-items-center"
                    type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMembers"
                    aria-controls="offcanvasMembers">
                <i class="fa-solid fa-users me-1"></i>
                <span class="d-none d-md-inline">Membros</span>
            </button>

            @if(!$isDono)
                <a href="/home" class="btn btn-sm btn-outline-danger d-flex align-items-center">
                    <i class="fa-solid fa-door-open me-1"></i> Sair da sala
                </a>
            @endif
        </div>
    </nav>

    {{-- Estrutura principal com 3 colunas --}}
    <div class="d-flex flex-grow-1 gap-3">
        {{-- Coluna 2: Área de imagens + chat --}}
        <div class="d-flex flex-column flex-grow-1">

            {{-- Área principal de jogos (personagens + DiceBox) --}}
            <div id="games-section" class="d-flex flex-column flex-lg-row gap-3 align-items-stretch" style="height: 60vh;">

                {{-- Coluna esquerda (Logs) --}}
                <nav class="d-none d-md-flex flex-column bg-dark p-3 rounded" style="flex: 0 0 30%; overflow-y: auto; min-width: 260px; max-width: 340px;">

                    {{-- Botões horizontais --}}
                    <ul class="nav nav-pills mb-3" id="chatLogsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-center" id="tab-chat" data-bs-toggle="pill" data-bs-target="#chat-tab" type="button" role="tab" aria-controls="chat-tab" aria-selected="true">
                                <i class="fa-solid fa-comment"></i> Chat
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-center" id="tab-logs" data-bs-toggle="pill" data-bs-target="#logs-tab" type="button" role="tab" aria-controls="logs-tab" aria-selected="false">
                                <i class="fa-solid fa-list-ul"></i> Logs
                            </button>
                        </li>
                        @if(!$isDono && $personagemJogador)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFicha" aria-controls="offcanvasFicha">
                                    <i class="fa-solid fa-scroll"></i> Ficha
                                </button>
                            </li>
                        @endif
                    </ul>

                    {{-- Conteúdo das Tabs --}}
                    <div class="tab-content flex-grow-1" style="height: calc(100% - 50px);">
                        {{-- Chat --}}
                        <div class="tab-pane fade show active d-flex flex-column h-100" id="chat-tab" role="tabpanel" aria-labelledby="tab-chat">
                            <strong class="mb-2">Chat da Sala:</strong>
                            <div id="chat-messages" class="flex-grow-1 d-flex flex-column gap-2 scroll-bottom scroll-invisible" style="min-height:150px;">
                                <!-- Mensagens -->
                            </div>
                            <div class="d-flex mt-2">
                                <input type="text" class="form-control me-2" placeholder="Digite sua mensagem..." id="chat-input">
                                <button class="btn btn-primary" id="chat-send"><i class="fa-solid fa-paper-plane"></i></button>
                            </div>
                        </div>

                        {{-- Logs --}}
                        <div class="tab-pane fade d-flex flex-column h-100" id="logs-tab" role="tabpanel" aria-labelledby="tab-logs">
                            <div class="d-flex flex-column bg-dark rounded p-3 text-white flex-grow-1 scroll-invisible">
                                <div id="system-logs">
                                    <!-- Logs -->
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                {{-- Coluna central (DiceBox) --}}
                <div id="dice-container" class="bg-dark rounded shadow-lg mx-2 position-relative"
                        style="flex: 1 1 45%; border: 2px solid #555; overflow: hidden; min-height: 420px;">

                    {{-- 🎲 Área exclusiva do DiceBox --}}
                    <div id="dice-box"
                        style="position: absolute; inset: 0; width: 100%; height: 100%;">
                    </div>

                    {{-- Placeholder centralizado --}}
                    <div id="dice-placeholder" class="text-white text-center"
                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                        🎲 Aguardando início do turno...
                    </div>

                    {{-- Controles fixos na parte inferior --}}
                    <div id="turnControls" class="d-none flex-column align-items-center gap-2"
                            style="position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); z-index: 10;">

                        <div class="d-flex gap-2 flex-wrap justify-content-center align-items-center">

                            @if ($isDono)
                                <div class="form-check d-flex align-items-center gap-2">
                                    <input type="checkbox" name="ocultarDados" id="ocultarDados" class="form-check-input">

                                    <label for="ocultarDados" class="form-check-label text-white">
                                        Ocultar Dados aos Jogadores
                                    </label>
                                </div>
                            @endif

                        </div>

                        <div id="diceOptions"
                            class="d-none mt-2 text-center">

                            <button class="btn btn-outline-primary m-1 diceBtn" data-sides="4">D4</button>
                            <button class="btn btn-outline-primary m-1 diceBtn" data-sides="6">D6</button>
                            <button class="btn btn-outline-primary m-1 diceBtn" data-sides="10">D10</button>
                            <button class="btn btn-outline-primary m-1 diceBtn" data-sides="12">D12</button>
                            <button class="btn btn-outline-primary m-1 diceBtn" data-sides="20">D20</button>

                        </div>
                    </div>

                </div>

                {{-- Coluna direita (personagens) --}}
                <div id="coluna-personagens" class="d-none d-lg-flex flex-column gap-3 overflow-auto" style="flex: 0 0 25%; padding: 0.5rem; min-width: 160px; max-width: 240px;">
                    {{-- Personagens serão inseridos aqui via JS --}}

                </div>

            </div>
            @if($isDono)
                {{-- Botões de ação de mestre como linha abaixo de área --}}
                <div class="flex-shrink-0 d-flex flex-column flex-md-row align-items-center justify-content-center gap-2 p-2 bg-dark rounded-3 shadow mt-3" style="max-height: fit-content;">
                    {{-- Todos os 6 botões em uma linha flexível --}}
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        {{-- 🔹 Iniciar/Avançar Turno --}}
                        <button id="btnIniciarTurno" class="btn btn-outline-success rounded-circle d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Iniciar/Avançar Turno"
                            style="width: 45px; height: 45px; font-size: 0.95rem;">
                            <i class="fa-solid fa-play"></i>
                        </button>

                        {{-- 🔹 Lançar Dados --}}
                        <button id="btnLancarMestre"
                            class="btn btn-outline-warning rounded-circle d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Lançar Dados (Mestre)"
                            style="width: 45px; height: 45px; font-size: 0.95rem;" disabled>
                            <i class="fa-solid fa-dice-d20"></i>
                        </button>

                        {{-- 🔹 Permitir Dados --}}
                        <button id="btnPermitirJogadaExtra"
                            class="btn btn-outline-primary rounded-circle d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Permitir Jogada Extra"
                            style="width: 45px; height: 45px; font-size: 0.95rem;" disabled>
                            <i class="fa-solid fa-user-check"></i>
                        </button>

                        {{-- 🔹 Causar Dano --}}
                        <button id="btnDano" class="btn btn-outline-danger rounded-circle d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Causar Dano"
                            style="width: 45px; height: 45px; font-size: 0.95rem;" disabled>
                            <i class="fa-solid fa-burst"></i>
                        </button>

                        {{-- 🔹 Curar --}}
                        <button id="btnCurar" class="btn btn-outline-success rounded-circle d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Curar"
                            style="width: 45px; height: 45px; font-size: 0.95rem;" disabled>
                            <i class="fa-solid fa-heart-pulse"></i>
                        </button>

                        {{-- 🔹 Upar Personagem --}}
                        <button id="btnUpar" class="btn btn-outline-info rounded-circle d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Upar Personagem"
                            style="width: 45px; height: 45px; font-size: 0.95rem;" disabled>
                            <i class="fa-solid fa-arrow-up"></i>
                        </button>
                    </div>
                </div>
            @else
                {{-- botoes de acao do player --}}
                <div class="flex-shrink-0 d-flex flex-column flex-md-row align-items-center justify-content-center gap-2 p-2 bg-dark rounded-3 shadow mt-3" style="max-height: fit-content;">
                    {{--2 botões em uma linha flexível --}}
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        {{-- 🔹 Lançar Dados --}}
                        <button id="btn-roll" class="btn btn-outline-light" disabled>🎲 Rodar Dado</button>
                        <button id="btn-skip" class="btn btn-outline-warning" disabled>⏭️ Pular</button>
                    </div>
                </div>
            @endif

            {{-- Botão para abrir/fechar chat --}}
            {{-- Container mobile de Chat, Logs e Players --}}
            <div class="d-md-none mt-3 w-100 bg-dark rounded p-2" style="max-height: 45vh;">
                {{-- Tabs --}}
                <ul class="nav nav-pills nav-fill mb-3" id="mobileTabs" role="tablist" style="font-size: 0.85rem;">
                    <li class="nav-item flex-fill" role="presentation">
                        <button class="nav-link active text-white d-flex align-items-center gap-1 justify-content-center p-2"
                                id="mobile-chat-tab" data-bs-toggle="pill" data-bs-target="#mobile-chat" type="button" role="tab" aria-controls="mobile-chat" aria-selected="true">
                            <i class="fa-solid fa-comment"></i> Chat
                        </button>
                    </li>
                    <li class="nav-item flex-fill" role="presentation">
                        <button class="nav-link text-white d-flex align-items-center gap-1 justify-content-center p-2"
                                id="mobile-logs-tab" data-bs-toggle="pill" data-bs-target="#mobile-logs" type="button" role="tab" aria-controls="mobile-logs" aria-selected="false">
                            <i class="fa-solid fa-list-ul"></i> Logs
                        </button>
                    </li>
                    <li class="nav-item flex-fill" role="presentation">
                        <button class="nav-link text-white d-flex align-items-center gap-1 justify-content-center p-2"
                                id="mobile-players-tab" data-bs-toggle="pill" data-bs-target="#mobile-players" type="button" role="tab" aria-controls="mobile-players" aria-selected="false">
                            <i class="fa-solid fa-users"></i> Players
                        </button>
                    </li>
                </ul>

                {{-- Conteúdo das tabs --}}
                <div class="tab-content bg-dark rounded p-2" style="max-height: 45vh;">
                    {{-- Chat --}}
                    <div class="tab-pane fade show active" id="mobile-chat" role="tabpanel" aria-labelledby="mobile-chat-tab">
                        <div id="chat-messages-mobile" class="d-flex flex-column gap-2 overflow-auto scroll-invisible" style="height: 30vh; font-size: 0.85rem;">
                            <!-- Mensagens -->
                        </div>
                        <div class="d-flex mt-2 gap-1">
                            <input type="text" class="form-control form-control-sm" placeholder="Mensagem..." id="chat-input-mobile">
                            <button class="btn btn-sm btn-primary" id="chat-send-mobile"><i class="fa-solid fa-paper-plane"></i></button>
                        </div>
                    </div>

                    {{-- Logs --}}
                    <div class="tab-pane fade" id="mobile-logs" role="tabpanel" aria-labelledby="mobile-logs-tab">
                        <div id="system-logs-mobile" class="d-flex flex-column overflow-auto scroll-invisible" style="height: 30vh; font-size: 0.85rem;">
                            <!-- Logs -->
                        </div>
                    </div>

                    {{-- Players --}}
                    <div class="tab-pane fade" id="mobile-players" role="tabpanel" aria-labelledby="mobile-players-tab">
                        <div id="coluna-personagens-mobile" class="d-flex flex-column gap-2 overflow-auto scroll-invisible" style="height: 30vh; font-size: 0.85rem;">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Offcanvas direita -->
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

        <!-- Offcanvas Ficha do Personagem (lado esquerdo) -->
        @if(!$isDono && $personagemJogador)
            <div class="offcanvas offcanvas-start text-light" tabindex="-1" id="offcanvasFicha"
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
    </div>

<!-- Modal de Edição -->
<div class="modal fade" id="editSalaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Editar Sala</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditSala">
                    <input type="hidden" name="id" value="{{ $sala['id'] }}">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ $sala['nome'] }}">
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao">{{ $sala['descricao'] }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Salvar alterações</button>
                </form>
            </div>
        </div>
    </div>
</div>

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


@include('partials/loading')
@include('partials/alerts')
@include('partials/invite')
{{-- @include('partials/invite') --}}

{{-- Estilos para animações de turno e modos de ação --}}
<style>
    /* Animação de pulse para cards selecionáveis */
    @keyframes hz-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
        }
    }

    /* Animação de pulse para perigo (dano) */
    @keyframes hz-pulse-danger {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    /* Animação de pulse para cura (sucesso) */
    @keyframes hz-pulse-success {
        0% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(25, 135, 84, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
        }
    }

    /* Animação de pulse para upgrade (info) */
    @keyframes hz-pulse-info {
        0% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
        }
    }

    /* Classes para modos de ação */
    .hz-modo-dano {
        animation: hz-pulse-danger 1.5s ease-in-out infinite !important;
        transition: all 0.3s ease;
    }

    .hz-modo-cura {
        animation: hz-pulse-success 1.5s ease-in-out infinite !important;
        transition: all 0.3s ease;
    }

    .hz-modo-upgrade {
        animation: hz-pulse-info 1.5s ease-in-out infinite !important;
        transition: all 0.3s ease;
    }

    /* Turno atual */
    .hz-turno {
        animation: hz-pulse-warning 1s ease-in-out infinite;
        transition: all 0.3s ease;
    }

    @keyframes hz-pulse-warning {
        0% {
            box-shadow: inset 0 0 0 3px rgba(255, 193, 7, 0.3);
        }
        50% {
            box-shadow: inset 0 0 0 3px rgba(255, 193, 7, 0.7);
        }
        100% {
            box-shadow: inset 0 0 0 3px rgba(255, 193, 7, 0.3);
        }
    }

    /* Selecionável */
    .hz-selecionavel {
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .hz-selecionavel:hover {
        transform: scale(1.02);
    }

    /* Animação de pulse para mudança de vida */
    @keyframes pulse-vida {
        0% {
            transform: scale(1);
            filter: brightness(1);
        }
        50% {
            transform: scale(1.05);
            filter: brightness(1.3);
        }
        100% {
            transform: scale(1);
            filter: brightness(1);
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/room/game/turnManager.js') }}"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>
<script src="{{ asset('js/utils/loading.js') }}"></script>
<script src="{{ asset('js/room/game/turnState.js') }}"></script>
<script type="module" src="{{ asset('js/room/game/diceManager.js') }}"></script>
<script src="{{ asset('js/room/game/gameFlow.js') }}"></script>
<script src="{{ asset('js/room/game/turnUIManager.js') }}"></script>
<script src="{{ asset('js/room/game/personagensManager.js') }}"></script>
{{-- Ativar tooltips --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (window.bootstrap && window.bootstrap.Tooltip) {
            [...tooltipTriggerList].forEach(el => new window.bootstrap.Tooltip(el));
        } else {
            // bootstrap não carregado ainda; será criado pelo script do bundle quando disponível
            console.warn('[room] bootstrap.Tooltip não disponível no momento.');
        }

        // ======== COPIAR CÓDIGO DA SALA ========
        const btnCopyCode = document.getElementById('btnCopyCode');

        btnCopyCode?.addEventListener('click', async () => {
            const code = btnCopyCode.dataset.code;

            if (!code) {
                showToast('Nenhum código disponível para copiar.', 'danger');
                return;
            }

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(code);
                } else {
                    // 🔸 fallback para browsers sem suporte ou sem HTTPS
                    const tempInput = document.createElement('input');
                    tempInput.value = code;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                }

                showToast('Código copiado para a área de transferência!', 'success');
            } catch (err) {
                console.error(err);
                showToast('Falha ao copiar o código.', 'danger');
            }
        });
    });
</script>
{{-- Exporta variáveis PHP para JS --}}
<script>
    // const newWsUrl = "/ws";

    // console.log("WebSocket URL:", newWsUrl);


    window.CHAT_CONFIG = {
        userId: {{ session('user_id') ?? 'null' }},
        userLogin: "{{ session('user_login') ?? 'Desconhecido' }}",
        salaId: {{ $sala['id'] }},
        // wsUrl: "{{ env('EXTERNAL_API_URL') }}" + "/ws",
        wsUrl: window.location.origin + "/ws",
        isMestre: {{ $isDono ? 'true' : 'false' }}
    };

    window.csrfToken = "{{ csrf_token() }}";
    const csrfToken = "{{ csrf_token() }}";
    const routeSalasIndex = "{{ route('salas.index') }}";
</script>

{{-- Scripts principais, ordem importante! --}}
{{-- 1. Serviço WebSocket (fundamental) --}}
<!-- Status styles for personagens (inline file) -->
<link rel="stylesheet" href="{{ asset('css/room-status.css') }}">
<script src="{{ asset('js/utils/webSocketService.js') }}"></script>

{{-- 2. Gerenciadores da sala --}}
<script src="{{ asset('js/room/general/chatRoom.js') }}"></script>
<script src="{{ asset('js/room/game/roomManager.js') }}"></script>

{{-- Scripts auxiliares --}}
<script src="{{ asset('js/room/general/exit.js') }}"></script>
<script src="{{ asset('js/room/general/invite.js') }}"></script>
<script src="{{ asset('js/room/general/delete.js') }}"></script>

@endsection

