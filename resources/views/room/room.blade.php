@extends('partials/app')
@section('title', "{$sala['nome']} - ForgeAction")

@section('content')
<div class="container-fluid mt-4 d-flex flex-column gap-3">

    {{-- Mini Navbar da Sala --}}
    <nav class="navbar navbar-dark bg-dark rounded px-3 py-1 mb-2 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
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
                <button class="btn btn-sm btn-outline-danger d-flex align-items-center" data-id="{{ $sala['id'] }}">
                    <i class="fa-solid fa-door-open me-1"></i> Abandonar aventura
                </button>
            @endif
        </div>
    </nav>

    {{-- Estrutura principal com 3 colunas --}}
    <div class="d-flex flex-grow-1 gap-3">
        {{-- Coluna 2: Área de imagens + chat --}}
        <div class="d-flex flex-column flex-grow-1" style="min-height: 100vh; width: 100%;">

            {{-- Área principal de jogos (personagens + DiceBox) --}}
            <div id="games-section" class="d-flex flex-column flex-lg-row gap-3 align-items-stretch flex-grow-1" style="min-height: 50vh;">

                {{-- Coluna esquerda (Logs) --}}
                <nav class="d-none d-md-flex flex-column bg-dark p-3 rounded" style="min-width: 200px; flex: 1 1 auto;">

                    {{-- Botões horizontais --}}
                    <ul class="nav nav-pills mb-3" id="chatLogsTabs" role="tablist">
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link active w-100 text-center" id="tab-chat" data-bs-toggle="pill" data-bs-target="#chat-tab" type="button" role="tab" aria-controls="chat-tab" aria-selected="true">
                                <i class="fa-solid fa-comment"></i> Chat
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100 text-center" id="tab-logs" data-bs-toggle="pill" data-bs-target="#logs-tab" type="button" role="tab" aria-controls="logs-tab" aria-selected="false">
                                <i class="fa-solid fa-list-ul"></i> Logs
                            </button>
                        </li>
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
                <div id="dice-container" class="bg-dark rounded shadow-lg d-flex flex-column justify-content-center align-items-center"
                    style="flex:2 1 auto; min-height:150px; border:2px solid #555;">
                    <span id="dice-placeholder" class="text-white">🎲 Aguardando início do turno...</span>

                    <div id="turn-controls" class="d-none flex-column align-items-center gap-2 mt-2">
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            @if (!$isDono)
                                <button id="btn-roll" class="btn btn-outline-light">🎲 Rodar Dado</button>
                                <button id="btn-skip" class="btn btn-outline-warning">⏭️ Pular</button>
                            @else
                                {{-- Apenas caso mestre quiser ocultar os dados --}}
                                <input type="checkbox" name="ocultarDados" id="ocultarDados" class="form-check-input mt-1" >
                                <label for="ocultarDados" class="form-check-label text-white">Ocultar Dados aos Jogadores</label>
                            @endif
                        </div>

                        <div id="dice-options" class="d-none mt-3 text-center">
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="4">D4</button>
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="6">D6</button>
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="10">D10</button>
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="12">D12</button>
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="20">D20</button>
                        </div>
                    </div>
                </div>

                {{-- Coluna direita (personagens) --}}
                <div class="d-flex flex-column gap-2 overflow-auto d-none d-lg-flex" style="flex:1 1 auto; min-width:120px;">
                    @foreach ($membros as $m)
                        <div id="info-personagem-{{ $m['personagemId'] }}" class="bg-dark rounded p-1 text-center d-flex flex-column align-items-center personagem-card"
                            data-bs-toggle="collapse"
                            data-bs-target="#info-personagem-{{ $m['personagemId'] }}"
                            aria-expanded="false"
                            aria-controls="info-personagem-{{ $m['personagemId'] }}"
                            style="cursor: pointer;"
                            data-id="{{ $m['personagemId'] }}"
                            data-vida-max="{{ $m['vida'] }}"
                            data-nome="{{ $m['nome'] }}"
                            data-raca="{{ $m['raca'] }}"
                            data-classe="{{ $m['classe'] }}"
                            data-level="{{ $m['level'] }}"
                            data-vida="{{ $m['vida'] }}"
                            data-mana="{{ $m['mana'] }}"
                            data-usuario-id="{{ $m['usuarioId'] }}"
                            data-forca="{{ $m['forca'] }}"
                            data-agilidade="{{ $m['agilidade'] }}"
                            data-inteligencia="{{ $m['inteligencia'] }}"
                            data-destreza="{{ $m['destreza'] }}"
                            data-vitalidade="{{ $m['vitalidade'] }}"
                            data-percepcao="{{ $m['percepcao'] }}"
                            data-sabedoria="{{ $m['sabedoria'] }}"
                            data-carisma="{{ $m['carisma'] }}"
                            data-ataque-magico="{{ $m['ataqueMagico'] }}"
                            data-ataque-corpo="{{ $m['ataqueFisicoCorpo'] }}"
                            data-ataque-distancia="{{ $m['ataqueFisicoDistancia'] }}"
                            data-defesa="{{ $m['defesaPersonagem'] }}"
                            data-esquiva="{{ $m['esquivaPersonagem'] }}"
                            data-iniciativa="{{ $m['iniciativa'] }}">
                            <strong class="small">{{ $m['nome'] }}</strong>
                            <div class="progress mt-1 w-100" style="height: 16px; font-size:0.7rem;">
                                <div class="progress-bar bg-success d-flex justify-content-center align-items-center"
                                    role="progressbar"
                                    style="width: {{ ($m['vida'] / $m['vida']) * 100 }}%;">
                                    {{ $m['vida'] }}/{{ $m['vida'] }} HP
                                </div>
                            </div>
                            <div id="info-personagem-{{ $m['personagemId'] }}" class="collapse mt-2"
                                style="min-height:150px; max-height:40vh; overflow:hidden;">
                                <div class="bg-dark rounded p-2 text-start text-light small">
                                    <strong>Raça:</strong> {{ $m['raca'] }}<br>
                                    <strong>Classe:</strong> {{ $m['classe'] }}<br>
                                    <strong>Nível:</strong> {{ $m['level'] }}<br>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
            @if(!$isDono)
                {{-- View de infos personagem --}}
                <div class="d-flex flex-column flex-md-row flex-wrap align-items-stretch gap-3 p-2 bg-dark rounded-4 shadow mt-3">

                    {{-- 🔹 Identidade --}}
                    <div class="card bg-warning text-dark flex-fill">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#collapseIdentidade" style="cursor:pointer;">
                            <h6 class="mb-0 text-dark">Identidade</h6>
                        </div>
                        <div id="collapseIdentidade" class="collapse">
                            <div class="card-body">
                                <div class="mb-2"><strong>Nome:</strong> {{ $personagem['nome'] ?? 'Desconhecido' }}</div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <div class="flex-fill bg-dark rounded p-2 text-white text-center">Raça: {{ $personagem['racaDescricao'] ?? '?' }}</div>
                                    <div class="flex-fill bg-dark rounded p-2 text-white text-center">Classe: {{ $personagem['classeDescricao'] ?? '?' }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <div class="flex-fill bg-dark rounded p-2 text-white text-center">Idade: {{ $personagem['idade'] ?? '?' }}</div>
                                    <div class="flex-fill bg-dark rounded p-2 text-white text-center">Gênero: {{ $personagem['genero'] ?? '?' }}</div>
                                </div>
                                <div class="bg-dark rounded p-2 text-white text-center"><strong>Nível:</strong> {{ $personagem['level'] ?? 1 }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- 🔹 Atributos --}}
                    <div class="card bg-warning text-dark flex-fill">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#collapseAtributos" style="cursor:pointer;">
                            <h6 class="mb-0 text-dark">Atributos</h6>
                        </div>
                        <div id="collapseAtributos" class="collapse">
                            <div class="card-body">
                                <div class="row g-2 text-white text-center">
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Força: {{ $personagem['forca'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Agilidade: {{ $personagem['agilidade'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Inteligência: {{ $personagem['inteligencia'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Destreza: {{ $personagem['destreza'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Vitalidade: {{ $personagem['vitalidade'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Percepção: {{ $personagem['percepcao'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Sabedoria: {{ $personagem['sabedoria'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Carisma: {{ $personagem['carisma'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 🔹 Status de Combate --}}
                    <div class="card bg-warning text-dark flex-fill">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#collapseCombate" style="cursor:pointer;">
                            <h6 class="mb-0 text-dark">Status de Combate</h6>
                        </div>
                        <div id="collapseCombate" class="collapse">
                            <div class="card-body">
                                <div class="row g-2 text-white text-center mb-2">
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">HP: {{ $personagem['vida'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Mana: {{ $personagem['mana'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Iniciativa: {{ $personagem['iniciativa'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Defesa: {{ $personagem['defesaPersonagem'] ?? 0 }}</div>
                                </div>
                                <div class="row g-2 text-white text-center">
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Esquiva: {{ $personagem['esquivaPersonagem'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Atk Corpo: {{ $personagem['ataqueFisicoCorpo'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Atk Distância: {{ $personagem['ataqueFisicoDistancia'] ?? 0 }}</div>
                                    <div class="col-6 col-md-3 bg-dark rounded p-2">Atk Mágico: {{ $personagem['ataqueMagico'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @else
                {{-- Botões de ação de mestre como linha abaixo de área --}}
                <div class="flex-shrink-0 d-flex flex-column flex-md-row align-items-center justify-content-center gap-2 gap-md-4 p-2 bg-dark rounded-4 shadow mt-3">
                    {{-- Linha pra ter três elementos --}}
                    <div class="row mb-2 gap-2 justify-content-center">
                        {{-- 🔹 Iniciar/Avançar Turno --}}
                        <button id="btnIniciarTurno" class="btn btn-outline-success rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Iniciar/Avançar Turno"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-play fs-4"></i>
                        </button>

                        {{-- 🔹 Lançar Dados --}}
                        <button id="btn-lancar-mestre"
                            class="btn btn-outline-warning rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Lançar Dados (Mestre)"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-dice-d20 fs-4"></i>
                        </button>

                        {{-- 🔹 Permitir Dados --}}
                        <button id="btn-permitir-jogada"
                            class="btn btn-outline-primary rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Permitir Jogada Extra"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-user-check fs-4"></i>
                        </button>
                    </div>

                    {{-- Linha pra ter três elementos --}}
                    <div class="row mb-2 gap-2 justify-content-center">
                        {{-- 🔹 Causar Dano --}}
                        <button id="btn-dano" class="btn btn-outline-danger rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Causar Dano"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-burst fs-4"></i>
                        </button>

                        {{-- 🔹 Curar --}}
                        <button id="btn-curar" class="btn btn-outline-success rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Curar"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-heart-pulse fs-4"></i>
                        </button>
                        {{-- 🔹 Upar Personagem --}}
                        <button id="btn-upar" class="btn btn-outline-info rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Upar Personagem"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-arrow-up fs-4"></i>
                        </button>


                    </div>
                </div>
            @endif

            {{-- Botão para abrir/fechar chat --}}
            {{-- Container mobile de Chat, Logs e Players --}}
            <div class="d-md-none mt-3 w-100">
                {{-- Tabs --}}
                <ul class="nav nav-tabs nav-fill mb-2" id="mobileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-white" id="mobile-chat-tab" data-bs-toggle="tab" data-bs-target="#mobile-chat" type="button" role="tab" aria-controls="mobile-chat" aria-selected="true">
                            <i class="fa-solid fa-comment"></i> Chat
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white" id="mobile-logs-tab" data-bs-toggle="tab" data-bs-target="#mobile-logs" type="button" role="tab" aria-controls="mobile-logs" aria-selected="false">
                            <i class="fa-solid fa-list-ul"></i> Logs
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white" id="mobile-players-tab" data-bs-toggle="tab" data-bs-target="#mobile-players" type="button" role="tab" aria-controls="mobile-players" aria-selected="false">
                            <i class="fa-solid fa-users"></i> Players
                        </button>
                    </li>
                </ul>

                {{-- Conteúdo das tabs --}}
                <div class="tab-content bg-dark rounded p-2">
                    {{-- Chat --}}
                    <div class="tab-pane fade show active" id="mobile-chat" role="tabpanel" aria-labelledby="mobile-chat-tab">
                        <div id="chat-messages-mobile" class="d-flex flex-column gap-2 scroll-bottom scroll-invisible" style="max-height: 300px;">
                            <!-- Mensagens -->
                        </div>
                        <div class="d-flex mt-2">
                            <input type="text" class="form-control me-2" placeholder="Digite sua mensagem..." id="chat-input-mobile">
                            <button class="btn btn-primary" id="chat-send-mobile"><i class="fa-solid fa-paper-plane"></i></button>
                        </div>
                    </div>

                    {{-- Logs --}}
                    <div class="tab-pane fade" id="mobile-logs" role="tabpanel" aria-labelledby="mobile-logs-tab">
                        <div id="system-logs-mobile" class="d-flex flex-column scroll-invisible" style="max-height: 300px;">
                            <!-- Logs -->
                        </div>
                    </div>

                    {{-- Players --}}
                    <div class="tab-pane fade" id="mobile-players" role="tabpanel" aria-labelledby="mobile-players-tab">
                        <div class="d-flex flex-column gap-2 scroll-invisible" style="max-height: 300px;">
                            @foreach ($membros as $m)
                                <div class="bg-dark rounded p-2 text-white text-center">
                                    <strong>{{ $m['nome'] }}</strong>
                                    <div class="progress mt-1" style="height: 12px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($m['vida'] / $m['vida']) * 100 }}%;">
                                            {{ $m['vida'] }}/{{ $m['vida'] }} HP
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
                    @if(isset($sala['mestre']))
                        @php
                            $mestre = collect($membros)->firstWhere('usuarioId', $sala['mestre']);
                        @endphp

                        <li class="list-group-item bg-dark text-warning d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fa-solid fa-crown text-warning me-2"></i>
                                <strong>{{ $mestre['usuarioLogin'] ?? 'Mestre' }}</strong>
                            </div>
                            <span><i class="fa-solid fa-circle text-success"></i></span>
                        </li>
                    @endif

                    {{-- 🔹 Depois os Players (exceto o mestre) --}}
                    @foreach($membros as $membro)
                        @continue(isset($sala['mestre']) && $membro['usuarioId'] == $sala['mestre']) {{-- Pula o mestre --}}

                        <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center">
                            {{ $membro['usuarioLogin'] ?? 'Jogador Desconhecido' }}
                            <span><i class="fa-solid fa-circle text-success"></i></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>
<script src="{{ asset('js/utils/loading.js') }}"></script>
<script type="module" src="{{ asset('js/room/dice-manager.js') }}"></script>
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
        // wsUrl: newWsUrl, // "{{ env('EXTERNAL_API_URL') }}/ws",
        wsUrl: "/ws",
        isMestre: {{ $isDono ? 'true' : 'false' }}
    };

    window.csrfToken = "{{ csrf_token() }}";
    const csrfToken = "{{ csrf_token() }}";
    const routeSalasIndex = "{{ route('salas.index') }}";
</script>

{{-- Scripts principais, ordem importante! --}}
{{-- 1. Serviço WebSocket (fundamental) --}}
<script src="{{ asset('js/utils/webSocketService.js') }}"></script>

{{-- 2. Gerenciadores da sala --}}
<script src="{{ asset('js/room/chat-room.js') }}"></script>
<script src="{{ asset('js/room/room-manager.js') }}"></script>

{{-- Scripts auxiliares --}}
<script src="{{ asset('js/room/exit.js') }}"></script>
<script src="{{ asset('js/room/invite.js') }}"></script>
<script src="{{ asset('js/room/delete.js') }}"></script>

@endsection

